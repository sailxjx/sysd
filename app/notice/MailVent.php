<?php

/**
 * Document: MailVent
 * Created on: 2012-9-6, 18:09:54
 * @author: jxxu
 * Email: jingxinxu@anjuke.com
 * GTalk: sailxjx@gmail.com
 */
class MailVent extends Task_Vent {

    protected function main() {
        $this->vent();
    }

    protected function vent() {
        $oTask=Mod_RTask::getIns();
        for ($i = 0; $i < 10; $i++) {
            $oTask->msg($i);
        }
        $oTask->send();
    }

}
