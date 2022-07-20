<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Form\Element\Text;
use Components\Form\Element\DatabaseSelect;
use Timecard\Model\PaycodeModel;
use Laminas\Db\Adapter\AdapterAwareTrait;

class PaycodeForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $paycode_model = new PaycodeModel($this->adapter);
        
        $this->add([
            'name' => 'ACCRUAL',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'ACCRUAL',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Accrual Code',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'CODE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'CODE',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Pay Code',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DESC',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'DESC',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Pay Code Description',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'RESOURCE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'RESOURCE',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Acl Resource',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'CAT',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'RESOURCE',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Category',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PAY_TYPE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'PAY_TYPE',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Pay Type',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'UNITS',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'UNITS',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Units',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PHOURLYRATE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'PHOURLYRATE',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Hourly Rate',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PDAILYRATE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'PDAILYRATE',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Daily Rate',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'FLATAMT',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'FLATAMT',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Flat Amount',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PARENT',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'PAY_TYPE',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Parent Paycode',
                'database_table' => $paycode_model->getTableName(),
                'database_id_column' => $paycode_model->getPrimaryKey(),
                'database_value_columns' => [
                    'CODE',
                    'DESC'
                ],
                'database_adapter' => $this->adapter,
            ],
        ],['priority' => 100]);
    }
}