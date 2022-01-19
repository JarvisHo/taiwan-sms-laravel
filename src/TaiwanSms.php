<?php
namespace Jarvisho\TaiwanSmsLaravel;

class TaiwanSms
{
    public static function send($destination, $text)
    {
        try {
            $response = self::process(self::getPrimaryClassName(), $text, $destination);
        }catch (\Exception $exception) {
            if(empty(self::getFailoverClassName())) throw new \Exception($exception->getMessage());
            try {
                $response = self::process(self::getFailoverClassName(), $text, $destination);
            }catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
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
    public static function process(string $class, $text, $destination): array
    {
        $api = new $class();
        $api->setText($text);
        $api->setDestination($destination);
        return $api->send();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getPrimaryClassName(): string
    {
        if(empty(config('taiwan_sms.primary'))) throw new \Exception('SMS config error');
        return self::getClassPrefix() . ucfirst(strtolower(config('taiwan_sms.primary')));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getFailoverClassName(): string
    {
        if(empty(config('taiwan_sms.failover'))) return '';
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
