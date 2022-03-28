<?php

/**
 * WeEngine Sdk Core System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Sdk\OpenCloud\Request\Middleware\Request;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Throwable;

class RetryMiddleware
{
	protected $config = [];
	/** @var callable  */
	protected $nextHandler;
	protected $retryHandler;

	public function __construct(callable $nextHandler, $config = [])
	{
		$this->config = $config;
		$this->nextHandler = $nextHandler;
		$this->retryHandler = new \GuzzleHttp\RetryMiddleware($this->retryDecider(), $this->nextHandler, $this->retryDelay());
	}

	public function __invoke(RequestInterface $request, array $options)
	{
		$retryHandler = $this->retryHandler;
		return $retryHandler($request, $options);
	}

	protected function getRetryMaxNum()
	{
		return $this->config['max_retry_num'] ?? 1;
	}

	protected function getRetryDelay()
	{
		return $this->config['retry_delay'] ?? 500;
	}

	/**
	 * retryDecider
	 * 返回一个匿名函数, 匿名函数若返回false 表示不重试，反之则表示继续重试
	 * @return \Closure
	 */
	protected function retryDecider() : \Closure
	{
		return function (
			$retries,
			PsrRequest $request,
			Response $response = null,
			Throwable $exception = null
		) {
			// 超过最大重试次数，不再重试
			if ($retries >= $this->getRetryMaxNum()) {
				return false;
			}

			// 请求失败，继续重试
			if ($exception instanceof ConnectException) {
				return true;
			}

			return false;
		};
	}

	/**
	 * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
	 * @return \Closure
	 */
	protected function retryDelay() : \Closure
	{
		return function ($numberOfRetries) {
			return $this->getRetryDelay() * $numberOfRetries;
		};
	}
}
