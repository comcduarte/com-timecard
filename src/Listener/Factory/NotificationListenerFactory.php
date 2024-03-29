<?php
namespace Timecard\Listener\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Timecard\Listener\NotificationListener;

class NotificationListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener = new NotificationListener();
        $listener->logger= $container->get('syslogger');
        
        $adapter = $container->get('timecard-model-adapter');
        $listener->setDbAdapter($adapter);
        
        return $listener;
    }
}