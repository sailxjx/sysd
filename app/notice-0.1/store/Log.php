<?php
/**
 * Document: Store_Log
 * Created on: 2012-8-23, 14:57:27
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Store_Log extends Store_Table {
    /**
     * log表结构
     * @var array
     */
    public static $aFields = array(
        Const_Log::F_ID => Const_Log::F_ID,
        Const_Log::F_CTIME => Const_Log::F_CTIME,
        Const_Log::F_LOCATION => Const_Log::F_LOCATION,
        Const_Log::F_REASON => Const_Log::F_REASON,
        Const_Log::F_FROMSRC => Const_log::F_FROMSRC,
        Const_Log::F_ACTOR => Const_Log::F_ACTOR,
        Const_Log::F_OBJECT => Const_Log::F_OBJECT,
        Const_Log::F_ECODE => Const_Log::F_ECODE,
        Const_Log::F_STATUS => Const_Log::F_STATUS,
        Const_Log::F_MSG => Const_Log::F_MSG,
        Const_Log::F_EXTRA => Const_Log::F_EXTRA
    );
}
