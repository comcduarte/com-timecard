<?php
namespace Timecard\Controller\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\PaycodeController;
use Timecard\Model\PaycodeModel;
use Timecard\Form\PaycodeForm;

class PaycodeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new PaycodeController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new PaycodeModel($adapter);
        $form = $container->get('FormElementManager')->get(PaycodeForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}