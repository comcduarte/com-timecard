<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardModel extends AbstractBaseModel
{
    public $WORK_WEEK;
    public $EMP_UUID;
    public $PAY_UUID;
    public $SUN;
    public $MON;
    public $TUES;
    public $WED;
    public $THURS;
    public $FRI;
    public $SAT;
    public $DAYS;
    
    public function __construct($adapter = NULL) 
    {
        parent::__construct($adapter);
        $this->setTableName('timecards');
    }
}