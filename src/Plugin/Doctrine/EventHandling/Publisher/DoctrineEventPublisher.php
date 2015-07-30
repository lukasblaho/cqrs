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
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postFlush
        ];
    }

    public function publishEvents()
    {
        $this->events = array_merge($this->events, $this->dequeueEvents());
        // Actual event dispatching is postponed until doctrine's postFlush event.
    }

    public function postFlush()
    {
        $this->publishEvents();

        if (empty($this->events)) {
            return;
        }

        $events = $this->events;
        $this->events = [];
        $this->dispatchEvents($events);
    }
}