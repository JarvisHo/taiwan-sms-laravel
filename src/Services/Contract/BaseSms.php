<?php

namespace Jarvisho\TaiwanSmsLaravel\Services\Contract;

abstract class BaseSms
{
    /**
     * 接收SMS的手機號碼
     * @var string
     */
    protected $destination = '';

    /**
     * 簡訊內容
     * @var string
     */
    protected $text = '';
    protected $subject = '';

    abstract public function send(): array;

    public function setDestination($destination)
    {
        $destination = str_replace(['+', '-', ' '], '', $destination);
        $this->destination = $destination;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
}
