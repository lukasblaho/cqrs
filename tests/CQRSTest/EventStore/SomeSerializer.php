<?php

namespace CQRSTest\EventStore;

use CQRS\Serializer\SerializerInterface;

class SomeSerializer implements SerializerInterface
{
    public function serialize($data): string
    {
        return '{}';
    }

    public function deserialize(string $data, string $type)
    {
        switch ($type) {
            case SomeEvent::class:
                return new SomeEvent();

            case 'array':
                return [];
        }

        return null;
    }
}
