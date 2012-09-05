<?php

/**
 * Document: Fields
 * Created on: 2012-8-23, 14:59:48
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Store_Fields {

    /**
     * log表结构
     * @var array
     */
    public static $aLog = array(
        Const_Log::F_ID => Const_Log::F_ID,
        Const_Log::F_CTIME => Const_Log::F_CTIME,
        Const_Log::F_LOCATION => Const_Log::F_LOCATION,
        Const_Log::F_REASON => Const_Log::F_REASON,
        Const_Log::F_FROMSRC => Const_log::F_FROMSRC,
        Const_Log::F_ACTOR => Const_Log::F_ACTOR,
        Const_Log::F_OBJECT => Const_Log::F_OBJECT,
        Const_Log::F_ECODE => Const_Log::F_ECODE,
        Const_Log::F_STATUS => Const_Log::F_STATUS,
        Const_Log::F_EXTRA => Const_Log::F_EXTRA
    );

    /**
     * 邮件表结构
     * @var array
     */
    public static $aMail = array(
        Const_Mail::F_ID => Const_Mail::F_ID,
        Const_Mail::F_SENDER => Const_Mail::F_SENDER,
        Const_Mail::F_RECEIVER => Const_Mail::F_RECEIVER,
        Const_Mail::F_TITLE => Const_Mail::F_TITLE,
        Const_Mail::F_CONTENT => Const_Mail::F_CONTENT,
        Const_Mail::F_CTIME => Const_Mail::F_CTIME,
        Const_Mail::F_STIME => Const_Mail::F_STIME,
        Const_Mail::F_STATUS => Const_Mail::F_STATUS,
        Const_Mail::F_EXTRA => Const_Mail::F_EXTRA
    );

    /**
     * 站内信表结构
     * @var array
     */
    public static $aSiteMsg = array(
        Const_Mail::F_ID => Const_Mail::F_ID,
        Const_Mail::F_SENDER => Const_Mail::F_SENDER,
        Const_Mail::F_RECEIVER => Const_Mail::F_RECEIVER,
    );

}
