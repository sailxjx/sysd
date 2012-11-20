<?php
abstract class Redis_Key extends Redis_SysKey {

    protected static $sPrefix = 'notice:';
    protected static $aMap = array(
        'logtable' => 'log:table:{$id}',
        'mailtable' => 'mail:table:{$id}',
        'sitemsgtable' => 'sitemsg:table:{$id}',
        'mailchannelset' => 'mail:channel:set'
    );
    
}
