<?php

namespace Jarvisho\TaiwanSmsLaravel\Tests\Feature;

use Illuminate\Support\Str;
use Jarvisho\TaiwanSmsLaravel\TaiwanSms;
use Jarvisho\TaiwanSmsLaravel\Tests\TestCase;

class TaiwanSmsTest extends TestCase
{
    /** @test */
    function kotsms()
    {
        $username = Str::random(8);
        $password = Str::random(8);
        $phone = '09' . random_int(10000,99999) . random_int(100,999);
        $text = Str::random(8);

        config(['taiwan_sms' => ['primary' => 'kotsms',
            'failover' => '',
            'timeout' => 5,
            'services' => [
                'kotsms' => [
                    'url' => env('KOTSMS_URL', 'https://api.kotsms.com.tw/kotsmsapi-1.php?username=%s&password=%s&dstaddr=%s&smbody=%s&response='),
                    'username' => $username,
                    'password' => $password,
                ]
            ]]]);

        $result = TaiwanSms::send($phone, $text, true);
        expect(data_get($result, 'url'))->toBe('https://api.kotsms.com.tw/kotsmsapi-1.php?username='. $username .'&password='. $password . '&dstaddr='. $phone .'&smbody=' . $text . '&response=');
    }

    /** @test */
    function every8d()
    {
        $username = Str::random(8);
        $password = Str::random(8);
        $phone = '09' . random_int(10000,99999) . random_int(100,999);
        $text = Str::random(8);

        config(['taiwan_sms' => ['primary' => 'every8d',
            'failover' => '',
            'timeout' => 5,
            'services' => [
                'every8d' => [
                    'url' => env('EVERY8D_URL', 'http://biz3.every8d.com.tw/prepaid/API21/HTTP/sendSMS.ashx?UID=%s&PWD=%s&SB=%s&MSG=%s&DEST=%s'),
                    'username' => $username,
                    'password' => $password,
                ]
            ]]]);

        $result = TaiwanSms::send($phone, $text, true);
        expect(data_get($result, 'url'))->toBe('http://biz3.every8d.com.tw/prepaid/API21/HTTP/sendSMS.ashx?UID='. $username .'&PWD='. $password . '&SB=&MSG=' . $text . '&DEST=' . $phone);
    }

    /** @test */
    function mitake()
    {
        $username = Str::random(8);
        $password = Str::random(8);
        $phone = '09' . random_int(10000,99999) . random_int(100,999);
        $text = Str::random(8);

        config(['taiwan_sms' => ['primary' => 'mitake',
            'failover' => '',
            'timeout' => 5,
            'services' => [
                'mitake' => [
                    'url' => env('MITAKE_URL', 'https://sms.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8'),
                    'username' => $username,
                    'password' => $password,
                ]
            ]]]);

        $result = TaiwanSms::send($phone, $text, true);
        expect(data_get($result, 'url'))->toBe('https://sms.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8');
        expect(data_get($result, 'data'))->toBe('username='. $username .'&password='. $password . '&dstaddr=' . $phone . '&smbody=' . $text);
    }

    /** @test */
    function infobip()
    {
        $username = Str::random(8);
        $password = Str::random(8);
        $phone = '8869' . random_int(10000,99999) . random_int(100,999);
        $text = Str::random(8);

        config(['taiwan_sms' => ['primary' => 'infobip',
            'failover' => '',
            'timeout' => 5,
            'services' => [
                'infobip' => [
                    'url' => env('INFOBIP_URL', 'https://vqlkm.api.infobip.com'),
                    'username' => $username,
                    'password' => $password,
                ]
            ]]]);

        $result = TaiwanSms::send($phone, $text, true);
        expect(data_get($result, 'data'))->toBe('[{"destinations":[{"to":"' . $phone . '"}],"from":"","text":"'. $text .'"}]');
    }

    /** @test */
    function getPrimaryClassNameTest()
    {
        config(['taiwan_sms' => ['primary' => 'every8d',
            'failover' => '',
            'timeout' => 5,
            'services' => [
                'every8d' => [
                    'url' => env('EVERY8D_URL', 'http://biz3.every8d.com.tw/prepaid/API21/HTTP/sendSMS.ashx?UID=%s&PWD=%s&SB=%s&MSG=%s&DEST=%s'),
                    'username' => env('EVERY8D_USERNAME'),
                    'password' => env('EVERY8D_PASSWORD'),
                ],
                'kotsms' => [
                    'url' => env('KOTSMS_URL', 'https://api.kotsms.com.tw/kotsmsapi-1.php?username=%s&password=%s&dstaddr=%s&smbody=%s&response='),
                    'username' => env('KOTSMS_USERNAME'),
                    'password' => env('KOTSMS_PASSWORD'),
                ],
                'infobip' => [
                    'url' => env('INFOBIP_URL', 'https://vqlkm.api.infobip.com'),
                    'username' => env('INFOBIP_USERNAME'),
                    'password' => env('INFOBIP_PASSWORD'),
                ],
                'mitake' => [
                    'url' => env('MITAKE_URL', 'https://sms.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8'),
                    'username' => env('MITAKE_USERNAME'),
                    'password' => env('MITAKE_PASSWORD'),
                ]
            ]]]);
        expect(TaiwanSms::getPrimaryClassName())->toBe('Jarvisho\TaiwanSmsLaravel\Services\Every8d');
    }

    /** @test */
    function getFailoverClassName()
    {
        config(['taiwan_sms' => ['primary' => 'every8d',
            'failover' => 'kotsms',
            'timeout' => 5,
            'services' => [
                'every8d' => [
                    'url' => env('EVERY8D_URL', 'http://biz3.every8d.com.tw/prepaid/API21/HTTP/sendSMS.ashx?UID=%s&PWD=%s&SB=%s&MSG=%s&DEST=%s'),
                    'username' => env('EVERY8D_USERNAME'),
                    'password' => env('EVERY8D_PASSWORD'),
                ],
                'kotsms' => [
                    'url' => env('KOTSMS_URL', 'https://api.kotsms.com.tw/kotsmsapi-1.php?username=%s&password=%s&dstaddr=%s&smbody=%s&response='),
                    'username' => env('KOTSMS_USERNAME'),
                    'password' => env('KOTSMS_PASSWORD'),
                ],
                'infobip' => [
                    'url' => env('INFOBIP_URL', 'https://vqlkm.api.infobip.com'),
                    'username' => env('INFOBIP_USERNAME'),
                    'password' => env('INFOBIP_PASSWORD'),
                ],
                'mitake' => [
                    'url' => env('MITAKE_URL', 'https://sms.mitake.com.tw/b2c/mtk/SmSend?CharsetURL=UTF-8'),
                    'username' => env('MITAKE_USERNAME'),
                    'password' => env('MITAKE_PASSWORD'),
                ]
            ]]]);
        expect(TaiwanSms::getFailoverClassName())->toBe('Jarvisho\TaiwanSmsLaravel\Services\Kotsms');
    }

    /** @test */
    function if_can_get_class_prefix()
    {
        expect(TaiwanSms::getClassPrefix())->toBe('Jarvisho\TaiwanSmsLaravel\Services\\');
    }
}

