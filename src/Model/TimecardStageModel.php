<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class TimecardStageModel extends AbstractBaseModel
{
    public $NAME;
    public $SEQUENCE;
    public $PARENT;
    
    public function __construct($adapter = NULL) 
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards_stages');
    }
}