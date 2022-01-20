<?php
namespace Jarvisho\TaiwanSmsLaravel\Services;

use GuzzleHttp\Client;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Jarvisho\TaiwanSmsLaravel\Exceptions\InvalidSms;
use Jarvisho\TaiwanSmsLaravel\Services\Contract\BaseSms;

class Infobip extends BaseSms
{
    public const UNDELIVERABLE = 2;
    public const REJECTED = 5;
    protected $api;

    public function __construct()
    {
        if(empty(config('taiwan_sms.services.infobip.url'))) throw new InvalidSms('infobip need url');
        if(empty(config('taiwan_sms.services.infobip.username'))) throw new InvalidSms('infobip need username');
        if(empty(config('taiwan_sms.services.infobip.password'))) throw new InvalidSms('infobip need password');

        $configuration = (new Configuration())
            ->setHost(config('taiwan_sms.services.infobip.url'))
            ->setUsername(config('taiwan_sms.services.infobip.username'))
            ->setPassword(config('taiwan_sms.services.infobip.password'));

        $client = new Client([
            'timeout' => config('taiwan_sms.timeout', 5),
            'connect_timeout' => config('taiwan_sms.timeout', 5),
        ]);
        $this->api = new SendSmsApi($client, $configuration);
    }

    public function send(): array
    {
        if(empty($this->destination)) throw new InvalidSms('The empty destination is invalid.');
        if(empty($this->text)) throw new InvalidSms('The empty text is invalid.');
        if($this->isTaiwanPhoneNumber()) $this->destination = '886' . substr($this->destination, 1, 9);


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

    /**
     * @return bool
     */
    public function isTaiwanPhoneNumber(): bool
    {
        return strlen($this->destination) == 10 && substr($this->destination, 0, -8) == '09';
    }
}
