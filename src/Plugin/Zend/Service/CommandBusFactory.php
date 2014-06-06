<?php

namespace CQRS\Plugin\Zend\Service;

use CQRS\Plugin\Zend\Options\CommandBus as Options;
use Zend\ServiceManager\ServiceLocatorInterface;

class CommandBusFactory extends AbstractFactory
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \CQRS\CommandHandling\CommandBus
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Options $options */
        $options = $this->getOptions($serviceLocator, 'commandBus');
        return $this->create($serviceLocator, $options);
    }

    /**
     * @return string
     */
    public function getOptionsClass()
    {
        return 'CQRS\Plugin\Zend\Options\CommandBus';
    }

    /**
     * @param ServiceLocatorInterface $sl
     * @param Options $options
     * @return \CQRS\CommandHandling\CommandBus
     */
    protected function create(ServiceLocatorInterface $sl, Options $options)
    {
        $class = $options->getClass();

        /** @var \CQRS\CommandHandling\CommandHandlerLocator $commandHandlerLocator */
        $commandHandlerLocator = $sl->get($options->getCommandHandlerLocator());

        /** @var \CQRS\CommandHandling\TransactionManager $transactionManager */
        $transactionManager = $sl->get($options->getTransactionManager());

        $commandBus = new $class($commandHandlerLocator, $transactionManager);

        return $commandBus;
    }
}
