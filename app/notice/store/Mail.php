<?php
/**
 * Document: Store_Mail
 * Created on: 2012-8-22, 16:26:44
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Store_Mail extends Store_Table {
    /**
     * 邮件表结构
     * @var array
     */
    public static $aFields = array(
        Const_Mail::F_ID => Const_Mail::F_ID,
        Const_Mail::F_SENDER => Const_Mail::F_SENDER,
        Const_Mail::F_RECEIVER => Const_Mail::F_RECEIVER,
        Const_Mail::F_TITLE => Const_Mail::F_TITLE,
        Const_Mail::F_CONTENT => Const_Mail::F_CONTENT,
        Const_Mail::F_CTIME => Const_Mail::F_CTIME,
        Const_Mail::F_STIME => Const_Mail::F_STIME,
        Const_Mail::F_STATUS => Const_Mail::F_STATUS,
        Const_Mail::F_ERRORNUM => Const_Mail::F_ERRORNUM,
        Const_Mail::F_EXTRA => Const_Mail::F_EXTRA
    );
}
