<?php

declare(strict_types=1);

namespace ProjectHuddle\Vendor\Laminas\ServiceManager\Factory;

use ProjectHuddle\Vendor\Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use ProjectHuddle\Vendor\Laminas\ServiceManager\Exception\ServiceNotFoundException;
use ProjectHuddle\Vendor\Psr\Container\ContainerExceptionInterface;
use ProjectHuddle\Vendor\Psr\Container\ContainerInterface;

/**
 * Interface for a factory
 *
 * A factory is an callable object that is able to create an object. It is
 * given the instance of the service locator, the requested name of the class
 * you want to create, and any additional options that could be used to
 * configure the instance state.
 */
interface FactoryInterface
{
    /**
     * Create an object
     *
     * @param  string             $requestedName
     * @param  null|array<mixed>  $options
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerExceptionInterface If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null);
}
