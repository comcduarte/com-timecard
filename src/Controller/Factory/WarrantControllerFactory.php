<?php
namespace Timecard\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Timecard\Controller\WarrantController;
use Timecard\Form\WarrantForm;
use Timecard\Model\Warrant;

class WarrantControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new WarrantController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new Warrant($adapter);
        $form = new WarrantForm();
        $form->init();
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}