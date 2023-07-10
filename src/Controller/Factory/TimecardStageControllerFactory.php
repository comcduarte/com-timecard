<?php
namespace Timecard\Controller\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\TimecardStageController;
use Timecard\Form\TimecardStageForm;
use Timecard\Model\TimecardStageModel;

class TimecardStageControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardStageController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new TimecardStageModel($adapter);
        $form = $container->get('FormElementManager')->get(TimecardStageForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}