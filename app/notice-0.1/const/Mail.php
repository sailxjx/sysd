<?php
/**
 * Document: Mail
 * Created on: 2012-9-3, 12:11:01
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
abstract class Const_Mail {
    /**
     * store fields
     */
    const F_ID = 'id'; //id
    const F_SENDER = 'sender'; //发件人
    const F_RECEIVER = 'receiver'; //收件人
    const F_EMAIL = 'email'; //邮件地址
    const F_TITLE = 'title'; //邮件标题
    const F_CONTENT = 'content'; //邮件内容
    const F_CTIME = 'ctime'; //邮件创建时间
    const F_STIME = 'stime'; //邮件发送时间
    const F_STATUS = 'status'; //邮件发送状态
    const F_ERRORNUM = 'error'; //邮件发送错误次数
    const F_EXTRA = 'extra'; //其他
    const F_SERVICETYPE = 'servicetype'; //服务商标识
    const F_TRYSERVICE = 'tryservice'; //已尝试过的服务商
    const F_MAILPARAMS = 'mailparams'; //邮件替换参数
    const F_MAILTEMPLATE = 'template';

    const S_WAIT = 0;
    const S_SEND = 1;
    const S_ERROR = 2;
    const S_FAIL = 3;
    const S_SUCC = 4;

    const P_CAMPAIGNID = 'campaignid'; // webpower only
    const P_GROUPID = 'groupid'; // webpower only
    const P_MAILINGID = 'mailingid'; // webpower only
    const P_PARAMS = 'params';
    
    const C_SERVICE_NAME = 'name';
    const C_SERVICE_TEMP = 'temp';
    const C_SERVICE_SCORE = 'score';
    const C_SERVICE_ERRTIMES = 'errtimes';
    const TEMP_LOCAL = 'local';
    const TEMP_REMOTE = 'remote';

    const EXTRA_HEARTBEAT = 'heartbeat';
    
    public static function getServiceFields() {
        return array(
            self::C_SERVICE_NAME,
            self::C_SERVICE_SCORE,
            self::C_SERVICE_TEMP,
            self::C_SERVICE_ERRTIMES
        );
    }
    
}
