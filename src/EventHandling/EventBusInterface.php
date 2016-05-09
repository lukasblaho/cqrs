<?php

namespace CQRS\EventHandling;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\EventStream\EventStreamInterface;
use Generator;

interface EventBusInterface
{
    public function publish(EventMessageInterface $event);

    public function publishFromStream(EventStreamInterface $eventStream): Generator;
}
