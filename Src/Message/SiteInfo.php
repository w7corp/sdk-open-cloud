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

namespace W7\Sdk\Cloud\Message;

final class SiteInfo
{
	/**
	 * 站点id
	 */
	private $key;

	/**
	 * 站点Host
	 */
	private $host;

	/**
	 * 当前站点版本类型，商业版，普通版
	 */
	private $family;

	/**
	 * 当前站点token
	 */
	private $token;

	/**
	 * 当前站点通信密码
	 */
	private $password;

	/**
	 * 当前请求接口操作类型
	 */
	private $method;

	/**
	 * 当前PHP版本号
	 */
	private $php_version;

	/**
	 *  当前host
	 */
	private $current_host;

	/**
	 * 当前发行版本号
	 */
	private $release;

	/**
	 *  站点类型: 简体 繁体
	 */
	private $locale;

	/**
	 *  用于接收数据时保存的临时文件的键名
	 */
	private $file;

	private $version;

	public function toArray()
	{
		if (empty($this->key) || empty($this->host) || (empty($this->password) && empty($this->token))) {
			throw new \RuntimeException('站点信息不完整');
		}

		$info = [
			'key'         => $this->key,
			'host'        => $this->host,
			'method'      => $this->method,
			'family'      => $this->family,
			'version'     => $this->version,
			'release'     => $this->release,
			'password'    => $this->password ?: md5($this->key . $this->token),
			'php_version' => PHP_VERSION,
		];
		if (!preg_match('/cli/i', php_sapi_name())) {
			$info['current_host'] = false !== strpos($_SERVER['HTTP_HOST'], ':') ? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) : $_SERVER['HTTP_HOST'];
		}
		if (!empty($this->file)) {
			$info['file'] = $this->file;
		}

		return $info;
	}

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function setFamily($family)
	{
		$this->family = $family;
	}

	public function setRelease($release)
	{
		$this->release = $release;
	}

	public function setHost($host)
	{
		$this->host = $host;
	}

	public function setToken($token)
	{
		$this->token = $token;
	}

	public function setVersion($version)
	{
		$this->version = $version;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}
}
