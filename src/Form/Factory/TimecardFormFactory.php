<?php
namespace Timecard\Form\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Form\TimecardForm;

class TimecardFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new TimecardForm();
        $adapter = $container->get('timecard-model-adapter');
        $form->setDbAdapter($adapter);
        return $form;
    }
}