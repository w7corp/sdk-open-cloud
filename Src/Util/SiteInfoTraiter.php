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

namespace W7\Sdk\OpenCloud\Util;

use W7\Sdk\OpenCloud\Message\SiteInfo;

trait SiteInfoTraiter
{
    /**
     * @var SiteInfo
     */
    protected $siteInfo;

    /**
     * @param SiteInfo $siteInfo
     * @return $this
     */
    public function setSiteInfo(SiteInfo $siteInfo)
    {
        $this->siteInfo = $siteInfo;
        return $this;
    }
}
