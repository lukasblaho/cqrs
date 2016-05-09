<?php

namespace CQRS\EventHandling\Publisher;

use CQRS\Domain\Message\EventMessageInterface;
use CQRS\Domain\Message\Metadata;
use CQRS\EventHandling\EventBusInterface;
use CQRS\EventStore\EventStoreInterface;
use CQRS\EventStore\NullEventStore;

class SimpleEventPublisher implements EventPublisherInterface
{
    /**
     * @var EventBusInterface
     */
    private $eventBus;

    /**
     * @var EventQueueInterface
     */
    private $queue;

    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * @var Metadata
     */
    private $additionalMetadata;

    /**
     * @param EventBusInterface $eventBus
     * @param EventQueueInterface|null $queue
     * @param EventStoreInterface|null $eventStore
     * @param Metadata|array|null $additionalMetadata
     */
    public function __construct(
        EventBusInterface $eventBus,
        EventQueueInterface $queue = null,
        EventStoreInterface $eventStore = null,
        $additionalMetadata = null
    ) {
        $this->eventBus = $eventBus;
        $this->queue = $queue;
        $this->eventStore = $eventStore ?? new NullEventStore();
        $this->additionalMetadata = Metadata::from($additionalMetadata);
    }

    public function getEventBus(): EventBusInterface
    {
        return $this->eventBus;
    }

    /**
     * @param Metadata|array $additionalMetadata
     */
    public function setAdditionalMetadata($additionalMetadata)
    {
        $this->additionalMetadata = Metadata::from($additionalMetadata);
    }

    public function getAdditionalMetadata(): Metadata
    {
        return $this->additionalMetadata;
    }

    public function publishEvents()
    {
        $this->dispatchEvents($this->dequeueEvents());
    }

    /**
     * @return EventMessageInterface[]
     */
    protected function dequeueEvents(): array
    {
        if (!$this->queue) {
            return [];
        }

        $events = $this->queue->dequeueAllEvents();
        foreach ($events as &$event) {
            $event = $event->addMetadata($this->additionalMetadata);
        }
        return $events;
    }

    /**
     * @param EventMessageInterface[] $events
     */
    protected function dispatchEvents(array $events)
    {
        foreach ($events as $event) {
            $this->eventStore->store($event);
            $this->eventBus->publish($event);
        }
    }
}
