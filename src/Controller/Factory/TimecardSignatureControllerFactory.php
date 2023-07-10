<?php
namespace Timecard\Controller\Factory;

use Psr\Container\ContainerInterface;
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
        
        $controller->logger = $container->get('syslogger');
        $controller->employee_adapter = $container->get('employee-model-adapter');
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}