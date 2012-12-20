<?php
/**
 * Document: Store_Mail
 * Created on: 2012-8-22, 16:26:44
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */


class Store_Mail extends Store_Table {
    
    protected $aSyncFields = array(
        Const_Mail::F_ID,
        Const_Mail::F_EMAIL,
        Const_Mail::F_MAILTEMPLATE,
        Const_Mail::F_SERVICETYPE,
        Const_Mail::F_STATUS,
        Const_Mail::F_CTIME
    );
    protected $sSyncTable = 'notice_mail_table';
    
}
