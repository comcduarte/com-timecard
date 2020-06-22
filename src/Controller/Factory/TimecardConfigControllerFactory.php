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
        $controller->timecard_adapter = $container->get('timecard-model-adapter');
        $controller->employee_adapter = $container->get('employee-model-adapter');
        return $controller;
    }
}