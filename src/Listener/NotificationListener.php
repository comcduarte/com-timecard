<?php
namespace Timecard\Listener;

use Employee\Model\EmployeeModel;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mail\Protocol\Smtp as SmtpProtocol;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mime\Mime;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Settings\Model\SettingsModel;
use Timecard\Model\TimecardModel;
use Timecard\Model\Entity\TimecardEntity;

class NotificationListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    use AdapterAwareTrait;
    
    public $logger;
    
    /**
     * 
     * {@inheritDoc}
     * @see \Laminas\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $shared_manager = $events->getSharedManager();
        
        $this->listeners[] = $shared_manager->attach('*', TimecardModel::EVENT_SUBMITTED,  [$this, 'onSign'], -1000);
    }
    
    public function onSign(Event $event)
    {
        /**
         * @var \Laminas\Log\Logger $logger
         */
        $this->logger->info('Notification Listener >> onSign >> Executed');
        
        $params = $event->getParams();
        
        $employee = new EmployeeModel($event->getTarget()->employee_adapter);
        
        /**
         * @var TimecardEntity $timecard_entity
         */
        $timecard_entity = $params['timecard_entity'];
        $employee->read(['UUID' => $timecard_entity->EMP_UUID]);
        $status = $timecard_entity->STATUS;
        
        if (is_null($employee->EMAIL)) {
            $logger = $this->logger;
            $logger->info(sprintf('Error: %s does not have email address assigned.  Unable to send notification.', $employee->EMP_NUM));
            return;
        }
        
        /****************************************
         * Notifications
         ****************************************/
        $view = new PhpRenderer();
        
        $settings = new SettingsModel($this->adapter);
        
        
        $resolver = new AggregateResolver();
        $view->setResolver($resolver);
        
        $map = new \Laminas\View\Resolver\TemplateMapResolver([
            'layout' => __DIR__ . '/../../view/timecard/layout/notification.phtml',
            'notifications/timecard-submittal' => __DIR__ . '/../../view/timecard/notifications/timecard-submittal.phtml',
        ]);
        $resolver->attach($map);
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate('notifications/timecard-submittal')->setVariables($event->getParams());
        $view->viewModel()->setRoot($viewModel);
        
        $message = new \Laminas\Mail\Message();
        $body = new \Laminas\Mime\Message();
        
        $html = $view->render($viewModel);
        $part = new \Laminas\Mime\Part($html);
        $part->type = Mime::TYPE_HTML;
        
        $settings->read(['MODULE' => 'TIMECARD', 'SETTING' => 'FROM']);
        $message->setFrom($settings->VALUE);
        $message->setTo($employee->EMAIL);
        $message->setSubject(sprintf('CHRONOS: Timecard %s', TimecardModel::retrieveStatus($status)));
        
        $body->addPart($part);
        
        $message->setBody($body);
        
        try {
            $settings->read(['MODULE' => 'TIMECARD', 'SETTING' => 'SERVER']);
            $protocol = new SmtpProtocol($settings->VALUE);
            $protocol->connect();
            $settings->read(['MODULE' => 'TIMECARD', 'SETTING' => 'HELO']);
            $protocol->helo($settings->VALUE);
            
            $transport = new SmtpTransport();
            $transport->setConnection($protocol);
            $protocol->rset();
            $transport->send($message);
        } catch (\Exception $e) {
            /**
             * @var \Laminas\Log\Logger $logger
             */
            $logger = $this->logger;
            $logger->err($e->getMessage());
            $logger->info("Error sending email:" . $employee->EMAIL);
        }
        
        
        
        $protocol->disconnect();
    }
}