<?php
namespace Timecard\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\TimecardController;
use Timecard\Form\TimecardForm;
use Timecard\Model\TimecardModel;

class TimecardControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new TimecardModel($adapter);
        $form = $container->get('FormElementManager')->get(TimecardForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}
