<?php
namespace Timecard\Form\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Form\PaycodeForm;

class PaycodeFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new PaycodeForm();
        return $form;
    }
}