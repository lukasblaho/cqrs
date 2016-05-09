<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Exception;
use Generator;
use Ramsey\Uuid\UuidInterface;

class ChainingEventStore implements EventStoreInterface
{
    /**
     * @var EventStoreInterface[]
     */
    private $eventStores;

    /**
     * @param EventStoreInterface[] $eventStores
     */
    public function __construct(array $eventStores)
    {
        $this->eventStores = $eventStores;
    }

    public function store(EventMessageInterface $event)
    {
        foreach ($this->eventStores as $eventStore) {
            $eventStore->store($event);
        }
    }

    public function read(int $offset = null, int $limit = 10): array
    {
        throw new Exception\BadMethodCallException(sprintf('%s does not support reading', self::class));
    }

    public function iterate(UuidInterface $previousEventId = null): Generator
    {
        throw new Exception\BadMethodCallException(sprintf('%s does not support iterating', self::class));
    }
}
