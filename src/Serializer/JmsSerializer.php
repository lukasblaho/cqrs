<?php

namespace CQRS\Serializer;

use JMS\Serializer\Serializer;

class JmsSerializer implements SerializerInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $format = 'json';

    public function __construct(Serializer $serializer, string $format = null)
    {
        $this->serializer = $serializer;

        if (null !== $format) {
            $this->format = $format;
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data): string
    {
        return $this->serializer->serialize($data, $this->format);
    }

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type)
    {
        return $this->serializer->deserialize($data, $type, $this->format);
    }
}
