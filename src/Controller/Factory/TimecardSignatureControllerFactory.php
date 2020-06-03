<?php
namespace Timecard\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\TimecardSignatureController;
use Timecard\Form\TimecardSignatureForm;
use Timecard\Model\TimecardSignatureModel;

class TimecardSignatureControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardSignatureController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new TimecardSignatureModel($adapter);
        $form = $container->get('FormElementManager')->get(TimecardSignatureForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}