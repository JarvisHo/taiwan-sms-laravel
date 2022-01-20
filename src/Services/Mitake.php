<?php

namespace Jarvisho\TaiwanSmsLaravel\Services;

use GuzzleHttp\Client;
use Jarvisho\TaiwanSmsLaravel\Exceptions\InvalidSms;
use Jarvisho\TaiwanSmsLaravel\Services\Contract\BaseSms;

class Mitake extends BaseSms
{
    protected $client;
    protected $url;

    public function __construct()
    {
        if(empty(config('taiwan_sms.mitake.url'))) throw new InvalidSms('mitake need url');
        if(empty(config('taiwan_sms.mitake.username'))) throw new InvalidSms('mitake need username');
        if(empty(config('taiwan_sms.mitake.password'))) throw new InvalidSms('mitake need password');

        $this->url = config('taiwan_sms.mitake.url');

        $this->client = new Client([
            'timeout' => config('taiwan_sms.timeout', 5),
            'connect_timeout' => config('taiwan_sms.timeout', 5),
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
            ]
        ]);
    }

    public function send(): array
    {
        if(empty($this->destination)) throw new InvalidSms('The empty destination is invalid.');
        if(empty($this->text)) throw new InvalidSms('The empty text is invalid.');
        if($this->isGlobalPhoneNumber()) $this->destination = '0' . substr($this->destination, 3, 9);

        $data = [
            'username'=> config('taiwan_sms.mitake.username'),
            'password'=> config('taiwan_sms.mitake.password'),
            'dstaddr' => $this->destination,
            'smbody' => $this->text,
        ];

        $response = $this->client->post($this->url, $data);

        if ($response->getStatusCode() != 200) {
            throw new InvalidSms('Mitake service failed');
        }

        return [
            'code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents(),
        ];
    }

    /**
     * @return bool
     */
    public function isGlobalPhoneNumber(): bool
    {
        return strlen($this->destination) == 12 && substr($this->destination, 0, -9) == '886';
    }
}
