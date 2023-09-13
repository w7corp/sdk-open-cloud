<?php

namespace W7\Sdk\OpenCloud\Api\Thirdparty;

use PHPUnit\Framework\TestCase;

class ThirdPartPayTest extends TestCase
{
    const APPID = '';
    const APPSECRET = '';
    const AGENT = "";

    public function testScanPay()
    {
        //        $fromPayAppAccount = Str::startsWith('paa', self::APPID);
        //        dd($fromPayAppAccount);
        $scanPay = ThirdPartPay::instance()
            ->withAppId(self::APPID)
            ->withAppSecret(self::APPSECRET)
            ->withHeader('User-Agent', self::AGENT)
            ->scanPay([
                'var_user_id' => 183516,
                'openid' => 'QNVpLHOAoPHe1W9AU8o7rQ',
                'order_sn' => 'order_sn_111',
                'amount' => 0.1,
                'body' => '调试',
                'detail' => '调试扫码支付',
                'origin_pay_appid' => 'wa84a416471a6e8e1f',
            ]);

        var_dump($scanPay);
        self::assertArrayHasKey('payinfo', $scanPay);
        self::assertArrayHasKey('ticket', $scanPay['payinfo']);
    }

    public function testPayLog()
    {
        $appid = 'paa20230306145430uljvkt5x';
        $appsecret = '3710PXVUO96XGjoR4vEHWIzU/ziovajbcJWCjiPocuLjX/gGKZLM2XwrlTf52lpxiYHoSWWZ';
        $result = ThirdPartPay::instance()
            ->withAppId(self::APPID)
            ->withAppSecret(self::APPSECRET)
            ->withHeader('User-Agent', self::AGENT)
            ->payLog([
                'paylog_sn' => '20230824113952-TPXUAXVL',
            ]);

        var_dump($result);
    }

    public function testRefund()
    {
        $refundResult = ThirdPartPay::instance()
            ->withAppId(self::APPID)
            ->withAppSecret(self::APPSECRET)
            ->withHeader('User-Agent', self::AGENT)
            ->refund([
                'pay_order_sn' => '20230824113952-TPXUAXVL',
                'refund_order_sn' => 'aaa',
                'refund_fee' => 0.1,
                'refund_reason' => '测试退款2',
                'refund_remark' => '测试退款remark2',
            ]);

        var_dump($refundResult);
    }

    public function testRefundLog()
    {
        $appid = 'paa20230306145430uljvkt5x';
        $appsecret = '3710PXVUO96XGjoR4vEHWIzU/ziovajbcJWCjiPocuLjX/gGKZLM2XwrlTf52lpxiYHoSWWZ';
        $refundResult = ThirdPartPay::instance()
            ->withAppId(self::APPID)
            ->withAppSecret(self::APPSECRET)
            ->withHeader('User-Agent', self::AGENT)
            ->refundLog([
//                'refund_out_sn' => 'aaa',
                'refund_order_sn' => 'aaa',
            ]);

        var_dump($refundResult);
    }
}
