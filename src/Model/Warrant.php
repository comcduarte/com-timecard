<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class Warrant extends AbstractBaseModel
{
    public $WARRANT_NUM;
    public $WORK_WEEK;
    
    public function __construct($adapter = null)
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards_warrants');
    }
}