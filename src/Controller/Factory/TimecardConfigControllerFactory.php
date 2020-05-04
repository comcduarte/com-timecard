<?php
namespace Timecard\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\TimecardConfigController;

class TimecardConfigControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardConfigController();
        $adapter = $container->get('timecard-model-adapter');
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}