<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardSignatureModel extends AbstractBaseModel
{
    public $TIMECARD_UUID;
    public $USER_UUID;
    public $STAGE_UUID;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards_signatures');
    }
}