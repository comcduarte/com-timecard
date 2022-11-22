<?php
namespace Timecard\Controller\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\ShiftCodeController;
use Timecard\Form\ShiftCodeForm;
use Timecard\Model\ShiftCodeModel;

class ShiftCodeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new ShiftCodeController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new ShiftCodeModel($adapter);
        $form = new ShiftCodeForm();
        $form->init();
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}