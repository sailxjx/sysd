<?php
/**
 * Document: SiteMsg
 * Created on: 2012-9-3, 17:27:35
 * @author: jxxu
 * Email: sailxjx@163.com
 * GTalk: sailxjx@gmail.com
 */
class Store_SiteMsg extends Store_Table {
    /**
     * 站内信表结构
     * @var array
     */
    public static $aFields = array(
        Const_Mail::F_ID => Const_Mail::F_ID,
        Const_Mail::F_SENDER => Const_Mail::F_SENDER,
        Const_Mail::F_RECEIVER => Const_Mail::F_RECEIVER,
    );
}
