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

namespace W7\Sdk\OpenCloud\Api\Common;

use W7\Sdk\OpenCloud\Exception\SiteRegisteredException;
use W7\Sdk\OpenCloud\Util\Common;
use W7\Sdk\OpenCloud\Request\We7Request;
use W7\Sdk\OpenCloud\Util\SiteInfoTraiter;

class Download extends We7Request
{
	use SiteInfoTraiter;

	protected $apiPath = '/util/shipping/index';
	protected $method  = 'application.shipping';
	private $path;
	private $type = 'module';

	/**
	 * 下载一个文件
	 * 下载文件时特殊的文件会采用推送的操作，将调用到 callback -> registershippingFileHandler 的回调中
	 * @return array|mixed ['path' => 文件在模块内的路径, 'file' => 文件内容]
	 * @throws SiteRegisteredException
	 */
	public function get()
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}
		if (empty($this->path)) {
			throw new \RuntimeException('缺少文件路径');
		}
		$data             = $this->siteInfo->toArray();
		$data['path']     = $this->path;
		$data['type']     = $this->type;
		$data['method']   = $this->method;
		$data['gz']       = function_exists('gzcompress') && function_exists('gzuncompress') ? 'true' : 'false';
		$data['download'] = true;
		return parent::post($data);
	}

	/**
	 * 要下载的文件名，模块内的文件需要在开头加上模块名称
	 * /modulename/site.php
	 * @param $path
	 * @return $this
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	public function decode($method, $response)
	{
		if ('success' == $response) {
			return $response;
		}

		$errorMessage = json_decode($response, true);
		if (JSON_ERROR_NONE === json_last_error()) {
			throw new \RuntimeException($errorMessage['message'], $errorMessage['errno']);
		}
		$result = Common::unserialize($response);
		$gz     = function_exists('gzcompress') && function_exists('gzuncompress');
		$file   = base64_decode($result['file']);
		if ($gz) {
			$file = gzuncompress($file);
		}
		$transtoken = $this->transToken;
		if (empty($transtoken)) {
			throw new \RuntimeException('Invalid trans token');
		}
		$string = (md5($file) . $result['path'] . $transtoken);

		if (md5($string) === $result['sign']) {
			return [
				'path' => $result['path'],
				'file' => $file,
			];
		}
	}
}
