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

namespace W7\Sdk\OpenCloud\Api\Module;

use W7\Sdk\OpenCloud\Exception\ParamsErrorException;
use W7\Sdk\OpenCloud\Request\We7Request;
use W7\Sdk\OpenCloud\Util\InstanceTraiter;
use W7\Sdk\OpenCloud\Util\SiteInfoTraiter;

class Query extends We7Request
{
	protected $apiPath = '/module/query';
	protected $method  = 'module.query';

	protected $module = '';
	protected $page   = 0;

	use SiteInfoTraiter;
	use InstanceTraiter;

	public function get()
	{
		if (empty($this->siteInfo)) {
			throw new ParamsErrorException('缺少站点信息参数');
		}
		$data           = $this->siteInfo->toArray();
		$data['method'] = $this->method;
		$data['module'] = $this->module;
		if (!empty($this->page)) {
			$data['page'] = $this->page;
		}
		return parent::post($data);
	}

	/**
	 * @param int $page
	 * @return Query
	 */
	public function setPage(int $page)
	{
		$this->page = $page;
		return $this;
	}

	/**
	 * @param mixed $module
	 * @return Query
	 */
	public function setSyncModule($module)
	{
		$this->module = $module;
		return $this;
	}
}
