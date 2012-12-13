<?php
class Queue_Sms extends Queue_Queue {
    protected $aQueues = array(
        'wait' => array() ,
        'succ' => array() ,
        'error' => array() ,
        'fail' => array() ,
        'send' => array()
    );
}
