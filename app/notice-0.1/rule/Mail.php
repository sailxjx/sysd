<?php
/**
 * Document: Mail
 * Created on: 2012-8-27, 15:47:13
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Rule_Mail extends Rule_Rule {
    protected $aRdRule = array(
        '1' => 5, //5s
        '2' => 5, //5s
        '3' => 5, //5s
        '4' => 3600, //60*60s
        '5' => 14400, //60*60*4s
        '6' => 43200, //60*60*12s,
        'extra' => array(
            '7-10' => 86400, //60*60*24s
            '10+' => false
        )
    );
    protected function getRdNumQueue() {
        return Redis_Key::mailRedelTimes();
    }
    
}
