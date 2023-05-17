<?php
namespace Timecard\Controller;

use Components\Controller\AbstractConfigController;
use Components\Form\UploadFileForm;
use Employee\Model\EmployeeModel;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Decimal;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\View\Model\ViewModel;
use Timecard\Model\PaycodeModel;
use Timecard\Model\TimecardModel;
use Timecard\Traits\DateAwareTrait;
use Exception;

class TimecardConfigController extends AbstractConfigController
{
    use DateAwareTrait;
    
    public $timecard_adapter;
    public $employee_adapter;
    
    public function __construct()
    {
        $this->setRoute('timecard/config');
    }
    
    public function indexAction()
    {
        $view = new ViewModel();
        $view = parent::indexAction();
        
        $importForm = new UploadFileForm('PAYCODES');
        $importForm->init();
        $importForm->addInputFilter();
        
        $view->setVariable('importForm', $importForm);
        
        $view->setTemplate('timecard/config');
        
        return $view;
    }
    
    public function clearDatabase()
    {
        $sql = new Sql($this->timecard_adapter);
        $ddl = [];
        
        $ddl[] = new DropTable('time_pay_codes');
        $ddl[] = new DropTable('time_shift_codes');
        $ddl[] = new DropTable('time_cards');
        $ddl[] = new DropTable('time_cards_lines');
        $ddl[] = new DropTable('time_cards_signatures');
        $ddl[] = new DropTable('time_cards_stages');
        $ddl[] = new DropTable('user_employee');
        
        foreach ($ddl as $obj) {
            $this->timecard_adapter->query($sql->buildSqlString($obj), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        }
        
        $this->clearSettings('TIMECARD');
    }

    public function createDatabase()
    {
        $sql = new Sql($this->timecard_adapter);
        
        /******************************
         * TIMECARD
         ******************************/
        $ddl = new CreateTable('time_cards_lines');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Datetime('WORK_WEEK', TRUE));
        $ddl->addColumn(new Varchar('TIMECARD_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('PAY_UUID', 36, TRUE));
        $ddl->addColumn(new Decimal('SUN', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('MON', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('TUE', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('WED', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('THU', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('FRI', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('SAT', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('DAYS', 8, 2, TRUE)); 
        $ddl->addColumn(new Integer('ORD', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * TIME CARDS
         ******************************/
        $ddl = new CreateTable('time_cards');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Datetime('WORK_WEEK', TRUE));
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * TIME CARD SIGNATURES
         ******************************/
        $ddl = new CreateTable('time_cards_signatures');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Varchar('USER_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('TIMECARD_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('STAGE_UUID', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * TIME CARD STAGES
         ******************************/
        $ddl = new CreateTable('time_cards_stages');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Varchar('NAME', 255, TRUE));
        $ddl->addColumn(new Integer('SEQUENCE', TRUE));
        $ddl->addColumn(new Varchar('PARENT', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * PAY CODES
         ******************************/
        $ddl = new CreateTable('time_pay_codes');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Varchar('ACCRUAL', 10, TRUE));
        $ddl->addColumn(new Varchar('CODE', 10, TRUE));
        $ddl->addColumn(new Varchar('DESC', 100, TRUE));
        $ddl->addColumn(new Varchar('CAT', 10, TRUE));
        $ddl->addColumn(new Varchar('PAY_TYPE', 50, TRUE));
        $ddl->addColumn(new Varchar('UNITS', 10, TRUE));
        $ddl->addColumn(new Decimal('PHOURLYRATE', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('PDAILYRATE', 8, 2, TRUE));
        $ddl->addColumn(new Decimal('FLATAMT', 8, 2, TRUE));
        $ddl->addColumn(new Varchar('PARENT', 36));
        
        $ddl->addColumn(new Varchar('RESOURCE', 25, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * SHIFT CODES
         ******************************/
        $ddl = new CreateTable('time_shift_codes');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Varchar('CODE', 10, TRUE));
        $ddl->addColumn(new Varchar('DESC', 100, TRUE));
        $ddl->addColumn(new Decimal('HOUR', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * USER-EMPLOYEE RELATIONAL TABLE
         ******************************/
        $ddl = new CreateTable('user_employee');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Varchar('USER_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->timecard_adapter->query($sql->buildSqlString($ddl), $this->timecard_adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
    }
    
    public function populateWeeklyTimecards()
    {
        /******************************
         * GET EMPLOYEE UUID LIST
         ******************************/
        $employeeModel = new EmployeeModel($this->employee_adapter);
        $where = new Where();
        $where->equalTo('STATUS', $employeeModel::ACTIVE_STATUS);
        $employees = $employeeModel->fetchAll($where);
        unset($employeeModel);
        
        /******************************
         * GET WORK WEEK
         ******************************/
        $work_week = $this->getEndofWeek();
        
        /******************************
         * GET REGULAR PAYCODE
         ******************************
        $paycode = new PaycodeModel($this->timecard_adapter);
        $paycode->read(['CODE' => '001']);
        $reg_paycode = $paycode->UUID;
        unset($paycode); */
        
        /******************************
         * GET EXISTING TIMECARDS
         ******************************/
        $timecardModel = new TimecardModel($this->timecard_adapter);
        $timecardModel->WORK_WEEK = $work_week;
        
        $where = new Where();
        $where->equalTo('WORK_WEEK', $work_week);
        $timecards = $timecardModel->fetchAll($where);
        $current_timecards = [];
        
        foreach ($timecards as $timecard) {
            $current_timecards[] = $timecard['EMP_UUID'];
        }
        
        /******************************
         * CREATE TIMECARDS
         ******************************/
        foreach ($employees as $employee) {
            if (in_array($employee['UUID'], $current_timecards)) {
                continue;
            }
            $timecardModel->EMP_UUID = $employee['UUID'];
            
            try {
                $timecardModel->createDefaultTimeCard($employee['SHIFT_CODE']);
            } catch (Exception $e) {
                
            }
            
            /**
            $timecardModel->UUID = $timecardModel->generate_uuid();
            $timecardModel->EMP_UUID = $employee['UUID'];
            
            $timecard_line = new TimecardLineModel($this->timecard_adapter);
            $timecard_line->WORK_WEEK = $work_week;
            $timecard_line->TIMECARD_UUID = $timecardModel->UUID;
            $timecard_line->PAY_UUID = $reg_paycode;
            
            switch ($employee['SHIFT_CODE']) {
                case '40':
                    $timecard_line->MON = 8;
                    $timecard_line->TUE = 8;
                    $timecard_line->WED = 8;
                    $timecard_line->THU = 8;
                    $timecard_line->FRI = 8;
                    break;
                case '35':
                    $timecard_line->MON = 7;
                    $timecard_line->TUE = 7;
                    $timecard_line->WED = 7;
                    $timecard_line->THU = 7;
                    $timecard_line->FRI = 7;
                    break;
                case '20':
                    $timecard_line->MON = 4;
                    $timecard_line->TUE = 4;
                    $timecard_line->WED = 4;
                    $timecard_line->THU = 4;
                    $timecard_line->FRI = 4;
                    break;
                case '42':
                    $timecard_line->SUN = 42;
                    break;
                case '40S':
                    $timecard_line->SUN = 40;
                    break;
                case '35S':
                    $timecard_line->SUN = 35;
                    break;
                case '20S':
                    $timecard_line->SUN = 20;
                    break;
                case '19.5':
                    $timecard_line->SUN = 19.5;
                    break;
                default:
                    continue 2;
                    break;
            }
            
            $timecardModel->create();
            $timecard_line->create();
            */
        }
    }
    
    public function cronAction()
    {
        $view = new ViewModel();
        $view->setTemplate('timecard/cron');
        
        return $view;
    }
    
    public function importpaycodesAction()
    {
        /****************************************
         * Column Descriptions
         ****************************************/
        $CODE = 0;
        $DESC = 1;
        $CAT = 2;
        $PAY_TYPE = 7;
        $PHOURLYRATE = 8;
        $PDAILYRATE = 9;
        $FLATAMT = 10;
        $UNITS = 11;
        
        /****************************************
         * Generate Form
         ****************************************/
        $request = $this->getRequest();
        
        $form = new UploadFileForm();
        $form->init();
        $form->addInputFilter();
        
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $form->setData($data);
            
            if ($form->isValid()) {
                $data = $form->getData();
                if (($handle = fopen($data['FILE']['tmp_name'],"r")) !== FALSE) {
                    while (($record = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        /****************************************
                         * Paycodes
                         ****************************************/
                        $pc = new PaycodeModel($this->timecard_adapter);
                        $result = $pc->read(['CODE' => sprintf('%s', $record[$CODE])]);
                        if ($result === FALSE) {
                            $pc->UUID = $pc->generate_uuid();
                            $pc->STATUS = $pc::ACTIVE_STATUS;
                            $pc->create();
                        }
                        
                        $pc->CODE = $record[$CODE];
                        $pc->DESC = $record[$DESC];
                        $pc->CAT = $record[$CAT];
                        $pc->FLATAMT = $record[$FLATAMT];
                        $pc->PHOURLYRATE = $record[$PHOURLYRATE];
                        $pc->PDAILYRATE = $record[$PDAILYRATE];
                        $pc->UNITS = $record[$UNITS];
                        
                        switch ($record[$PAY_TYPE]) {
                            case 'Regular':
                            case 'Overtime':
                            case 'Premium':
                            case 'Unproductive':
                                $pc->PAY_TYPE = $record[$PAY_TYPE];
                                break;
                            case 'Other':
                            case 'Unknown':
                            default:    
                                $pc->PAY_TYPE = 'Other/Unpaid';
                                break;
                        }
                        $pc->update();
                    }
                    fclose($handle);
                    unlink($data['FILE']['tmp_name']);
                }
                $this->flashMessenger()->addSuccessMessage("Successfully imported paycodes.");
            } else {
                $this->flashmessenger()->addErrorMessage("Form is Invalid.");
            }
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}
