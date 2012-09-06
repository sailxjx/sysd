<?php

/**
 * Document: RTask
 * Created on: 2012-9-6, 16:43:00
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
class Mod_RTask extends Mod_Task {

    protected $oRedis;

    protected $sMsgKey='taskList';

    protected function __construct(){
        $this->oRedis=Fac_Db::getIns()->loadRedis();
    }

    protected function reset(){
        $this->aMsg=array();
        return $this;
    }

    public function recv() {
        while(!$sMsg=$this->oRedis->rpop(Redis_Key::{$this->sMsgKey}())){
            usleep(10000);
        }
        return $sMsg;
    }

    public function send() {
        if(empty($this->aMsg)){
            return false;
        }
        $this->oRedis->multi();
        foreach ($this->aMsg as $sMsg) {
            $this->oRedis->lpush(Redis_Key::{$this->sMsgKey}(), $sMsg);
        }
        $this->reset();
        return $this->oRedis->exec();
    }

    public function conf(){

    }

}
