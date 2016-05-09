<?php

namespace CQRS\Domain\Message;

use DateTimeImmutable;
use DateTimeZone;
use JsonSerializable;

class Timestamp extends DateTimeImmutable implements JsonSerializable
{
    const FORMAT = 'Y-m-d\TH:i:s.uP';

    public function __construct(string $time = null, DateTimeZone $timezone = null)
    {
        if ($time === null) {
            $t = microtime(true);
            $micro = sprintf('%06d', ($t - floor($t)) * 1000000);
            $time = date('Y-m-d H:i:s.' . $micro, $t);
        }
        parent::__construct($time, $timezone);
    }

    public function getTimestampWithMicroseconds(): float
    {
        return (float) $this->format('U.u');
    }

    public function __toString(): string
    {
        return $this->format(static::FORMAT);
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
