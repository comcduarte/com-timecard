<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;

class PaycodeModel extends AbstractBaseModel
{
    public $CODE;
    public $DESC;
    public $RESOURCE;
    public $CAT;
    public $PAY_TYPE;
    public $PARENT;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_pay_codes');
    }
}