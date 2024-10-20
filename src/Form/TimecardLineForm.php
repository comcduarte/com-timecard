<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\AclDatabaseSelect;
use Components\Form\Element\HiddenSubmit;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Components\Traits\AclAwareTrait;

class TimecardLineForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    use AclAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'WORK_WEEK',
            'type' => Hidden::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'WORK_WEEK',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Work Week',
                'format' => 'Y-m-d H:m:s',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'TIMECARD_UUID',
            'type' => Hidden::class,
            'attributes' => [
                'id' => 'TIMECARD_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Timecard ID',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PAY_UUID',
            'type' => AclDatabaseSelect::class,
            'attributes' => [
                'id' => 'PAY_UUID',
                'class' => 'form-select',
            ],
            'options' => [
                'label' => 'Pay Code',
                'empty_option' => 'Select a Paycode',
                'acl_service' => $this->getAclService(),
                'acl_resource_column' => 'RESOURCE',
                'database_adapter' => $this->adapter,
                'database_table' => 'time_pay_codes',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'CODE',
                    'DESC',
                ],
            ],
        ],['priority' => 100]);
        
        $days = ['SUN','MON','TUE','WED','THU','FRI','SAT', 'DAYS'];
        foreach ($days as $day) {
            $this->add([
                'name' => $day,
                'type' => Text::class,
                'attributes' => [
                    'id' => $day,
                    'class' => 'form-control',
                    'onchange' => 'this.form.submit()',
                ],
                'options' => [
                    'label' => $day,
                ],
            ],['priority' => 100]);
        }
        
        $this->add([
            'name' => 'ORD',
            'type' => Text::class,
            'attributes' => [
                'id' => 'ORD',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'ORDER',
            ],
        ],['priority' => 100]);
        
        $this->remove('SUBMIT');
        $this->add([
            'name' => 'SUBMIT',
            'type' => HiddenSubmit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary form-control mt-4',
                'id' => 'SUBMIT',
            ],
        ],['priority' => 0]);
    }
}