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

use function collect;

trait AppTrait
{
	protected $appId;
	protected $appSecret;

	public function withAppId($appId)
	{
		$this->appId = $appId;
		return $this;
	}

	public function withAppSecret($appSecret)
	{
		$this->appSecret = $appSecret;
		return $this;
	}

	protected function convertNullToEmptyString(array $data)
	{
		$data = collect($data)->map(function ($value) {
			if (is_null($value)) {
				return '';
			}
			return is_array($value) ? $this->convertNullToEmptyString($value) : $value;
		});

		return $data->toArray();
	}

	protected function sign($data)
	{
		if (isset($data['sign'])) {
			unset($data['sign']);
		}

		$data['appid'] = $this->appId;
		$data = $this->convertNullToEmptyString($data);

		ksort($data, SORT_STRING);
		reset($data);

		return md5(http_build_query($data, '', '&') . $this->appSecret);
	}
}
