<?php
namespace Timecard\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Exception;
use Laminas\Stdlib\ArrayUtils;

class PaycodeModel extends AbstractBaseModel
{
    public $ACCRUAL;
    public $CODE;
    public $DESC;
    public $RESOURCE;
    public $CAT;
    public $PAY_TYPE;
    public $UNITS;
    public $PHOURLYRATE;
    public $PDAILYRATE;
    public $FLATAMT;
    public $PARENT;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('time_pay_codes');
    }
    
    public function get_accruals()
    {
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->columns(['ACCRUAL', 'CODE']);
        $select->from($this->getTableName());
        
        $where = new Where();
        $where->isNotNull('ACCRUAL');
        $select->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        try {
            $resultSet = $statement->execute();
        } catch (Exception $e) {
            return FALSE;
        }
        
        if ($resultSet->getAffectedRows() == 0) {
            return FALSE;
        } else {
            $x = [];
            $y = [];
            $z = ArrayUtils::iteratorToArray($resultSet);
            foreach ($z as $record) {
                $y[$record['CODE']] = $record['ACCRUAL'];
                $x[] = $record['ACCRUAL'];
            }
            return [array_unique($x),$y];
        }
    }
}