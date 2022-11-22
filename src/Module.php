<?php
namespace Timecard;

use Laminas\EventManager\LazyListenerAggregate;
use Laminas\Mvc\MvcEvent;
use Timecard\Listener\NotificationListener;

class Module
{
    const TITLE = "Timecard Module";
    const VERSION = "v1.0.6";
    
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function onBootstrap(MvcEvent $e) 
    {
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();
        $config = $serviceManager->get('config');
        
        $notificationListener = $serviceManager->get(NotificationListener::class);
        $notificationListener->attach($eventManager);
        
        /****************************************
         * Lazy Listeners Aggregate
         ****************************************/
        if (array_key_exists('event_manager', $config)
            && is_array($config['event_manager'])
            && array_key_exists('lazy_listeners', $config['event_manager'])
            ) {
                $listeners = $config['event_manager']['lazy_listeners'];
                $container = $serviceManager;
                $aggregate = new LazyListenerAggregate($listeners, $container);
                $aggregate->attach($eventManager);
            }
    }
}