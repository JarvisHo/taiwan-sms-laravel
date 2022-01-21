<?php

namespace Jarvisho\TaiwanSmsLaravel\Services;

use GuzzleHttp\Client;
use Jarvisho\TaiwanSmsLaravel\Exceptions\InvalidSms;
use Jarvisho\TaiwanSmsLaravel\Services\Contract\BaseSms;

class Kotsms extends BaseSms
{
    protected $client;
    public $url;

    public const CGI_ERROR = '-1';
    public const AUTH_ERROR = '-2';
    public const SENDER_ERROR = '-4';
    public const PHONE_ERROR = '-5';
    public const CLOSE_ERROR = '-6';
    public const SCHEDULE_TIME_ERROR = '-20';
    public const VALID_TIME_ERROR = '-21';
    public const NCC_ERROR = '-1000';
    public const PAYMENT_ERROR = '-59999';
    public const NO_POINT_ERROR = '-60002';
    public const REJECT_ERROR = '-60014';
    public const TAIWAN_IP_ONLY_ERROR = '-999949999';
    public const HOURS_ERROR = '-999959999';
    public const SAME_ERROR = '-999969999';
    public const LOCK_IP_ERROR = '-999979999';
    public const CONTENT_EMPTY_ERROR = '-999989999';
    public const ERRORS = [
        self::CGI_ERROR => '系統維護中或其他錯誤 ,帶入的參數異常,伺服器異常',
        self::AUTH_ERROR => '授權錯誤(帳號/密碼錯誤)',
        self::SENDER_ERROR => '發送端 870短碼VCSN 設定異常',
        self::PHONE_ERROR => '接收端 門號錯誤',
        self::CLOSE_ERROR => '接收端的門號停話異常090 094 099 付費代號等',
        self::SCHEDULE_TIME_ERROR => '預約時間錯誤 或時間已過',
        self::VALID_TIME_ERROR => '有效時間錯誤',
        self::NCC_ERROR => '發送內容違反 NCC 規範',
        self::PAYMENT_ERROR => '帳務系統異常 簡訊無法扣款送出',
        self::NO_POINT_ERROR => '您帳戶中的點數不足',
        self::REJECT_ERROR => '該用戶已申請 拒收簡訊平台之簡訊 ( 2010 NCC新規)',
        self::TAIWAN_IP_ONLY_ERROR => '境外IP限制(只接受台灣IP發送，欲申請過濾請洽簡訊王客服)',
        self::HOURS_ERROR => '在12 小時內，相同容錯機制碼',
        self::SAME_ERROR => '同秒, 同門號, 同內容簡訊',
        self::LOCK_IP_ERROR => '鎖定來源IP',
        self::CONTENT_EMPTY_ERROR => '簡訊為空',
    ];

    public function __construct()
    {
        if(empty(config('taiwan_sms.services.kotsms.url'))) throw new InvalidSms('kotsms need url');
        if(empty(config('taiwan_sms.services.kotsms.username'))) throw new InvalidSms('kotsms need username');
        if(empty(config('taiwan_sms.services.kotsms.password'))) throw new InvalidSms('kotsms need password');

        $this->client = new Client([
            'timeout' => config('taiwan_sms.timeout', 5),
            'connect_timeout' => config('taiwan_sms.timeout', 5),
        ]);
    }

    public function send(): array
    {
        $this->prepare();

        $response = $this->client->get($this->url);

        if ($response->getStatusCode() != 200) {
            throw new \Exception('Kotsms service failed');
        }

        $content = $this->getContent($response->getBody()->getContents());

        if (strpos($content, '=') !== false) {
            $code = explode('=', $content)[1];
            if(in_array($code, [
                self::CGI_ERROR,
                self::AUTH_ERROR,
                self::SENDER_ERROR,
                self::PHONE_ERROR,
                self::CLOSE_ERROR,
                self::SCHEDULE_TIME_ERROR,
                self::VALID_TIME_ERROR,
                self::NCC_ERROR,
                self::PAYMENT_ERROR,
                self::NO_POINT_ERROR,
                self::REJECT_ERROR,
                self::TAIWAN_IP_ONLY_ERROR,
                self::HOURS_ERROR,
                self::SAME_ERROR,
                self::LOCK_IP_ERROR,
                self::CONTENT_EMPTY_ERROR
            ])) {
                throw new \Exception(self::ERRORS[$code]);
            }
        }

        return [
            'code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents(),
        ];
    }

    /**
     * @param $content
     * @return string
     */
    public function getContent($content): string
    {
        $content = str_replace(' ', '', $content);
        $content = trim(preg_replace('/\s\s+/', ' ', $content));
        return $content;
    }

    /**
     * @return bool
     */
    public function isGlobalPhoneNumber(): bool
    {
        return strlen($this->destination) == 12 && substr($this->destination, 0, -9) == '886';
    }

    /**
     * @return void
     * @throws InvalidSms
     */
    public function prepare(): void
    {
        if (empty($this->destination)) throw new InvalidSms('The empty destination is invalid.');
        if (empty($this->text)) throw new InvalidSms('The empty text is invalid.');
        if ($this->isGlobalPhoneNumber()) $this->destination = '0' . substr($this->destination, 3, 9);

        $params = [
            config('taiwan_sms.services.kotsms.username'),
            config('taiwan_sms.services.kotsms.password'),
            $this->destination,
            urlencode(iconv(mb_detect_encoding($this->text), "big5", $this->text))
        ];
        $this->url = sprintf(config('taiwan_sms.services.kotsms.url'), ...$params);
    }

    public function test()
    {
        $this->prepare();

        return ['url' => $this->url];
    }
}
