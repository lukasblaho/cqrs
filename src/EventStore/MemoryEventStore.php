<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use Generator;
use Ramsey\Uuid\UuidInterface;

class MemoryEventStore implements EventStoreInterface
{
    /**
     * @var EventMessageInterface[]
     */
    private $events;

    public function store(EventMessageInterface $event)
    {
        $this->events[] = $event;
    }

    public function read(int $offset = null, int $limit = 10): array
    {
        return array_slice($this->events, $offset, $limit);
    }

    public function iterate(UuidInterface $previousEventId = null): Generator
    {
        $yield = !$previousEventId;
        foreach ($this->events as $event) {
            if ($yield) {
                yield $event;
            } elseif ($event->getId()->equals($previousEventId)) {
                $yield = true;
            }
        }
    }
}
