<?php
namespace Jarvisho\TaiwanSmsLaravel\Services;

use GuzzleHttp\Client;
use Jarvisho\TaiwanSmsLaravel\Exceptions\InvalidSms;
use Jarvisho\TaiwanSmsLaravel\Services\Contract\BaseSms;

class Every8d extends BaseSms
{
    protected $client;
    protected $url;

    public function __construct()
    {
        if(empty(config('taiwan_sms.services.every8d.url'))) throw new InvalidSms('every8d need url');
        if(empty(config('taiwan_sms.services.every8d.username'))) throw new InvalidSms('every8d need username');
        if(empty(config('taiwan_sms.services.every8d.password'))) throw new InvalidSms('every8d need password');

        $this->client = new Client([
            'timeout' => config('taiwan_sms.timeout', 5),
            'connect_timeout' => config('taiwan_sms.timeout', 5),
        ]);
    }

    public function send(): array
    {
        $this->prepare();
        $response = $this->client->request('GET', $this->url);

        return [
            'code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents()
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
     * @return void
     * @throws InvalidSms
     */
    public function prepare(): void
    {
        if (empty($this->destination)) throw new InvalidSms('The empty destination is invalid.');
        if (empty($this->text)) throw new InvalidSms('The empty text is invalid.');
        if ($this->isGlobalPhoneNumber()) $this->destination = '0' . substr($this->destination, 3, 9);

        $params = [
            config('taiwan_sms.services.every8d.username'),
            config('taiwan_sms.services.every8d.password'),
            $this->subject,
            urlencode($this->text),
            $this->destination
        ];
        $this->url = sprintf(config('taiwan_sms.services.every8d.url'), ...$params);
    }

    public function test()
    {
        $this->prepare();

        return ['url' => $this->url];
    }
}
