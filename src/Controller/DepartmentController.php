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

class DepartmentController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    
    public function indexAction()
    {
        $view = new ViewModel();
        
        $user = $this->currentUser();
        $user_entity = new UserEntity($this->user_adapter);
        $user_entity->employee->setDbAdapter($this->employee_adapter);
        $user_entity->department->setDbAdapter($this->employee_adapter);
        $user_entity->getUser($user->UUID);
        $view->setVariable('dept', $user_entity->department);
        
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
        $view->setVariable('employees', $data);
        
        return $view;
    }
}