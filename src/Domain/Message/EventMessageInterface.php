<?php

namespace CQRS\Domain\Message;

interface EventMessageInterface extends MessageInterface
{
    public function getTimestamp(): Timestamp;
}
