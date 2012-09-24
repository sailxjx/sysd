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
     * fields
     */
    
    const F_ID = 'id'; //id
    const F_SENDER = 'sender'; //发件人
    const F_RECEIVER = 'receiver'; //收件人
    const F_MAIL = 'mail'; //邮件地址
    const F_TITLE = 'title'; //邮件标题
    const F_CONTENT = 'content'; //邮件内容
    const F_CTIME = 'ctime'; //邮件创建时间
    const F_STIME = 'stime'; //邮件发送时间
    const F_STATUS = 'status'; //邮件发送状态
    const F_ERRORNUM = 'error'; //邮件发送错误次数
    const F_EXTRA = 'extra'; //其他
    
    const S_WAIT = 0;
    const S_SEND = 1;
    const S_ERROR = 2;
    const S_FAIL = 3;
    const S_SUCC = 4;
    
}
