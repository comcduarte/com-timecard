<?php
namespace Timecard\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Controller\CronController;
use Timecard\Model\Entity\TimecardEntity;
use Application\Model\Entity\UserEntity;

class CronControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new CronController();
        $adapter = $container->get('timecard-model-adapter');
        
        $timecardEntity = new TimecardEntity();
        $timecardEntity->setDbAdapter($adapter);
        $controller->setTimecardEntity($timecardEntity);
        
        $controller->timecard_adapter = $adapter;
        $controller->employee_adapter = $container->get('employee-model-adapter');
        
        
        $adapter = $container->get('user-model-adapter');
        $userEntity = new UserEntity($adapter);
        $userEntity->setDbAdapter($adapter);
        $controller->setUserEntity($userEntity);
        
        return $controller;
    }
}