<?php

/**
 * Document: MailList
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailList extends Base {

    protected function main() {
        $id = $this->addMail();
        print_r($this->getMail($id));
        $this->queue($id);
        print_r(Fac_Db::getIns()->loadRedis()->zrange('notice:mail:wait', 0, -1));
        echo PHP_EOL;
    }

    protected function addMail() {
        $oHMail = Hash_Mail::getIns();
        $oHMail->{Const_Mail::F_SENDER} = 'web';
        $oHMail->{Const_Mail::F_RECEIVER} = 'me';
        $oHMail->{Const_Mail::F_CTIME} = time();
        $oHMail->{Const_Mail::F_TITLE} = '测试邮件';
        $oHMail->{Const_Mail::F_CONTENT} = '测试内容';
        return $oHMail->set(array(
                    Const_Mail::F_STATUS => '1',
                    Const_Mail::F_SENDER => 'web'
                ));
    }

    protected function getMail($id) {
        $oHMail = Hash_Mail::getIns();
        return $oHMail->get($id);
    }

    protected function queue($id) {
        Queue_Mail::getIns()->wait($id, 100)->push();
    }

}
