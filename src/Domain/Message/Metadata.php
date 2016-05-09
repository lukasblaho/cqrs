<?php

namespace CQRS\Domain\Message;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Countable;
use CQRS\Exception\RuntimeException;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

class Metadata implements IteratorAggregate, ArrayAccess, Countable, Serializable, JsonSerializable
{
    /**
     * @var self
     */
    private static $emptyInstance;

    /**
     * @var array
     */
    private $values;

    public static function emptyInstance(): self
    {
        if (static::$emptyInstance === null) {
            static::$emptyInstance = new static();
        }
        return static::$emptyInstance;
    }

    public static function resetEmptyInstance()
    {
        static::$emptyInstance = null;
    }

    public static function from($metadata = null): self
    {
        if ($metadata instanceof static) {
            return $metadata;
        }
        if ($metadata === null || $metadata === []) {
            return static::emptyInstance();
        }
        return new static($metadata);
    }

    public static function jsonDeserialize(array $data): self
    {
        return new static($data);
    }

    private function __construct(array $values = [])
    {
        ksort($values);
        $this->values = $values;
    }

    public function jsonSerialize(): ArrayObject
    {
        return new ArrayObject($this->toArray());
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->values[$offset] ?? null;
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @throws RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Event metadata is immutable.');
    }

    /**
     * @param string $offset
     * @throws RuntimeException
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Event metadata is immutable.');
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function serialize(): string
    {
        return serialize($this->values);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->values = unserialize($serialized);
    }

    /**
     * Returns a Metadata instance containing values of this, combined with the given additionalMetadata.
     * If any entries have identical keys, the values from the additionalMetadata will take precedence.
     *
     * @param Metadata $additionalMetadata
     * @return Metadata
     */
    public function mergedWith(Metadata $additionalMetadata): self
    {
        $values = array_merge($this->values, $additionalMetadata->values);

        if ($values === $this->values) {
            return $this;
        }

        return new static($values);
    }

    /**
     * Returns a Metadata instance with the items with given keys removed. Keys for which there is no
     * assigned value are ignored.
     * This Metadata instance is not influenced by this operation.
     *
     * @param array $keys
     * @return Metadata
     */
    public function withoutKeys(array $keys): self
    {
        $values = array_diff_key($this->values, array_flip($keys));

        if ($values === $this->values) {
            return $this;
        }

        return new static($values);
    }
}
