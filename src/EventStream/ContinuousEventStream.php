<?php

namespace CQRS\EventStream;

use Generator;
use IteratorAggregate;
use Ramsey\Uuid\UuidInterface;

class ContinuousEventStream implements IteratorAggregate, EventStreamInterface
{
    /**
     * @var EventStreamInterface
     */
    private $eventStream;

    /**
     * @var int
     */
    private $pauseMicroseconds;

    public function __construct(EventStreamInterface $eventStream, int $pauseMicroseconds = 500000)
    {
        $this->eventStream = $eventStream;
        $this->pauseMicroseconds = $pauseMicroseconds;
    }

    /**
     * @return UuidInterface|null
     */
    public function getLastEventId()
    {
        return $this->eventStream->getLastEventId();
    }

    public function getIterator(): Generator
    {
        while (true) {
            foreach ($this->eventStream as $event) {
                yield $event;
            }
            usleep($this->pauseMicroseconds);
        }
    }
}
