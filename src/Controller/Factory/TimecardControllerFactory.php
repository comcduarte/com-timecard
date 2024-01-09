<?php
namespace Timecard\Controller\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\TimecardController;
use Timecard\Form\TimecardAddForm;
use Timecard\Form\TimecardForm;
use Timecard\Model\TimecardModel;
use Laminas\Box\API\AccessToken;

class TimecardControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new TimecardController();
        $adapter = $container->get('timecard-model-adapter');
        $model = new TimecardModel($adapter);
        $form = $container->get('FormElementManager')->get(TimecardForm::class);
        $controller->setDbAdapter($adapter);
        
        $adapter = $container->get('user-model-adapter');
        $controller->user_adapter = $adapter;
        
        $adapter = $container->get('employee-model-adapter');
        $controller->employee_adapter = $adapter;
        
        $access_token = new AccessToken($container->get('access-token-config'));
        $controller->setAccessToken($access_token);
        
        $controller->setModel($model);
        $controller->setForm($form);
        
        $controller->timecard_add_form = $container->get('FormElementManager')->get(TimecardAddForm::class);
        $controller->setAclService($container->get('acl-service'));
        
        return $controller;
    }
}
