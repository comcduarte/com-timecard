<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardModel extends AbstractBaseModel
{
    public $WORK_WEEK;
    public $EMP_UUID;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards');
    }
}