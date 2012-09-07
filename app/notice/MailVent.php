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
        $oTask=Mod_ZTask::getIns()->channel(0);
        for ($i = 0; $i < 100000; $i++) {
            $oTask->msg($i);
        }
        $oTask->send();
    }

}
