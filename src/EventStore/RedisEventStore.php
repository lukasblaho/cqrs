<?php

namespace CQRS\EventStore;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Exception;
use CQRS\Serializer\SerializerInterface;
use Generator;
use Ramsey\Uuid\UuidInterface;
use Redis;
use Traversable;

class RedisEventStore implements EventStoreInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var string
     */
    private $key = 'cqrs_event';

    /**
     * @var int
     */
    private $size;

    public function __construct(SerializerInterface $serializer, Redis $redis, string $key = null, int $size = null)
    {
        $this->serializer = $serializer;
        $this->redis = $redis;

        if (null !== $key) {
            $this->key = $key;
        }

        if (null !== $size) {
            $this->size = $size;
        }
    }

    public function store(EventMessageInterface $event)
    {
        $record = RedisEventRecord::fromMessage($event, $this->serializer);
        $this->redis->lPush($this->key, (string) $record);

        if ($this->size > 0) {
            $this->redis->lTrim($this->key, 0, $this->size - 1);
        }
    }

    /**
     * @param int|null $offset
     * @param int $limit
     * @return EventMessageInterface[]
     */
    public function read(int $offset = null, int $limit = 10): array
    {
        if (null === $offset) {
            $offset = -10;
        }

        $records = $this->redis->lRange($this->key, $offset, $limit);

        return array_map(function ($data) {
            $record = new RedisEventRecord($data);
            return $record->toMessage($this->serializer);
        }, $records);
    }

    /**
     * @param int $timeout
     * @return RedisEventRecord|null
     */
    public function pop(int $timeout = 0)
    {
        $data = $this->redis->brPop($this->key, $timeout);

        if (!array_key_exists(1, $data)) {
            return null;
        }

        return new RedisEventRecord($data[1]);
    }

    public function iterate(UuidInterface $previousEventId = null): Generator
    {
        throw new Exception\BadMethodCallException('Method is not implemented');
    }
}
