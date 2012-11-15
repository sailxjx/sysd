<?php
class Client extends Base {
    protected function main() {
        $oZmq = Fac_SysMq::getIns()->loadZMQ(ZMQ::SOCKET_REQ);
        $oZmq->connect('tcp://*:5556');
        for ($i = 0;$i < 10000;$i++) {
            $oZmq->send('request')->recv();
        }
    }
}
