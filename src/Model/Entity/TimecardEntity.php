<?php
namespace Timecard\Model\Entity;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Where;
use Timecard\Model\PaycodeModel;
use Timecard\Model\TimecardLineModel;
use Timecard\Model\TimecardModel;
use Timecard\Model\TimecardSignatureModel;

class TimecardEntity
{
    use AdapterAwareTrait;
    
    public $TIMECARD_UUID;
    public $WORK_WEEK;
    public $EMP_UUID;
    
    public $TIMECARD_LINES = [];
    public $TIMECARD_SIGNATURES = [];
    public $NOTES = [];
    
    public function __construct()
    {
        
    }
    
    public function getTimecard()
    {
        /****************************************
         * GET TIMECARD
         ****************************************/
        $timecard = new TimecardModel($this->adapter);
        $result = $timecard->read(['EMP_UUID' => $this->EMP_UUID, 'WORK_WEEK' => $this->WORK_WEEK]);
        if (! $result) {
            $this->createTimecard();
        } else {
            $this->TIMECARD_UUID = $timecard->UUID;
        }
        
        /****************************************
         * GET TIMECARD LINES
         ****************************************/
        $timecardline = new TimecardLineModel($this->adapter);
        $where = new Where();
        $where->equalTo('TIMECARD_UUID', $this->TIMECARD_UUID);
        $data = $timecardline->fetchAll($where);
        
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
        }
        
        /****************************************
         * GET TIMECARD SIGNATURES
         ****************************************/
        $timecard_signature = new TimecardSignatureModel($this->adapter);
        $where = new Where();
        $where->equalTo('TIMECARD_UUID', $timecard->UUID);
        $data = $timecard_signature->fetchAll($where);
        
        if (is_array($data)) {
            foreach ($data as $index => $record) {
                $signature = new TimecardSignatureModel($this->adapter);
                $signature->read(['UUID' => $record['UUID']]);
                $this->TIMECARD_SIGNATURES[] = $signature;
            }
        }
        
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
    
    public function addPayCode(TimecardLineModel $line)
    {
        
    }
}