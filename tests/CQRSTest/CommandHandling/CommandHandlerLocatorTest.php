<?php

namespace CQRSTest\CommandHandling;

use CQRS\CommandHandling\Exception\CommandHandlerNotFoundException;
use CQRS\CommandHandling\CommandHandlerLocator;
use CQRS\CommandHandling\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class CommandHandlerLocatorTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterAndGetCommandHandler()
    {
        $handler = function () {};

        $locator = new CommandHandlerLocator();
        $locator->set('Command', $handler);

        $this->assertSame($handler, $locator->get('Command'));
    }

    public function testItThrowsExceptionWhenNoHandlerIsRegisteredForCommand()
    {
        $this->setExpectedException(
            CommandHandlerNotFoundException::class,
            'Command handler for CommandWithoutHandler not found'
        );

        $locator = new CommandHandlerLocator();
        $locator->get('CommandWithoutHandler');
    }
}
