<?php
namespace Timecard\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\DepartmentController;

class DepartmentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new DepartmentController();
        
        $adapter = $container->get('user-model-adapter');
        $controller->user_adapter = $adapter;
        $controller->setDbAdapter($adapter);
        
        $adapter = $container->get('employee-model-adapter');
        $controller->employee_adapter = $adapter;
        
        $adapter = $container->get('timecard-model-adapter');
        $controller->timecard_adapter = $adapter;
        
        return $controller;
    }
}