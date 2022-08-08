<?php
namespace Timecard\Model\Entity;

use Annotation\Traits\AnnotationAwareTrait;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Where;
use Timecard\Model\PaycodeModel;
use Timecard\Model\TimecardLineModel;
use Timecard\Model\TimecardModel;
use Timecard\Model\TimecardSignatureModel;

class TimecardEntity
{
    use AdapterAwareTrait;
    use AnnotationAwareTrait;
    
    public $TIMECARD_UUID;
    public $WORK_WEEK;
    public $EMP_UUID;
    
    public $TIMECARD_LINES = [];
    public $TIMECARD_SIGNATURES = [];
    public $NOTES = [];
    public $HOURS = [];
    
    public $DAYS = ['MON','TUE','WED','THU','FRI','SAT','SUN'];
    public $STATUS;
    
    public function __construct()
    {
        foreach ($this->DAYS as $DAY) {
            $this->HOURS[$DAY] = 0;
        }
        $this->HOURS['TOTAL'] = 0;
    }
    
    public function getTimecard()
    {
        /****************************************
         * GET TIMECARD
         ****************************************/
        $timecard = new TimecardModel($this->adapter);
        $result = $timecard->read(['EMP_UUID' => $this->EMP_UUID, 'WORK_WEEK' => $this->WORK_WEEK]);
        if (! $result) {
            return false;
        } else {
            $this->TIMECARD_UUID = $timecard->UUID;
            $this->STATUS = $timecard->STATUS;
        }
        
        /****************************************
         * GET TIMECARD LINES
         ****************************************/
        $timecardline = new TimecardLineModel($this->adapter);
        $where = new Where();
        $where->equalTo('TIMECARD_UUID', $this->TIMECARD_UUID);
        $data = $timecardline->fetchAll($where);
        
        /****************************************
         * ANNOTATIONS
         ****************************************/
        $notes = $this->getAnnotations($timecard->getTableName(), $timecard->UUID);
        $this->NOTES = $notes['annotations'];
        
        /****************************************
         * IF NOT TIMECARD LINE, CREATE DEFAULT
         ****************************************/
        if (! sizeof($data)) {
            //-- Create default timecard line --//
        }
        
        foreach ($data as $index => $record) {
            $line = new TimecardLineModel($this->adapter);
            $line->read(['UUID' => $record['UUID']]);
            $id = preg_replace('/^[a-z0-9]*/', str_pad($line->ORD, 8, '0', STR_PAD_LEFT), $line->UUID);
            $this->TIMECARD_LINES[$id] = $line;
            
            $this->HOURS[$line->PAY_UUID] = 0;
            
            foreach ($this->DAYS as $DAY) {
                $this->HOURS[$DAY] += floatval($line->$DAY);
                $this->HOURS[$line->PAY_UUID] += floatval($line->$DAY);
                $this->HOURS['TOTAL'] += floatval($line->$DAY);
            }
        }
        
        $timecard_signature = new TimecardSignatureModel($this->adapter);
        $where = new Where();
        $where->equalTo('TIMECARD_UUID', $timecard->UUID);
        $data = $timecard_signature->fetchAll($where, ['DATE_CREATED DESC']);
        
        if (is_array($data)) {
            foreach ($data as $index => $record) {
                $signature = new TimecardSignatureModel($this->adapter);
                $signature->read(['UUID' => $record['UUID']]);
                $this->TIMECARD_SIGNATURES[] = $signature;
            }
        }
        return true;
    }

    public function createTimecard()
    {
        /******************************
         * GET REGULAR PAYCODE
         ******************************/
        $paycode = new PaycodeModel($this->adapter);
        $paycode->read(['CODE' => '001']);
        $reg_paycode = $paycode->UUID;
        unset($paycode);
        
        
        $TimecardModel = new TimecardModel($this->adapter);
        $this->TIMECARD_UUID = $TimecardModel->UUID;
        $TimecardModel->EMP_UUID = $this->EMP_UUID;
        $TimecardModel->WORK_WEEK = $this->WORK_WEEK;
        $result = $TimecardModel->create();
        
        $timecard_line = new TimecardLineModel($this->adapter);
        $timecard_line->WORK_WEEK = $this->WORK_WEEK;
        $timecard_line->TIMECARD_UUID = $TimecardModel->UUID;
        $timecard_line->PAY_UUID = $reg_paycode;
        
        
        $timecard_line->create();
        
        return $result;
    }
    
    public function updateTimecard()
    {
        return TRUE;
    }
    
    public function deleteTimecard()
    {
        $params = [];
        
        foreach (array_keys(get_object_vars($this)) as $var) {
            switch ($var) {
                case 'EMP_UUID':
                case 'WORK_WEEK':
                    $params[$var] = $this->$var;
                    break;
                default:
                    break;
            }
        }
        
        /****************************************
         * GET TIMECARD
         ****************************************/
        $timecard = new TimecardModel($this->adapter);
        $result = $timecard->read($params);
        if (! $result) {
            return false;
        } else {
            $this->TIMECARD_UUID = $timecard->UUID;
            $this->STATUS = $timecard->STATUS;
        }
        
        $bResult = $timecard->delete();
        return $bResult;
    }
    
    public function addPayCode(TimecardLineModel $line)
    {
        
    }

}