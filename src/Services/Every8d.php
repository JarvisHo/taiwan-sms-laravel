<?php
namespace Jarvisho\TaiwanSmsLaravel\Services;

use GuzzleHttp\Client;
use Jarvisho\TaiwanSmsLaravel\Services\Contract\BaseSms;

class Every8d extends BaseSms
{
    protected $client;
    protected $url;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('taiwan_sms.timeout', 5),
            'connect_timeout' => config('taiwan_sms.timeout', 5),
        ]);
    }

    public function send(): array
    {
        if(empty(config('taiwan_sms.every8d.url'))) throw new \Exception('every8d need url');
        if(empty(config('taiwan_sms.every8d.username'))) throw new \Exception('every8d need username');
        if(empty(config('taiwan_sms.every8d.password'))) throw new \Exception('every8d need password');

        $params = [
            config('taiwan_sms.every8d.username'),
            config('taiwan_sms.every8d.password'),
            $this->subject,
            $this->text,
            $this->destination
        ];
        $this->url = sprintf(config('taiwan_sms.every8d.url'), ...$params);
        $response = $this->client->request('GET', $this->url);

        return [
            'code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents()
        ];
    }
}
