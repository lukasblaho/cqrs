<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use Generator;
use Ramsey\Uuid\UuidInterface;

interface EventStoreInterface
{
    public function store(EventMessageInterface $event);

    /**
     * @param int|null $offset
     * @param int $limit
     * @return EventMessageInterface[]
     */
    public function read(int $offset = null, int $limit = 10): array;

    public function iterate(UuidInterface $previousEventId = null): Generator;
}
