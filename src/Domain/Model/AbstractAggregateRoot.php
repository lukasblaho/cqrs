<?php

namespace CQRS\Domain\Model;

use CQRS\Domain\Message\DomainEventMessageInterface;
use CQRS\Domain\Message\Metadata;
use CQRS\Domain\Payload\AbstractDomainEvent;
use CQRS\Exception\RuntimeException;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractAggregateRoot implements AggregateRootInterface
{
    /**
     * @var EventContainer|null
     */
    private $eventContainer;

    /**
     * @var bool
     */
    private $deleted = false;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @var int|null
     */
    private $lastEventSequenceNumber;

    /**
     * @ORM\Version
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @var int|null
     */
    private $version;

    /**
     * @return mixed
     */
    abstract public function getId();

    /**
     * Registers an event to be published when the aggregate is saved, containing the given payload and optional
     * metadata.
     *
     * @param mixed $payload
     * @param Metadata|array $metadata
     * @return DomainEventMessageInterface
     */
    protected function registerEvent($payload, $metadata = null): DomainEventMessageInterface
    {
        if ($payload instanceof AbstractDomainEvent && null === $payload->aggregateId) {
            $payload->setAggregateId($this->getId());
        }

        return $this->getEventContainer()
            ->addEvent($payload, $metadata);
    }

    protected function registerEventMessage(DomainEventMessageInterface $eventMessage): DomainEventMessageInterface
    {
        return $this->getEventContainer()
            ->addEventMessage($eventMessage);
    }

    /**
     * {@inheritdoc}
     *
     * @return DomainEventMessageInterface[]
     */
    public function getUncommittedEvents(): array
    {
        if ($this->eventContainer === null) {
            return [];
        }
        return $this->eventContainer->getEvents();
    }

    /**
     * {@inheritdoc}
     */
    public function getUncommittedEventsCount(): int
    {
        return count($this->eventContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function commitEvents()
    {
        if ($this->eventContainer !== null) {
            $this->lastEventSequenceNumber = $this->eventContainer->getLastSequenceNumber();
            $this->eventContainer->commit();
        }
    }

    /**
     * Marks this aggregate as deleted, instructing a Repository to remove that aggregate at an appropriate time
     */
    protected function markAsDeleted()
    {
        $this->deleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @return int|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return EventContainer
     * @throws RuntimeException
     */
    private function getEventContainer(): EventContainer
    {
        if ($this->eventContainer === null) {
            $aggregateId = $this->getId();
            $aggregateType = get_class($this);
            if ($aggregateId === null) {
                throw new RuntimeException(sprintf(
                    'Aggregate ID is unknown in %s. '
                    . 'Make sure the Aggregate ID is initialized before registering events.',
                    $aggregateType
                ));
            }

            $this->eventContainer = new EventContainer($aggregateType, $aggregateId);
            $this->eventContainer->initializeSequenceNumber($this->lastEventSequenceNumber);
        }
        return $this->eventContainer;
    }
}
