<?php
namespace Timecard\Form\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Form\TimecardAddForm;

class TimecardAddFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new TimecardAddForm();
        $form->setAclService($container->get('acl-service'));
        $form->setDbAdapter($container->get('timecard-model-adapter'));
        return $form;
    }
}