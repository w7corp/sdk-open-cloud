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

use W7\Sdk\OpenCloud\Exception\InstallProtectException;
use W7\Sdk\OpenCloud\Exception\ServiceExpireException;
use W7\Sdk\OpenCloud\Exception\ApiErrorException;

class Shipping
{
    use InstanceTraiter;

    public function decode($data, $fileContent)
    {
        if (Common::is_error($data)) {
            throw new ApiErrorException('网络传输错误, 请检查您的cURL是否可用, 或者服务器网络是否正常. ' . $data['message']);
        }

        if ('install-theme-protect' == $data || 'install-module-protect' == $data) {
            throw new InstallProtectException('此' . ('install-theme-protect' == $data ? '模板' : '模块') . '已设置版权保护，您只能通过云平台来安装，请先删除该模块的所有文件，购买后再行安装。');
        }

        $content = json_decode($data, true);

        if (!empty($content['error'])) {
            throw new ApiErrorException($content['error']);
        }

        if (!empty($content) && is_array($content)) {
            if (!empty($content['data']) && 'success' == $content['data']) {
                return true;
            }
            if (!empty($content[0]) && 'success' == $content[0]) {
                return true;
            }
            return $content;
        }

        if (32 != strlen($data)) {
            $message = Common::unserialize($data);
            if (is_array($message) && Common::is_error($message)) {
                throw new ApiErrorException($message['message']);
            }

            if ('patching' == $data) {
                throw new ApiErrorException('补丁程序正在更新中，请稍后再试！');
            }
            if ('frequent' == $data) {
                throw new ApiErrorException('更新操作太频繁，请稍后再试！');
            }
            if ('blacklist' == $data) {
                throw new ApiErrorException('抱歉，您的站点已被列入云服务黑名单，云服务一切业务已被禁止，请联系微擎客服！');
            }

            $shippingToken = '';
        } else {
            $shippingToken = $data;

            $data = $fileContent;
            if (empty($data)) {
                throw new ApiErrorException('没有接收到服务器的传输的数据，您可以尝试更新缓存后重试。');
            }
        }
        if (!is_array($data)) {
            $result = Common::unserialize($data);
            if (!isset($result['secret'])) {
                $result['secret'] = '';
            }

            if (empty($result) || $shippingToken != $result['secret']) {
                throw new ApiErrorException('云服务平台向您的服务器传输的数据校验失败, 可能是因为您的网络不稳定, 或网络不安全, 请稍后重试.');
            }
        } else {
            $result = $data;
        }

        $result = Common::unserialize($result['data']);

        if (is_array($result) && Common::is_error($result)) {
            if ('-3' == $result['errno']) { //模块升级服务到期
                throw new ServiceExpireException($result['message'], $result['errno']);
            }
        }
        if (!Common::is_error($result) && is_array($result)) {
            if (!empty($result) && !empty($result['state']) && 'fatal' == $result['state']) {
                throw new ApiErrorException('发生错误: ' . $result['message'], $result['errno']);
            }
            if (!empty($result[0]) && 'success' == $result[0]) {
                return true;
            }
            return $result;
        } else {
            throw new ApiErrorException('发生错误: ' . $result['message'], $result['errno']);
        }
    }
}
