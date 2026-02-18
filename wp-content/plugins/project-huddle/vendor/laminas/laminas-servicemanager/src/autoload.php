<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid


declare(strict_types=1);

use ProjectHuddle\Vendor\Interop\Container\Containerinterface as InteropContainerInterface;
use ProjectHuddle\Vendor\Interop\Container\Exception\ContainerException as InteropContainerException;
use ProjectHuddle\Vendor\Interop\Container\Exception\NotFoundException as InteropNotFoundException;
use ProjectHuddle\Vendor\Psr\Container\ContainerExceptionInterface;
use ProjectHuddle\Vendor\Psr\Container\ContainerInterface;
use ProjectHuddle\Vendor\Psr\Container\NotFoundExceptionInterface;

if (! interface_exists(InteropContainerInterface::class, false)) {
    class_alias(ContainerInterface::class, InteropContainerInterface::class);
}
if (! interface_exists(InteropContainerException::class, false)) {
    class_alias(ContainerExceptionInterface::class, InteropContainerException::class);
}
if (! interface_exists(InteropNotFoundException::class, false)) {
    class_alias(NotFoundExceptionInterface::class, InteropNotFoundException::class);
}
