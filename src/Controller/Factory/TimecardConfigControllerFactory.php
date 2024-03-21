<?php
namespace Timecard\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Timecard\Controller\TimecardConfigController;

class TimecardConfigControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardConfigController();
        $controller->setDbAdapter($container->get('timecard-model-adapter'));
        $controller->timecard_adapter = $container->get('timecard-model-adapter');
        $controller->employee_adapter = $container->get('employee-model-adapter');
        $controller->setLogger($container->get('syslogger'));
        return $controller;
    }
}