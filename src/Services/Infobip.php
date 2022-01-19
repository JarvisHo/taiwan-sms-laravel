<?php
namespace Jarvisho\TaiwanSmsLaravel\Services;

use GuzzleHttp\Client;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Jarvisho\TaiwanSmsLaravel\Services\Contract\BaseSms;

class Infobip extends BaseSms
{
    public const UNDELIVERABLE = 2;
    public const REJECTED = 5;
    protected $api;

    public function __construct()
    {
        if(empty(config('taiwan_sms.infobip.url'))) throw new \Exception('infobip need url');
        if(empty(config('taiwan_sms.infobip.username'))) throw new \Exception('infobip need username');
        if(empty(config('taiwan_sms.infobip.password'))) throw new \Exception('infobip need password');

        $configuration = (new Configuration())
            ->setHost(config('taiwan_sms.infobip.url'))
            ->setUsername(config('taiwan_sms.infobip.username'))
            ->setPassword(config('taiwan_sms.infobip.password'));

        $client = new Client([
            'timeout' => config('taiwan_sms.timeout', 5),
            'connect_timeout' => config('taiwan_sms.timeout', 5),
        ]);
        $this->api = new SendSmsApi($client, $configuration);
    }

    public function send(): array
    {
        $destination = (new SmsDestination())->setTo($this->destination);
        $message = (new SmsTextualMessage())
            ->setFrom($this->subject)
            ->setText($this->text)
            ->setDestinations([$destination]);
        $request = (new SmsAdvancedTextualRequest())
            ->setMessages([$message]);

        $response = $this->api->sendSmsMessage($request);

        if (count($response->getMessages()) == 0 || in_array($response->getMessages()[0]->getStatus()->getGroupId(), [self::UNDELIVERABLE, self::REJECTED])) {
            throw new \Exception('SMS Send failed');
        }

        return [
            'code' => 200,
            'body' => $response->getMessages()[0]->getStatus()->getDescription()
        ];
    }
}
