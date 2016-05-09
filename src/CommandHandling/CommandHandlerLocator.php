<?php

namespace CQRS\CommandHandling;

use Interop\Container\ContainerInterface;

class CommandHandlerLocator implements ContainerInterface
{
    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @var callable|null
     */
    protected $resolver;

    public function __construct(array $handlers = [], callable $resolver = null)
    {
        foreach ($handlers as $commandType => $handler) {
            $this->set($commandType, $handler);
        }

        $this->resolver = $resolver;
    }

    /**
     * @param string $commandType
     * @param callable|mixed $handler
     */
    public function set(string $commandType, $handler)
    {
        $this->handlers[$commandType] = $handler;
    }

    public function remove(string $commandType)
    {
        unset($this->handlers[$commandType]);
    }

    /**
     * @param callable|mixed $handler
     */
    public function removeHandler($handler)
    {
        foreach ($this->handlers as $commandType => $evaluatedHandler) {
            if ($handler === $evaluatedHandler) {
                unset($this->handlers[$commandType]);
            }
        }
    }

    /**
     * @param string $commandType
     * @return callable
     * @throws Exception\CommandHandlerNotFoundException
     */
    public function get($commandType): callable
    {
        if (!$this->has($commandType)) {
            throw new Exception\CommandHandlerNotFoundException(sprintf(
                'Command handler for %s not found',
                $commandType
            ));
        }

        return $this->resolver
            ? call_user_func($this->resolver, $this->handlers[$commandType], $commandType)
            : $this->handlers[$commandType];
    }

    /**
     * @param string $commandType
     * @return bool
     */
    public function has($commandType): bool
    {
        return array_key_exists($commandType, $this->handlers);
    }
}
