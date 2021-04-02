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

namespace W7\Api\Module\Setting;

class Save extends Load
{
	protected $apiPath = '/module/setting/save';
	protected $setting;

	public function get()
	{
		if (empty($this->setting)) {
			throw new \RuntimeException('请设置配置参数');
		}
		$this->postData['setting'] = $this->setting;
		return parent::get(); // TODO: Change the autogenerated stub
	}

	public function setSetting($setting)
	{
		$this->setting = $setting;
		return $this;
	}
}
