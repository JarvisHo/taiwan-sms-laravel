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
        if(empty(config('taiwan_sms.services.mitake.url'))) throw new InvalidSms('mitake need url');
        if(empty(config('taiwan_sms.services.mitake.username'))) throw new InvalidSms('mitake need username');
        if(empty(config('taiwan_sms.services.mitake.password'))) throw new InvalidSms('mitake need password');

        $this->url = config('taiwan_sms.services.mitake.url');

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
        $data = $this->prepare();

        $response = $this->client->post($this->url, $data);

        if ($response->getStatusCode() != 200) {
            throw new InvalidSms('Mitake service failed');
        }

        return [
            'code' => $response->getStatusCode(),
            'body' => mb_convert_encoding($response->getBody()->getContents(), 'UTF-8', 'BIG-5'),
        ];
    }

    /**
     * @return bool
     */
    public function isGlobalPhoneNumber(): bool
    {
        return strlen($this->destination) == 12 && substr($this->destination, 0, -9) == '886';
    }

    /**
     * @return array
     * @throws InvalidSms
     */
    public function prepare(): array
    {
        if (empty($this->destination)) throw new InvalidSms('The empty destination is invalid.');
        if (empty($this->text)) throw new InvalidSms('The empty text is invalid.');
        if ($this->isGlobalPhoneNumber()) $this->destination = '0' . substr($this->destination, 3, 9);

        return [
            'username' => config('taiwan_sms.services.mitake.username'),
            'password' => config('taiwan_sms.services.mitake.password'),
            'dstaddr' => $this->destination,
            'smbody' => iconv(mb_detect_encoding($this->text), "UTF-8", $this->text),
        ];
    }

    public function test()
    {
        $data = $this->prepare();

        return ['url' => $this->url, 'data' => http_build_query($data)];
    }

}
