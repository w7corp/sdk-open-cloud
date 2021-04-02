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

class WxApp extends We7Request
{
	use SiteInfoTraiter;

	protected $apiPath = '/wxapp/publish';

	/**
	 * 模块名称和版本，打包公众号时可以不传
	 */
	protected $name;
	protected $version;
	protected $ticket;

	/**
	 * 小程序 appid appjson
	 */
	protected $appId;
	protected $appJson = '';

	protected $toMiniProgram = [];

	/**
	 * 是否是预览
	 */
	protected $isPreview = false;

	/**
	 * 是否是打包公众号小程序
	 */
	protected $isAccountWxapp = false;

	/**
	 * 清除掉直播配置
	 * @var bool
	 */
	protected $clearLivePlayerPlugin = false;

	/**
	 * 正式发布时必须填入发布信息，
	 */
	protected $publishInfo = [
		'version'     => '',
		'description' => ''
	];

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

	/**
	 * @param mixed $ticket
	 */
	public function setTicket($ticket)
	{
		$this->ticket = $ticket;
		return $this;
	}

	/**
	 * @param mixed $appId
	 */
	public function setAppId($appId)
	{
		$this->appId = $appId;
		return $this;
	}

	/**
	 * @param mixed $appJson
	 */
	public function setAppJson(string $appJson)
	{
		$this->appJson = $appJson;
		return $this;
	}

	/**
	 * @param string $version
	 * @param string $description
	 */
	public function setPublishInfo(string $version, string $description)
	{
		$this->publishInfo = [
			'version'     => $version,
			'description' => $description
		];
		return $this;
	}

	public function enablePreivew()
	{
		$this->isPreview = true;
		return $this;
	}

	public function enableAccountWxapp()
	{
		$this->isAccountWxapp = true;
		return $this;
	}

	public function enableClearLivePlayerPlugin()
	{
		$this->clearLivePlayerPlugin = true;
		return $this;
	}

	/**
	 * @param array $toMiniProgram
	 */
	public function setToMiniProgram(array $toMiniProgram)
	{
		$this->toMiniProgram = $toMiniProgram;
		return $this;
	}

	public function get()
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息');
		}
		if (empty($this->isPreview)) {
			if (empty($this->publishInfo['version']) || empty($this->publishInfo['description'])) {
				throw new \RuntimeException('非预览缺少发布信息');
			}
		}

		if (empty($this->isAccountWxapp)) {
			if (empty($this->name) || empty($this->version)) {
				throw new \RuntimeException('缺少模块发布信息');
			}
		}

		$data           = $this->siteInfo->toArray();
		$data['module'] = [
			'name'    => $this->name,
			'version' => $this->version
		];
		$data['publish'] = [
			'version'     => $this->publishInfo['version'],
			'description' => $this->publishInfo['description'],
		];
		$data['preview']                  = $this->isPreview;
		$data['clear_live_player_plugin'] = $this->clearLivePlayerPlugin;
		$data['wxapp_type']               = $this->isAccountWxapp;
		$data['tominiprogram']            = $this->toMiniProgram;
		$data['appjson']                  = $this->appJson;
		$data['appid']                    = $this->appId;
		$data['ticket']                   = $this->ticket;

		return parent::post($data);
	}
}
