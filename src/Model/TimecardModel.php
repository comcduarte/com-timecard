<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardModel extends AbstractBaseModel
{
    public $WORK_WEEK;
    public $EMP_UUID;
    
    const SUBMITTED_STATUS = 10;
    const PREPARERD_STATUS = 11;
    const APPROVED_STATUS = 12;
    const COMPLETED_STATUS = 13;
    
    const EVENT_SUBMITTED = 'submitted';
    const EVENT_PREPARED = 'prepared';
    const EVENT_APPROVED = 'approved';
    const EVENT_COMPLETED = 'completed';
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards');
    }
    
    public function formatStatus($status)
    {
        $retval = '';
        
        switch ($status) {
            
            case $this::SUBMITTED_STATUS:
                $retval = "<span class='badge text-bg-success'>Submitted</span>";
                break;
            case $this::PREPARERD_STATUS:
                $retval = "<span class='badge text-bg-info'>Prepared</span>";
                break;
            case $this::APPROVED_STATUS:
                $retval = "<span class='badge text-bg-primary'>Approved</span>";
                break;
            case $this::COMPLETED_STATUS:
                $retval = "<span class='badge text-bg-secondary'>Completed</span>";
                break;
            default:
                $retval = "<span class='badge text-bg-warning'>Pending</span>";
                break;
        }
        
        return $retval;
    }
    
    public function createDefaultTimeCard($SHIFT_CODE = NULL)
    {
        if (is_null($this->WORK_WEEK)) {
            return FALSE;
        }
        
        if (is_null($this->EMP_UUID)) {
            return FALSE;
        }
        
        $this->UUID = $this->generate_uuid();
        
        $timecard_line = new TimecardLineModel($this->adapter);
        $timecard_line->WORK_WEEK = $this->WORK_WEEK;
        $timecard_line->TIMECARD_UUID = $this->UUID;
        
        
        $paycode = new PaycodeModel($this->adapter);
        $paycode->read(['CODE' => '001']);
        $timecard_line->PAY_UUID = $paycode->UUID;
        unset($paycode);
        
        switch ($SHIFT_CODE) {
            case '40':
                $timecard_line->MON = 8;
                $timecard_line->TUE = 8;
                $timecard_line->WED = 8;
                $timecard_line->THU = 8;
                $timecard_line->FRI = 8;
                break;
            case '35':
                $timecard_line->MON = 7;
                $timecard_line->TUES = 7;
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
            case '26':
                $timecard_line->SUN = 26;
                break;
            case '20S':
                $timecard_line->SUN = 20;
                break;
            case '20.5':
                $timecard_line->SUN = 20.5;
                break;
            case '19.5':
                $timecard_line->SUN = 19.5;
                break;
            default:
                return FALSE;
        }
        
        $this->create();
        $timecard_line->create();
        return TRUE;
    }

    public static function retrieveStatus($status)
    {
        $statuses = [
            NULL => 'Inactive',
            self::INACTIVE_STATUS => 'Inactive',
            self::ACTIVE_STATUS => 'Active',
            self::SUBMITTED_STATUS => 'Submitted',
            self::PREPARERD_STATUS => 'Prepared',
            self::APPROVED_STATUS => 'Approved',
            self::COMPLETED_STATUS => 'Completed',
        ];
        
        return $statuses[$status];
    }
}