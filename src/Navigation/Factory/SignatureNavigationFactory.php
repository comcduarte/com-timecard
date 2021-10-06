<?php
namespace Timecard\Navigation\Factory;

use Laminas\Navigation\Service\AbstractNavigationFactory;

class SignatureNavigationFactory extends AbstractNavigationFactory
{
    protected function getName()
    {
        return 'signatures';
    }
}