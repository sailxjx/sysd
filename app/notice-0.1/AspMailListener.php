<?php
class AspMailListener extends Base {
    protected $iInterval = 10;
    protected $oRedis;
    protected function main() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        while (1) {
            $this->listen();
        }
    }

    protected function listen() {
        $oPdo = Fac_SysDb::getIns()->loadPdo('SQLSRV');
        $sSql = 'select top 10 id, mailinfo from tb_sendmail_queue;';
        $oStmt = $oPdo->prepare($sSql);
        $oStmt->execute();
        $aMails = $oStmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($aMails)) {
            sleep($this->iInterval);
        }
        foreach ($aMails as $aMail) {
            $sSql = "DELETE FROM tb_sendmail_queue WHERE id={$aMail['id']};";
            $oStmt = $oPdo->prepare($sSql);
            $r = $oStmt->execute();
            if ($r) {
                Util::output('delete mail id: '. $aMail['id'], 'notice');
                $aMailInfo = json_decode($aMail['mailinfo'],true);
                $aMailInfo[Const_Mail::F_MAILPARAMS] = json_encode($aMailInfo[Const_Mail::F_MAILPARAMS]);
                $aMailInfo[Const_Mail::F_EXTRA] = 'asp';
                if($this->oRedis->lpush(Redis_Key::mailServer(),json_encode($aMailInfo))){
                    Util::output('mail send: ', $aMailInfo, 'verbose');
                }
            }
        }
    }
}