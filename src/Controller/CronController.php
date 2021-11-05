<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Employee\Model\EmployeeModel;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Mail\Protocol\Smtp as SmtpProtocol;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mime\Mime;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Timecard\Model\TimecardModel;
use Timecard\Model\Entity\TimecardEntity;
use Timecard\Traits\DateAwareTrait;

class CronController extends AbstractActionController
{
    use AdapterAwareTrait;
    use DateAwareTrait;
    
    public $userEntity;
    public $timecardEntity;
    public $timecard_adapter;
    public $employee_adapter;
    
    public function defaultAction()
    {
        return;
    }
    
    public function reminderAction()
    {
        /**
         * SELECT time_cards.UUID, time_cards.STATUS, time_cards.WORK_WEEK, employees.FNAME, employees.LNAME, employees.EMAIL
FROM time_cards INNER JOIN employees ON time_cards.EMP_UUID = employees.UUID
WHERE (((time_cards.STATUS)<12) AND ((time_cards.WORK_WEEK)=#11/7/2021#));
         */
        $work_week = $this->getEndofWeek('last week');
        
        $timecard = new TimecardModel($this->timecard_adapter);
        
        $where = new Where();
        $where->lessThan('time_cards.STATUS', TimecardModel::APPROVED_STATUS);
        $where->equalTo('time_cards.WORK_WEEK', $work_week);
        
        $select = new Select();
        $select->columns([
            'UUID',
            'STATUS',
            'WORK_WEEK',
            'EMP_UUID',
        ]);
//         $select->join('employees', 'time_cards.EMP_UUID = employees.UUID', ['FNAME','LNAME','EMAIL'], Join::JOIN_INNER);
        $timecard->setSelect($select);
        $timecards = $timecard->fetchAll($where);
        
        $employees = [];
        $employee = new EmployeeModel($this->employee_adapter);
        foreach ($timecards as $tc) {
            if ($employee->read(['UUID' => $tc['EMP_UUID']])) {
                $employees[] = $employee->EMAIL;
            }
        }
        unset($employee);
        
        if (empty($employees)) {
            return;
        }
        
        /****************************************
         * Notifications
         ****************************************/
        $view = new PhpRenderer();
        
        $resolver = new AggregateResolver();
        $view->setResolver($resolver);
        
        $map = new \Laminas\View\Resolver\TemplateMapResolver([
            'layout' => __DIR__ . '/../../view/timecard/layout/notification.phtml',
            'notifications/submission-reminder' => __DIR__ . '/../../view/timecard/notifications/submission-reminder.phtml',
        ]);
        $resolver->attach($map);
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate('notifications/submission-reminder');
        $view->viewModel()->setRoot($viewModel);
        
        $message = new \Laminas\Mail\Message();
        $body = new \Laminas\Mime\Message();
        
        $html = $view->render($viewModel);
        $part = new \Laminas\Mime\Part($html);
        $part->type = Mime::TYPE_HTML;
        
        $message->setFrom('chronos-notifications@middletownct.gov');
        $message->setTo($employees);
        $message->setSubject(sprintf('CHRONOS: %s', 'Submission Reminder'));
        
        $body->addPart($part);
        
        $message->setBody($body);
        
        $protocol = new SmtpProtocol('smtprelay.middletownct.gov');
        $protocol->connect();
        $protocol->helo('chronos.middletownct.gov');
        
        $transport = new SmtpTransport();
        $transport->setConnection($protocol);
        $protocol->rset();
        
        try {
            $transport->send($message);
        } catch (\Exception $e) {
            /**
             * @var \Laminas\Log\Logger $logger
             */
//             $logger = $this->logger;
//             $logger->err($e->getMessage());
//             $logger->info("Error sending email:" . $employee->EMAIL);
        }
        
        
        
        $protocol->disconnect();
    }
    
    
    
    public function setUserEntity(UserEntity $userEntity)
    {
        $this->userEntity = $userEntity;
        return $this;
    }
    
    public function getUserEntity()
    {
        return $this->userEntity;
    }
    
    public function setTimecardEntity(TimecardEntity $timecardEntity)
    {
        $this->timecardEntity = $timecardEntity;
        return $this;
    }
    
    public function getTimecardEntity()
    {
        return $this->timecardEntity;
    }
}