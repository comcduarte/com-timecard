<?php 
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class ShiftCodeModel extends AbstractBaseModel
{
    public $CODE;
    public $DESC;
    public $HOUR;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_shift_codes');
    }
}