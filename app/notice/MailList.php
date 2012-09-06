<?php

/**
 * Document: MailList
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailList extends Base {

    protected function main() {
        Mod_ZTask::getIns()->recv();
        $id = $this->addMail();
        print_r($this->getMail($id));
        $this->queue($id);
        print_r(Fac_Db::getIns()->loadRedis()->zrange('notice:mail:wait', 0, -1));
        echo PHP_EOL;
    }

    protected function addMail() {
        $oHMail = Store_SiteMsg::getIns();
        $oHMail->{Const_Mail::F_SENDER} = 'web';
        $oHMail->{Const_Mail::F_RECEIVER} = 'me';
        return $oHMail->set();
    }

    protected function getMail($id) {
        $oHMail = Store_SiteMsg::getIns();
        return $oHMail->get($id);
    }

    protected function queue($id) {
        Queue_Mail::getIns()->wait($id, 100)->push();
    }

}
