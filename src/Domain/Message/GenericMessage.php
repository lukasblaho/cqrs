<?php

namespace CQRS\Domain\Message;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GenericMessage implements MessageInterface
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var string
     */
    private $payloadType;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @param mixed $payload
     * @param Metadata|array|null $metadata
     * @param UuidInterface|null $id
     */
    public function __construct($payload, $metadata = null, UuidInterface $id = null)
    {
        $this->id = $id ?: Uuid::uuid4();
        $this->payloadType = get_class($payload);
        $this->payload = $payload;
        $this->metadata = Metadata::from($metadata);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'payloadType' => $this->payloadType,
            'payload' => $this->payload,
            'metadata' => $this->metadata,
        ];
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getPayloadType(): string
    {
        return $this->payloadType;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function addMetadata(Metadata $metadata): self
    {
        $metadata = $this->metadata->mergedWith($metadata);

        if ($metadata === $this->metadata) {
            return $this;
        }

        $message = clone $this;
        $message->metadata = $metadata;
        return $message;
    }
}
