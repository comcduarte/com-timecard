<?php
namespace Timecard\Controller;

use Components\Controller\AbstractConfigController;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;

class TimecardConfigController extends AbstractConfigController
{
    public function __construct()
    {
        $this->setRoute('timecard/config');
    }
    
    public function clearDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
        $ddl[] = new DropTable('time');
        $ddl[] = new DropTable('time_pay_codes');
        
        foreach ($ddl as $obj) {
            $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
        }
        
        $this->clearSettings('TIMECARD');
    }

    public function createDatabase()
    {
        $sql = new Sql($this->adapter);
        
        /******************************
         * TIME
         ******************************/
        $ddl = new CreateTable('time');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Datetime('WORK_DATE', TRUE));
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('PAY_UUID', 36, TRUE));
        $ddl->addColumn(new Integer('HOURS', TRUE));
        $ddl->addColumn(new Integer('DAYS', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * PAY CODES
         ******************************/
        $ddl = new CreateTable('time_pay_codes');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Varchar('CODE', 100, TRUE));
        $ddl->addColumn(new Varchar('DESC', 255, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
    }
}
