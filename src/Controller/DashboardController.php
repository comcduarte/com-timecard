<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Components\Controller\AbstractBaseController;
use Components\Form\Element\DatabaseSelect;
use Components\Form\Element\HiddenSubmit;
use Components\Traits\AclAwareTrait;
use Employee\Model\DepartmentModel;
use Employee\Model\EmployeeModel;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate\Like;
use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\View\Model\ViewModel;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\TimecardModel;
use Timecard\Traits\DateAwareTrait;
use Laminas\Form\Element\Button;

class DashboardController extends AbstractBaseController
{
    use AdapterAwareTrait;
    use DateAwareTrait;
    use AclAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    public $timecard_adapter;
    
    private function getWorkWeek() 
    {
        /****************************************
         * GET WORK WEEK
         ****************************************/
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $work_week = $this->getEndofWeek($data['WORK_WEEK']);
        } elseif (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
            return $this->redirect()->toRoute('dashboard/default', ['week' => $work_week], [], TRUE);
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        
        return $work_week;
    }
    
    public function payrollAction()
    {
        global $work_week;
        
        $view = new ViewModel();
        $user = $this->currentUser();
        $data = [];
        $timecard = new TimecardModel($this->timecard_adapter);
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
            return $this->redirect()->toRoute('dashboard/default', ['action' => 'payroll', 'week' => $work_week], [], TRUE);
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        $view->setVariable('work_week', $work_week);
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        $form = new TimesheetFilterForm();
        $form->init();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            return $this->redirect()->toRoute('dashboard/default', ['action' => 'payroll', 'week' => $this->getEndofWeek($data['WORK_WEEK'])]);
        }
        
        $form->get('WORK_WEEK')->setValue($work_week);
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        /****************************************
         * RETRIEVE DEPARTMENTS
         ****************************************/
        $department_model = new DepartmentModel($this->employee_adapter);
        $where = new Where();
//         $where->equalTo('CODE', '03500');
        $where->equalTo('STATUS', $department_model::ACTIVE_STATUS);
        $data = $department_model->fetchAll($where, ['CODE']);
        
        /****************************************
         * RETRIEVE TIMECARDS PER DEPARTMENT
         ****************************************/
        $sql = new Sql($this->employee_adapter);
        foreach ($data as $index => $department) {
            unset ($data[$index]['DATE_CREATED']);
            unset ($data[$index]['DATE_MODIFIED']);
            
            $where = new Where();
            $where->equalTo('DEPT', $department['UUID'])->AND->equalTo('employees.STATUS', EmployeeModel::ACTIVE_STATUS);
            
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
            $employees = $resultSet->toArray();
            
            $data[$index]['STATUS'] = 100;
            foreach ($employees as $employee) {
                if ($timecard->read(['EMP_UUID' => $employee['UUID'], 'WORK_WEEK' => $work_week])) {
                    if ($timecard->STATUS < $data[$index]['STATUS']) {
                        $data[$index]['STATUS'] = $timecard->STATUS;
                    }
                }
            }
            $data[$index]['STATUS'] = $timecard->formatStatus($data[$index]['STATUS']);
        }
        
        unset($department_model);
        
        $view->setVariable('data', $data);
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        
        
        /****************************************
         * REPORTS SUBTABLE
         ****************************************/
        $reports = [];
        
        $sql = new Sql($this->adapter);
        $select = new Select();
        $select->columns(['UUID', 'NAME'])
        ->from('reports')
        ->where([new Like('NAME', 'PY - %')]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $reports = $resultSet->toArray();
        
        $view->setVariable('reports', $reports);
        
        return $view;
    }
    
    public function deptAction()
    {
        $view = new ViewModel();
        
        $redirect = FALSE;
        
        /****************************************
         * GET CURRENT USER
         * @var UserEntity $user_entity
         ****************************************/
        $user = $this->currentUser();
        $user_entity = new UserEntity($this->user_adapter);
        $user_entity->employee->setDbAdapter($this->employee_adapter);
        $user_entity->department->setDbAdapter($this->employee_adapter);
        $user_entity->getUser($user->UUID);
        
        /****************************************
         * GET WORK WEEK
         * @var String @work_week
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
            $redirect = TRUE;
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        $view->setVariable('work_week', $work_week);
        
        /****************************************
         * GET DEPARTMENT UUID
         * @var String $dept
         ****************************************/
        $dept = '';
        if (! $this->params()->fromRoute('uuid', 0)) {
            $dept = $user_entity->employee->DEPT;
            $redirect = TRUE;
        } else {
            $dept = $this->params()->fromRoute('uuid', 0);
        }
        
        if ($redirect) {
            return $this->redirect()->toRoute('dashboard/dept', ['uuid' => $dept, 'week' => $work_week]);
        }
        
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        $form = new TimesheetFilterForm();
        $form->init();
        
        /**
         * If form was submitted, load the new work week.
         */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $work_week = $this->getEndofWeek($data['WORK_WEEK']);
            return $this->redirect()->toRoute('dashboard/dept', ['week' => $work_week, 'uuid' => $dept]);
        }
                
        $form->get('WORK_WEEK')->setValue($work_week);
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        /****************************************
         * FIND EMPLOYEE FORM
         ****************************************/
        $employee = new EmployeeModel($this->employee_adapter);
        
        $find_employee_form = new Form();
        $find_employee_form->add(new HiddenSubmit('SUBMIT'));
        $find_employee_form->add(new Csrf('SECURITY'));
        $find_employee_form->add([
            'name' => 'BUTTON',
            'type' => Button::class,
            'attributes' => [
                'id' => 'BUTTON',
                'class' => 'btn btn-outline-primary',
                'onclick' => 'form.submit()',
            ],
            'options' => [
                'label' => 'Add',
            ],
        ]);
        
        $select = new Select();
        $select->from($employee->getTableName());
        $select->columns(['UUID','EMP_NUM','LNAME','FNAME']);
        $select->order(['LNAME']);
        
        $where = new Where();
        $where->equalTo('DEPT', $dept)->and->equalTo('STATUS', EmployeeModel::ACTIVE_STATUS);
        
        $select->where($where);
        
        
        $find_employee_form->add([
            'name' => 'UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'id' => 'UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Open Timesheet',
                'database_table' => $employee->getTableName(),
                'database_id_column' => $employee->getPrimaryKey(),
                'database_value_columns' => [
                    'LNAME',
                    'FNAME',
                    'EMP_NUM',
                ],
                'database_adapter' => $this->employee_adapter,
                'database_object' => $select,
//                 'acl_service' => $this->getAclService(),
//                 'acl_resource_column' => 'TIME_GROUP',
            ],
        ]);
        $find_employee_form->get('UUID')->roles = $user_entity->user->memberOf();
        $find_employee_form->get('UUID')->populateElement();
        $view->setVariable('find_employee_form', $find_employee_form);
        
        /****************************************
         * RETRIEVE DEPARTMENT EMPLOYEES
         ****************************************/
        
        $sql = new Sql($this->employee_adapter);
        
        $where = new Where();
        $where->equalTo('DEPT', $dept)->AND->equalTo('employees.STATUS', EmployeeModel::ACTIVE_STATUS);
        
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
        
        /****************************************
         * RETRIEVE EMPLOYEE SUBMISSION STATUS
         ****************************************/
        $timecard = new TimecardModel($this->timecard_adapter);
        
        foreach ($data as $index => $record) {
            $timecards = [];
            $sql = new Sql($this->user_adapter);
            $select = new Select();
            $select->from($timecard->getTableName());
            $where = new Where();
            $where->equalTo('WORK_WEEK', $work_week);
            $where->AND->equalTo('EMP_UUID', $record['UUID']);
            $select->where($where);
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            $resultSet = new ResultSet($results);
            $resultSet->initialize($results);
            $timecards = $resultSet->toArray();
            
            if (sizeof($timecards)) {
                switch ($timecards[0]['STATUS']) {
                    case $timecard::APPROVED_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-primary'>Approved</span>";
                        break;
                    case $timecard::SUBMITTED_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-success'>Submitted</span>";
                        break;
                    case $timecard::PREPARERD_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-info'>Prepared</span>";
                        break;
                    case $timecard::COMPLETED_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-secondary'>Completed</span>";
                        break;
                    default:
                        $data[$index]['STATUS'] = "<span class='badge badge-warning'>Pending</span>";
                        break;
                }
                $data[$index]['Timecard'] = $timecards[0]['UUID'];
            } else {
                /**
                 * If the employee has not logged in, is not full time and time card is populated by cron,
                 * or the preparer has not manually added a time card, disregard even active employees.  In
                 * this manner we can filter out temps.
                 */
                unset ($data[$index]);
            }
        }
        $view->setVariable('employees', $data);
        
        /****************************************
         * GET DEPARTMENT
         ****************************************/
        $department = new DepartmentModel($this->employee_adapter);
        $department->read(['UUID' => $dept]);
        $view->setVariable('dept', $department);
        
        /****************************************
         * REPORTS SUBTABLE
         ****************************************/
        $reports = [];
        
        $sql = new Sql($this->adapter);
        $select = new Select();
        $select->columns(['UUID', 'NAME'])
        ->from('reports')
        ->where([new Like('NAME', 'DEPT - %')])
        ->where([new Like('NAME', "$department->CODE - %")],Where::OP_OR);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $reports = $resultSet->toArray();
        
        $view->setVariable('reports', $reports);
        
        /****************************************
         * ACL
         ****************************************/
        $view->setVariable('acl_service', $this->acl_service);
        
        /****************************************
         * Roles
         ****************************************/
        $roles = [];
        foreach ($user_entity->user->memberOf() as $role) {
            array_push($roles, $role['ROLENAME']);
        }
        $view->setVariable('roles', $roles);
        
        return $view;
    }

    public function departmentAction()
    {
        $view = new ViewModel();
        $redirect = FALSE;
        $data = [];
        $timecard = new TimecardModel($this->timecard_adapter);
        
        
        /****************************************
         * GET CURRENT USER
         * @var UserEntity $user_entity
         ****************************************/
        $user = $this->currentUser();
        $user_entity = new UserEntity($this->user_adapter);
        $user_entity->employee->setDbAdapter($this->employee_adapter);
        $user_entity->department->setDbAdapter($this->employee_adapter);
        $user_entity->getUser($user->UUID);
        
        /****************************************
         * GET WORK WEEK
         * @var String @work_week
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
            $redirect = TRUE;
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        $view->setVariable('work_week', $work_week);
        
        /****************************************
         * GET DEPARTMENT UUID
         * @var String $dept
         * @var Boolean $redirect
         ****************************************/
        $dept = '';
        if ($this->params()->fromRoute('uuid', 0)) {
            $dept = $this->params()->fromRoute('uuid', 0);
        }
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        $form = new TimesheetFilterForm();
        $form->init();
        
        /**
         * If form was submitted, load the new work week.
         */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $work_week = $this->getEndofWeek($data['WORK_WEEK']);
            return $this->redirect()->toRoute('dashboard/department', ['week' => $work_week, 'uuid' => $dept]);
        }
        
        $form->get('WORK_WEEK')->setValue($work_week);
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        /****************************************
         * RETRIEVE DEPARTMENTS
         ****************************************/
        $department_model = new DepartmentModel($this->employee_adapter);
        $where = new Where();
        //         $where->equalTo('CODE', '03500');
        $where->equalTo('STATUS', $department_model::ACTIVE_STATUS);
        $data = $department_model->fetchAll($where, ['CODE']);
        
        /****************************************
         * RETRIEVE TIMECARDS PER DEPARTMENT
         ****************************************/
        $sql = new Sql($this->employee_adapter);
        foreach ($data as $index => $department) {
            if (!$this->isAllowed($user_entity->user->memberOf(), $department['CODE'], 'dashboard/department')) {
                unset ($data[$index]);
                continue;
            }
            
            unset ($data[$index]['DATE_CREATED']);
            unset ($data[$index]['DATE_MODIFIED']);
            
            $where = new Where();
            $where->equalTo('DEPT', $department['UUID'])->AND->equalTo('employees.STATUS', EmployeeModel::ACTIVE_STATUS);
            
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
            $employees = $resultSet->toArray();
            
            $data[$index]['STATUS'] = 100;
            foreach ($employees as $employee) {
                if ($timecard->read(['EMP_UUID' => $employee['UUID'], 'WORK_WEEK' => $work_week])) {
                    if ($timecard->STATUS < $data[$index]['STATUS']) {
                        $data[$index]['STATUS'] = $timecard->STATUS;
                    }
                }
            }
            $data[$index]['STATUS'] = $timecard->formatStatus($data[$index]['STATUS']);
        }
        
        unset($department_model);
        
        if (sizeof($data) == 1) {
            return $this->redirect()->toRoute('dashboard/dept', ['week' => $work_week,]);;
        }
        
        $view->setVariable('data', $data);
        
        /****************************************
         * REPORTS SUBTABLE
         ****************************************/
        $reports = [];
        
        $sql = new Sql($this->adapter);
        $select = new Select();
        $select->columns(['UUID', 'NAME'])
        ->from('reports')
        ->where([new Like('NAME', 'PY - %')]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $reports = $resultSet->toArray();
        
        $view->setVariable('reports', $reports);
        
        return $view;
    }
}