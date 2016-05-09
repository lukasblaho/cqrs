<?php

namespace CQRS\Plugin\Doctrine\EventHandling\Publisher;

use CQRS\Domain\Message\DomainEventMessageInterface;
use CQRS\EventHandling\Publisher\SimpleEventPublisher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class DoctrineEventPublisher extends SimpleEventPublisher implements EventSubscriber
{
    /**
     * @var DomainEventMessageInterface[]
     */
    private $events = [];

    /**
     * Returns an array of events this subscriber wants to listen to.
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::preFlush,
            Events::postFlush,
        ];
    }

    public function publishEvents()
    {
        $this->events = array_merge($this->events, $this->dequeueEvents());
        // Actual event dispatching is postponed until doctrine's postFlush event.
    }

    public function preFlush()
    {
        $this->publishEvents();
    }

    public function postFlush()
    {
        if (count($this->events) === 0) {
            return;
        }

        $events = $this->events;
        $this->events = [];
        $this->dispatchEvents($events);
    }
}
