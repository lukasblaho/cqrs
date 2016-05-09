<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Exception;
use Generator;
use Ramsey\Uuid\UuidInterface;

class NullEventStore implements EventStoreInterface
{
    public function store(EventMessageInterface $event)
    {
    }

    public function read(int $offset = null, int $limit = 10): array
    {
        return [];
    }

    public function iterate(UuidInterface $previousEventId = null): Generator
    {
        throw new Exception\BadMethodCallException('Method is not implemented');
    }
}
