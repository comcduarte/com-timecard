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
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards');
    }
}