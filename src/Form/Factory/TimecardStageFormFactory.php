<?php
namespace Timecard\Form\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Form\TimecardStageForm;

class TimecardStageFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new TimecardStageForm();
        $adapter = $container->get('timecard-model-adapter');
        $form->setDbAdapter($adapter);
        return $form;
    }
}