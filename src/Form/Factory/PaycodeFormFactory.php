<?php
namespace Timecard\Form\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Form\PaycodeForm;

class PaycodeFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new PaycodeForm();
        $form->setDbAdapter($container->get('timecard-model-adapter'));
        return $form;
    }
}