<?php
class Const_Sms {
    const F_ID = 'id';
    const F_MOBILE = 'mobile';
    const F_TYPE = 'type';
    const F_CONTENT = 'content';
    const F_CTIME = 'ctime';
    const F_STIME = 'stime';
    const F_STATUS = 'status';
    const F_ERRORNUM = 'en';
    const F_TRYSERVICE = 'tryservice';
    const F_SERVICETYPE = 'servicetype';
    const F_SMSPARAMS = 'smsparams';
    const F_SMSTEMPLATE = 'template';

    const S_WAIT = 0;
    const S_SEND = 1;
    const S_ERROR = 2;
    const S_FAIL = 3;
    const S_SUCC = 4;

    const C_SERVICE_SCORE = 'score';
    const C_SERVICE_DESC = 'desc';
    const C_SERVICE_POOL = 'pool';

    const C_POOL_HIGH = 'high';
    const C_POOL_LOW = 'low';

    const P_PARAMS = 'params';
}