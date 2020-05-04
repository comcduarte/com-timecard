<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardModel extends AbstractBaseModel
{
    public $WORK_DATE;
    public $EMP_UUID;
    public $PAY_UUID;
    public $HOURS;
    public $DAYS;
    
    public function __construct($adapter = NULL) 
    {
        parent::__construct($adapter);
        $this->setTableName('time');
    }
}