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

namespace W7\Api\App\Publish;

use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class Download extends We7Request
{
	use SiteInfoTraiter;

	protected $apiPath = '/wxapp/publish/download';

	protected $name;
	protected $version;
	protected $support;

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @param mixed $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
		return $this;
	}

	public function setSupport($support)
	{
		$this->support = $support;
		return $this;
	}

	public function get()
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息');
		}
		if (empty($this->name)) {
			throw new \RuntimeException('缺少模块名称');
		}
		if (empty($this->version)) {
			throw new \RuntimeException('缺少模块版本');
		}
		if (empty($this->support)) {
			throw new \RuntimeException('缺少小程序类型');
		}
		$data           = $this->siteInfo->toArray();
		$data['module'] = [
			'name'    => $this->name,
			'version' => $this->version
		];
		$data['support'] = $this->support;

		return parent::post($data);
	}
}
