<?php
class Queue_Log extends Queue_Queue {
    protected $aQueues = array(
        'warning' => array() ,
        'error' => array() ,
        'normal' => array() ,
    );
}
