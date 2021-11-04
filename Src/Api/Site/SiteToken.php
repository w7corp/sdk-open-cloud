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

namespace W7\Sdk\OpenCloud\Api\Site;

use W7\Sdk\OpenCloud\Exception\ParamsErrorException;
use W7\Sdk\OpenCloud\Request\We7Request;
use W7\Sdk\OpenCloud\Util\SiteInfoTraiter;

class SiteToken extends We7Request
{
    use SiteInfoTraiter;

    protected $method  = 'application.token';
    protected $apiPath = '/site/token/index';

    public function get()
    {
        if (empty($this->siteInfo)) {
            throw new ParamsErrorException('缺少站点信息参数');
        }

        $data           = $this->siteInfo->toArray();
        $data['method'] = $data['method'] ?? $this->method;
        return parent::post($data);
    }
}
