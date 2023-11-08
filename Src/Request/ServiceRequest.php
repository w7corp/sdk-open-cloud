<?php

/**
 * WeEngine Cloud SDK System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Sdk\OpenCloud\Request;

use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use W7\Sdk\OpenCloud\Exception\ResponseException;
use W7\Sdk\OpenCloud\Request\Middleware\Service\ServiceExtendMiddleware;
use W7\Sdk\OpenCloud\Request\Middleware\Service\ServiceNameMiddleware;
use W7\Sdk\OpenCloud\Util\AppTrait;
use W7\Sdk\OpenCloud\Util\Common;

abstract class ServiceRequest extends Request
{
	use AppTrait;

	protected $name = 'we7 open sdk';

	protected $apiUrl = 'https://openapi.w7.cc/';

	protected $defaultOperation = [
		'responseModel' => 'getResponse',
		'httpMethod' => 'POST',
		'errorResponses' => []
	];

	const OPERATION_CHECK_SIGN = 'checkSign';

	const TYPE_STRING = 'string';
	const TYPE_INT = 'integer';
	const TYPE_NUMBER = 'number';
	const TYPE_BOOLEAN = 'boolean';
	const TYPE_OBJECT = 'object';
	const TYPE_ARRAY = 'array';
	const TYPE_NULL = 'null';
	const TYPE_ANY = 'any';

	const LOCATION_QUERY = 'query';
	const LOCATION_HEADER = 'header';
	const LOCATION_BODY = 'body';
	const LOCATION_FORM_PARAM = 'formParam';
	const LOCATION_MULTIPART = 'multipart';
	const LOCATION_JSON = 'json';
	const LOCATION_XML = 'xml';
	const LOCATION_URI = 'uri';

	/**
	 * @var GuzzleClient
	 */
	protected $serviceClient;

	abstract public function getApiMap();

	protected function getDefaultServiceDescription()
	{
		//处理默认数据
		return [
			'name' => $this->name,
			'baseUri' => $this->apiUrl,
			'operations' => [
				self::OPERATION_CHECK_SIGN => [
					'parameters' => [
						'appid' => [
							'type' => self::TYPE_STRING,
							'location' => self::LOCATION_FORM_PARAM,
							'required' => true,
						],
						'sign' => [
							'type' => self::TYPE_STRING,
							'location' => self::LOCATION_FORM_PARAM,
							'required' => true,
						],
						'timestamp' => [
							'type' => self::TYPE_INT,
							'location' => self::LOCATION_FORM_PARAM,
							'required' => true,
						],
						'nonce' => [
							'type' => self::TYPE_STRING,
							'location' => self::LOCATION_FORM_PARAM,
							'required' => true,
						]
					]
				]
			],
			'models' => [
				'getResponse' => [
					'type' => 'object',
					'additionalProperties' => [
						'location' => 'body'
					],
					'properties' => [
						// 获取 body 到 content 变量中自行处理
						'content' => [
							'location' => 'body',
						],
					]
				]
			]
		];
	}

	protected function getServiceDescription() {
		$operation = $this->getApiMap();
		if (empty($operation)) {
			throw new \RuntimeException('Invalid api map');
		}

		$defaultServiceDescription = $this->getDefaultServiceDescription();

		foreach ($operation as $name => $config) {
			$config = array_merge([], $this->defaultOperation, $config);
			$defaultServiceDescription['operations'][$name] = $config;
		}

		return new Description($defaultServiceDescription);
	}

	public function getServiceClient()
	{
		if ($this->serviceClient) {
			return $this->serviceClient;
		}

		$this->serviceClient = new GuzzleClient(
			$this->getClient(),
			$this->getServiceDescription(),
			null,
			null,
			null,
			[
				'process' => false
			]
		);
		$handlerStack = $this->serviceClient->getHandlerStack();
		$handlerStack->unshift(new ServiceExtendMiddleware($this->serviceClient, $this));
		$handlerStack->unshift(new ServiceNameMiddleware($this->serviceClient));

		return $this->serviceClient;
	}

	final public function __call($name, $arguments)
	{
		try {
			/**
			 * @var Response $response
			 */
			$this->response = $response = $this->getServiceClient()->__call($name, $arguments);
			$this->responseContent = $response->getBody()->getContents();
			return $this->decode($this->responseContent);
		} catch (\Throwable $e) {
			if (method_exists($e, 'getResponse') && $e->getResponse()) {
				$content = $e->getResponse()->getBody()->getContents();
				$data = $this->decode($content);
				throw new ResponseException($data['error'] ?: $content, $e->getResponse()->getStatusCode());
			}

			$message = '返回的数据格式不正确. ' . $e->getMessage();
			throw new \Exception($message, $e->getCode());
		}
	}

	public function checkSign(Command $command)
	{
		$commandParams = [];
		foreach ($this->getServiceClient()->getDescription()->getOperation($command->getName())->getParams() as $param) {
			$commandParams[] = $param->getName();
		}

		$command['appid'] = $this->appId;
		$command['timestamp'] = time();
		$command['nonce'] = Common::random(16);
		foreach ($command->toArray() as $key => $value) {
			if (!in_array($key, $commandParams) && strpos($key, '@') === false) {
				throw new InvalidArgumentException("参数列表不一致 {$key}");
			}

			if (is_null($value)) {
				unset($command[$key]);
			}
		}
		$params = $command->toArray();

		$uriParams = $this->getUriParamsKey($command->getName());
		foreach ($uriParams as $key) {
			if (array_key_exists($key, $params)) {
				unset($params[$key]);
			}
		}

		unset($params['@http']);
		$command['sign'] = $this->sign($params);
		return $command;
	}

	protected function decode($content)
	{
		$message = json_decode($content, true);
		if (json_last_error() == JSON_ERROR_NONE) {
			// 一些成功结果标识直接返回 true
			$data = $message;
			if (!empty($message['data']) && $message['data'] === 'success') {
				$data = true;
			}
		} else {
			$data = $content;
		}

		return $data;
	}

	public function getUriParamsKey($operatorName)
	{
		/**
		 * @var $operation \GuzzleHttp\Command\Guzzle\Operation
		 */
		$operation = $this->getServiceClient()->getDescription()->getOperation($operatorName);
		$parameters = $operation->getParams();
        $parameters = array_filter($parameters, function (Parameter $parameter) {
            return $parameter->getLocation() === self::LOCATION_URI;
        });
        $params = [];
        foreach ($parameters as $parameter) {
            $params[] = $parameter->getName();
        }

		return $params;
	}
}
