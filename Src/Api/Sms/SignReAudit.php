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

class SignReAudit extends We7Request
{
	use SiteInfoTraiter;

	protected $method  = 'sms.sign_re_audit';
	protected $apiPath = '/we7/sms/re-audit-sign';

	public function reAudit($signId, $smsSign)
	{
		if (empty($this->siteInfo)) {
			throw new \RuntimeException('缺少站点信息参数');
		}

		$data             = $this->siteInfo->toArray();
		$data['method']   = $data['method'] ?? $this->method;
		$data['sign_id']  = $signId;
		$data['sms_sign'] = $smsSign;

		return parent::post($data);
	}
}
