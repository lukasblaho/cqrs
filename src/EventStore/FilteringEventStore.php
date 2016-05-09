<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use Generator;
use Ramsey\Uuid\UuidInterface;

class FilteringEventStore implements EventStoreInterface
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * @var EventFilterInterface
     */
    private $filter;

    public function __construct(EventStoreInterface $eventStore, EventFilterInterface $filter)
    {
        $this->eventStore = $eventStore;
        $this->filter = $filter;
    }

    public function store(EventMessageInterface $event)
    {
        if ($this->filter->isValid($event)) {
            $this->eventStore->store($event);
        }
    }

    public function read(int $offset = null, int $limit = 10): array
    {
        return $this->eventStore->read($offset, $limit);
    }

    public function iterate(UuidInterface $previousEventId = null): Generator
    {
        return $this->eventStore->iterate($previousEventId);
    }
}
