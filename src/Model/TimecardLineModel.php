<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardLineModel extends AbstractBaseModel
{
    public $WORK_WEEK;
    public $TIMECARD_UUID;
    public $PAY_UUID;
    public $SUN;
    public $MON;
    public $TUES;
    public $WED;
    public $THURS;
    public $FRI;
    public $SAT;
    public $DAYS;
    
    const SUBMITTED_STATUS = 10;
    const PREPARERD_STATUS = 11;
    const APPROVED_STATUS = 12;
    
    public function __construct($adapter = NULL) 
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards_lines');
    }
}