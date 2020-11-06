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
    
    public function formatStatus($status)
    {
        $retval = '';
        
        switch ($status) {
            case $this::APPROVED_STATUS:
                $retval = "<span class='badge badge-primary'>Approved</span>";
                break;
            case $this::SUBMITTED_STATUS:
                $retval = "<span class='badge badge-success'>Submitted</span>";
                break;
            case $this::PREPARERD_STATUS:
                $retval = "<span class='badge badge-info'>Prepared</span>";
                break;
            default:
                $retval = "<span class='badge badge-warning'>Pending</span>";
                break;
        }
        
        return $retval;
    }
}