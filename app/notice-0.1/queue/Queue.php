<?php
/**
 * Document: Queue
 * Created on: 2012-8-23, 14:08:50
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Queue_Queue extends Queue_SysQueue {
    
    protected $sRKeyClass = 'Redis_Key'; //class to build the redis key
    
}
