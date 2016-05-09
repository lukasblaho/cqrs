<?php

namespace CQRS\Domain\Message;

interface DomainEventMessageInterface extends EventMessageInterface
{
    public function getAggregateType(): string;

    /**
     * @return mixed
     */
    public function getAggregateId();

    public function getSequenceNumber(): int;
}
