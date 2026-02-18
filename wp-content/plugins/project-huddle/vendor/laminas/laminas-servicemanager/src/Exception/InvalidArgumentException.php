<?php

declare(strict_types=1);

namespace ProjectHuddle\Vendor\Laminas\ServiceManager\Exception;

use InvalidArgumentException as SplInvalidArgumentException;
use ProjectHuddle\Vendor\Laminas\ServiceManager\AbstractFactoryInterface;
use ProjectHuddle\Vendor\Laminas\ServiceManager\Initializer\InitializerInterface;

use function gettype;
use function is_object;
use function sprintf;

/**
 * @inheritDoc
 */
class InvalidArgumentException extends SplInvalidArgumentException implements ExceptionInterface
{
    public static function fromInvalidInitializer(mixed $initializer): self
    {
        return new self(sprintf(
            'An invalid initializer was registered. Expected a callable or an'
            . ' instance of "%s"; received "%s"',
            InitializerInterface::class,
            is_object($initializer) ? $initializer::class : gettype($initializer)
        ));
    }

    public static function fromInvalidAbstractFactory(mixed $abstractFactory): self
    {
        return new self(sprintf(
            'An invalid abstract factory was registered. Expected an instance of or a valid'
            . ' class name resolving to an implementation of "%s", but "%s" was received.',
            AbstractFactoryInterface::class,
            is_object($abstractFactory) ? $abstractFactory::class : gettype($abstractFactory)
        ));
    }
}
