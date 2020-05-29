<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Timecard\Traits\DateAwareTrait;
use Timecard\Model\TimecardModel;

class DepartmentController extends AbstractActionController
{
    use AdapterAwareTrait;
    use DateAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    public $timecard_adapter;
    
    public function indexAction()
    {
        $view = new ViewModel();
        
        $user = $this->currentUser();
        $user_entity = new UserEntity($this->user_adapter);
        $user_entity->employee->setDbAdapter($this->employee_adapter);
        $user_entity->department->setDbAdapter($this->employee_adapter);
        $user_entity->getUser($user->UUID);
        $view->setVariable('dept', $user_entity->department);
        $view->setVariable('role', $user_entity->groups[0]['ROLENAME']);
        
        /****************************************
         * RETRIEVE DEPARTMENT EMPLOYEES
         ****************************************/
        $departmet_preparer = $user_entity->employee;
        
//         $data = $departmet_preparer->fetchAll($where);
        $sql = new Sql($this->employee_adapter);
        
        $where = new Where();
        $where->equalTo('DEPT', $departmet_preparer->DEPT)->AND->equalTo('employees.STATUS', $departmet_preparer::ACTIVE_STATUS);
        
        $select = new Select();
        $select->from('employees');
        $select->where($where);
        $select->columns([
            'UUID' => 'UUID',
            'First Name' => 'FNAME',
            'Last Name' => 'LNAME',
            'Email' => 'EMAIL',
        ]);
        $select->order('LNAME ASC');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $data = $resultSet->toArray();
        
//         $header = [];
//         if (!empty($data)) {
//             $header = array_keys($data[0]);
//         }
        
//         $view->setVariable('employees_header', $header);

        /****************************************
         * RETRIEVE EMPLOYEE SUBMISSION STATUS
         ****************************************/
        $work_week = $this->getStartofWeek($this->today()->asString());
        $timecard = new TimecardModel($this->timecard_adapter);
        
        foreach ($data as $index => $record) {
            $timecards = [];
            $sql = new Sql($this->user_adapter);
            $select = new Select();
            $select->from($timecard->getTableName());
            $where = new Where();
            $where->equalTo('STATUS', 1)->AND->equalTo('WORK_WEEK', $work_week);
            $where->AND->equalTo('EMP_UUID', $record['UUID']);
            $select->where($where);
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            $resultSet = new ResultSet($results);
            $resultSet->initialize($results);
            $timecards = $resultSet->toArray();
            
            if (sizeof($timecards)) {
                $data[$index]['STATUS'] = 'Pending';
            } else {
                $data[$index]['STATUS'] = 'Submitted';
            }
        }
        
        
        $view->setVariable('employees', $data);
        
        /****************************************
         * SET MISCELLANEOUS VARIABLES
         ****************************************/
        $view->setVariable('work_week', $work_week);
        return $view;
    }
}