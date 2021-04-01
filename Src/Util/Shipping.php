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

namespace W7\Sdk\Cloud\Util;

use W7\Sdk\Cloud\Exception\InstallProtectException;
use W7\Sdk\Cloud\Exception\ServiceExpireException;

class Shipping {
	use InstanceTraiter;

	public function decode($data, $fileContent) {
		if (Common::is_error($data)) {
			throw new \RuntimeException('网络传输错误, 请检查您的cURL是否可用, 或者服务器网络是否正常. ' . $data['message']);
		}

		if ($data == 'install-theme-protect' || $data == 'install-module-protect') {
			throw new InstallProtectException('此' . ($data == 'install-theme-protect' ? '模板' : '模块') . '已设置版权保护，您只能通过云平台来安装，请先删除该模块的所有文件，购买后再行安装。');
		}

		$content = json_decode($data, true);

		if (!empty($content['error'])) {
			throw new \RuntimeException($content['error']);
		}

		if (!empty($content) && is_array($content)) {
			if (!empty($content['data']) && $content['data'] == 'success') {
				return true;
			}
			if (!empty($content[0]) && $content[0] == 'success') {
				return true;
			}
			return $content;
		}

		if (strlen($data) != 32) {
			$message = Common::unserialize($data);
			if (is_array($message) && Common::is_error($message)) {
				throw new \RuntimeException($message['message']);
			}

			if ($data == 'patching') {
				throw new \RuntimeException('补丁程序正在更新中，请稍后再试！');
			}
			if ($data == 'frequent') {
				throw new \RuntimeException('更新操作太频繁，请稍后再试！');
			}
			if ($data == 'blacklist') {
				throw new \RuntimeException('抱歉，您的站点已被列入云服务黑名单，云服务一切业务已被禁止，请联系微擎客服！');
			}

			$shippingToken = '';
		} else {
			$shippingToken = $data;

			$data = $fileContent;
			if (empty($data)) {
				throw new \RuntimeException('没有接收到服务器的传输的数据.');
			}
		}
		if (!is_array($data)) {
			$result = Common::unserialize($data);
			if (!isset($result['secret'])) {
				$result['secret'] = '';
			}

			if (empty($result) || $shippingToken != $result['secret']) {
				throw new \RuntimeException('云服务平台向您的服务器传输的数据校验失败, 可能是因为您的网络不稳定, 或网络不安全, 请稍后重试.');
			}
		} else {
			$result = $data;
		}

		$result = Common::unserialize($result['data']);

		if (is_array($result) && Common::is_error($result)) {
			if ($result['errno'] == '-3') { //模块升级服务到期
				throw new ServiceExpireException($result['message'], $result['errno']);
			}
		}
		if (!Common::is_error($result) && is_array($result)) {
			if (!empty($result) && !empty($result['state']) && $result['state'] == 'fatal') {
				throw new \RuntimeException('发生错误: ' . $result['message'], $result['errno']);
			}
			if (!empty($result[0]) && $result[0] == 'success') {
				return true;
			}
			return $result;
		} else {
			throw new \RuntimeException('发生错误: ' . $result['message'], $result['errno']);
		}
	}
}
