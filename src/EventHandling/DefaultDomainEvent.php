<?php

namespace CQRS\EventHandling;

use CQRS\Exception\RuntimeException;

/**
 * Default Implementation for the DomainEvent interface.
 *
 * Convenience DomainEvent that helps with construction by mapping an array input
 * to event properties. If a passed property does not exist on the class
 * an exception is thrown.
 *
 * @example
 *
 *   class GreetedDomainEvent extends DefaultDomainEvent
 *   {
 *      public $personId;
 *   }
 *   $event = new GreetedDomainEvent(['personId' => 1]);
 *   $eventBus->publish($event);
 */
abstract class DefaultDomainEvent implements DomainEvent
{
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->assertPropertyExists($key);
            $this->$key = $value;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $this->assertPropertyExists($name);
        return $this->$name;
    }

    /**
     * @param string $name
     * @throws RuntimeException
     */
    private function assertPropertyExists($name)
    {
        if (!property_exists($this, $name)) {
            $eventName = new EventName($this);
            throw new RuntimeException(sprintf(
                'Property "%s" is not a valid property on event "%s"',
                $name,
                $eventName
            ));
        }
    }
}