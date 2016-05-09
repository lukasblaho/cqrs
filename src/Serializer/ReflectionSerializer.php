<?php

namespace CQRS\Serializer;

use DateTime;
use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;
use ReflectionProperty;

class ReflectionSerializer implements SerializerInterface
{
    /**
     * @var ReflectionClass[]
     */
    private $classes = [];

    /**
     * @var ReflectionProperty[][]
     */
    private $reflectionProperties = [];

    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data): string
    {
        return json_encode($this->toPhpClassArray($data));
    }

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type)
    {
        return $this->fromArray(json_decode($data, true));
    }


    /**
     * @param mixed $object
     * @return array
     */
    private function toPhpClassArray($object): array
    {
        $data = $this->toArray($object);

        foreach ($data as &$value) {
            if (is_object($value)) {
                $value = $this->toPhpClassArray($value);
            }
        }

        return array_merge([
            'php_class' => get_class($object),
        ], $data);
    }

    /**
     * @param mixed $object
     * @return array
     */
    private function toArray($object): array
    {
        if ($object instanceof DateTimeInterface) {
            return ['time' => $object->format('Y-m-d\TH:i:s.uO')];
        }

        if ($object instanceof UuidInterface) {
            return ['uuid' => (string) $object];
        }

        return $this->extractValuesFromObject($object);
    }

    /**
     * @param mixed $object
     * @return array
     */
    private function extractValuesFromObject($object): array
    {
        $data = [];
        foreach ($this->getReflectionProperties(get_class($object)) as $property) {
            $data[$property->getName()] = $property->getValue($object);
        }
        return $data;
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function fromArray(array $data)
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->fromArray($value);
            }
        }

        if (array_key_exists('php_class', $data)) {
            return $this->toObject($data['php_class'], $data);
        }

        return $data;
    }

    /**
     * @param string $className
     * @param array $data
     * @return mixed
     */
    private function toObject(string $className, array $data)
    {
        switch ($className) {
            case DateTime::class:
            case DateTimeImmutable::class:
                $timezone = isset($data['timezone']) ? new DateTimeZone($data['timezone']) : null;
                return new $className($data['time'], $timezone);

            case Uuid::class:
                return Uuid::fromString($data['uuid']);
        }

        $reflectionClass = $this->getReflectionClass($className);
        $object = $reflectionClass->newInstanceWithoutConstructor();

        $this->hydrateObjectFromValues($object, $data, $className);
        return $object;
    }

    /**
     * @param mixed $object
     * @param array $data
     * @param string $className
     */
    private function hydrateObjectFromValues($object, array $data, string $className)
    {
        foreach ($this->getReflectionProperties($className) as $property) {
            $name = $property->getName();
            if (!isset($data[$name])) {
                continue;
            }

            $property->setAccessible(true);
            $property->setValue($object, $data[$name]);
        }
    }

    private function getReflectionClass(string $className): ReflectionClass
    {
        if (!array_key_exists($className, $this->classes)) {
            $this->classes[$className] = new ReflectionClass($className);
        }

        return $this->classes[$className];
    }

    /**
     * @param string $className
     * @return ReflectionProperty[]
     */
    private function getReflectionProperties(string $className): array
    {
        if (!array_key_exists($className, $this->reflectionProperties)) {
            $reflectionClass = $this->getReflectionClass($className);
            $this->reflectionProperties[$className] = $reflectionClass->getProperties();
            foreach ($this->reflectionProperties[$className] as $reflectionProperty) {
                $reflectionProperty->setAccessible(true);
            }
        }

        return $this->reflectionProperties[$className];
    }
}
