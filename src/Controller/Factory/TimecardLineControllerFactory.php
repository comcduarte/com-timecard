<?php
namespace Timecard\Controller\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\TimecardLineController;
use Timecard\Form\TimecardLineForm;
use Timecard\Model\TimecardLineModel;

class TimecardLineControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardLineController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new TimecardLineModel($adapter);
        $form = $container->get('FormElementManager')->get(TimecardLineForm::class);
        $controller->setDbAdapter($adapter);
        $controller->setModel($model);
        $controller->setForm($form);
        return $controller;
    }
}