<?php
class Rule_Sms extends Rule_Rule {
    protected $aRdRule = array(
        '1' => 5,
        '2' => 5,
        '3' => 5,
        '4' => 5,
        '5' => 5,
        'extra' => array(
            '6+' => false
        )
    );

    protected function getRdNumQueue() {
        return Redis_Key::smsRedelTimes();
    }
}
