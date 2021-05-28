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

namespace W7\Sdk\OpenCloud\Api\Module\Setting;

use W7\Sdk\OpenCloud\Api\Module\Build;

class Load extends Build
{
	protected $apiPath = '/module/setting/index';
	protected $acid;

	public function get()
	{
		$this->postData['acid']        = $this->acid;
		$this->postData['module_name'] = $this->name;
		return parent::get();
	}

	/**
	 * @param $acid
	 * @return Load
	 */
	public function setAcid($acid)
	{
		$this->acid = $acid;
		return $this;
	}
}
