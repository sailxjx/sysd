<?php
class Store_SysProc extends Store_SysTable {
    
    public static $aFields = array(
        Const_SysProc::F_NAME => Const_SysProc::F_NAME,
        Const_SysProc::F_START => Const_SysProc::F_START,
        Const_SysProc::F_PARAMS => Const_SysProc::F_PARAMS,
        Const_SysProc::F_OPTIONS => Const_SysProc::F_OPTIONS,
        Const_SysProc::F_PID => Const_SysProc::F_PID,
        Const_SysProc::F_PPID => Const_SysProc::F_PPID,
        Const_SysProc::F_ID => Const_SysProc::F_ID
    );
    
}
