<?php
namespace Timecard\Traits;

trait DateAwareTrait
{
    public $date;
    public $today;
    
    public function getStartofWeek(String $date)
    {
        $day = date('N', strtotime($date));
        $day = 7 - $day;
        return date('Y-m-d', strtotime("$date +$day days"));
    }
    
    public function today()
    {
        $this->date = new \DateTime('now',new \DateTimeZone('EDT'));
        $this->today = $this->date->format('Y-m-d');
        return $this;
    }
    
    public function asObject()
    {
        return $this->date;
    }
    
    public function asString(String $date_format = 'Y-m-d')
    {
        return $this->date->format($date_format);
    }
}