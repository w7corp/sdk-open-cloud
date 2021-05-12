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

namespace W7\Api\Sms;

use W7\Sdk\Cloud\Request\We7Request;
use W7\Sdk\Cloud\Util\SiteInfoTraiter;

class Send extends We7Request
{
	use SiteInfoTraiter;

	protected $method  = 'sms.send';
	protected $apiPath = '/we7/sms/send';

	public function index($mobile, $content, $sign, $uniacid, $balance, $accountName, $data = [])
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}

		$params                 = $this->siteInfo->toArray();
		$params['method']       = $data['method'] ?? $this->method;
		$params['mobile']       = $mobile;
		$params['content']      = $content;
		$params['sms_sign']     = $sign;
		$params['uniacid']      = $uniacid;
		$params['balance']      = $balance;
		$params['account_name'] = $accountName;
		$params['data']         = $data;
		return parent::post($params);
	}
}
