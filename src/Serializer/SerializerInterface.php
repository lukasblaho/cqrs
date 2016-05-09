<?php

namespace CQRS\Serializer;

interface SerializerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data): string;

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type);
}
