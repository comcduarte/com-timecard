<?php
namespace Timecard\Form\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Form\TimecardSignatureForm;

class TimecardSignatureFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new TimecardSignatureForm();
        $adapter = $container->get('timecard-model-adapter');
        $form->setDbAdapter($adapter);
        return $form;
    }
}