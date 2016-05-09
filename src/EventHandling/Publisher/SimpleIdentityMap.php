<?php

namespace CQRS\EventHandling\Publisher;

use CQRS\Domain\Model\AggregateRootInterface;

class SimpleIdentityMap implements IdentityMapInterface
{
    /**
     * @var AggregateRootInterface[]
     */
    private $aggregateRoots = [];

    /**
     * @param mixed $id
     * @return AggregateRootInterface|null
     */
    public function get($id)
    {
        $key = (string) $id;
        return $this->aggregateRoots[$key];
    }

    /**
     * @return AggregateRootInterface[]
     */
    public function getAll(): array
    {
        return $this->aggregateRoots;
    }

    public function add(AggregateRootInterface $aggregateRoot)
    {
        $key = (string) $aggregateRoot->getId();
        $this->aggregateRoots[$key] = $aggregateRoot;
    }

    public function remove(AggregateRootInterface $aggregateRoot)
    {
        $key = (string) $aggregateRoot->getId();
        unset($this->aggregateRoots[$key]);
    }

    public function clear()
    {
        $this->aggregateRoots = [];
    }
}
