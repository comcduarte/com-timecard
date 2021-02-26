<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Sql\Where;

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
    public $ORD;
    
    const SUBMITTED_STATUS = 10;
    const PREPARERD_STATUS = 11;
    const APPROVED_STATUS = 12;
    const COMPLETED_STATUS = 13;
    
    public function __construct($adapter = NULL) 
    {
        parent::__construct($adapter);
        $this->setTableName('time_cards_lines');
    }
    
    public function create()
    {
        $highest_ordinal = 0;
        
        $where = new Where();
        $where->equalTo('TIMECARD_UUID', $this->TIMECARD_UUID);
        $data = $this->fetchAll($where, ['ORD DESC']);
        if ($data) {
            $highest_ordinal = $data[0]['ORD'] + 10;
        }
        $this->ORD = $highest_ordinal;
        
        $retval = parent::create();
        return $retval;
    }
}