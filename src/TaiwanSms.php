<?php
namespace Jarvisho\TaiwanSmsLaravel;

use Jarvisho\TaiwanSmsLaravel\Exceptions\InvalidSms;

class TaiwanSms
{
    public static function send($destination, $text, $test = false)
    {
        try {
            $response = self::process(self::getPrimaryClassName(), $text, $destination, $test);
        }catch (\Exception $exception) {
            if(empty(self::getFailoverClassName())) throw new InvalidSms($exception->getMessage());
            try {
                $response = self::process(self::getFailoverClassName(), $text, $destination, $test);
            }catch (\Exception $exception) {
                throw new InvalidSms($exception->getMessage());
            }
        }

        return $response;
    }

    /**
     * @param string $class
     * @param $text
     * @param $destination
     * @return array
     */
    public static function process(string $class, $text, $destination, $test = false): array
    {
        $api = new $class();
        $api->setText($text);
        $api->setDestination($destination);
        if($test) return $api->test();

        return $api->send();
    }

    /**
     * @return string
     * @throws InvalidSms
     */
    public static function getPrimaryClassName(): string
    {
        if(empty(config('taiwan_sms.primary'))) throw new InvalidSms('主要簡訊服務尚未設定');
        if(!array_key_exists(strtolower(config('taiwan_sms.primary')), data_get(config('taiwan_sms'), 'services', []))) throw new InvalidSms('主要簡訊服務不在名單中');
        return self::getClassPrefix() . ucfirst(strtolower(config('taiwan_sms.primary')));
    }

    /**
     * @return string
     * @throws InvalidSms
     */
    public static function getFailoverClassName(): string
    {
        if(empty(config('taiwan_sms.failover'))) return '';
        if(!array_key_exists(strtolower(config('taiwan_sms.failover')), data_get(config('taiwan_sms'), 'services', []))) throw new InvalidSms('備援簡訊服務不在名單中');
        return self::getClassPrefix() . ucfirst(strtolower(config('taiwan_sms.failover')));
    }

    public static function getClassPrefix(): string
    {
        $array = explode('\\', __CLASS__);
        array_pop($array);
        $class = implode('\\', $array);
        $class .= '\\Services\\';

        return $class;
    }
}
