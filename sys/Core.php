<?php
/**
 * Document: Core
 * Created on: 2012-4-6, 14:48:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
final class Core {
    
    protected $aOptionMaps = array(
        Const_SysCommon::OS_HELP => 'showHelp',
        Const_SysCommon::OL_HELP => 'showHelp',
        Const_SysCommon::OS_VERSION => 'showVersion',
        Const_SysCommon::OL_VERSION => 'showVersion',
        Const_SysCommon::OS_LOG => 'showLog',
        Const_SysCommon::OL_LOG => 'showLog',
        Const_SysCommon::OS_DAEMON => 'daemon',
        Const_SysCommon::OL_DAEMON => 'daemon',
        Const_SysCommon::OS_QUIET => 'setQuiet',
        Const_SysCommon::OL_QUIET => 'setQuiet',
        Const_SysCommon::OS_TODO => 'showTodo',
        Const_SysCommon::OL_TODO => 'showTodo'
    );
    protected $aDCmds = array(
        Const_SysCommon::C_START,
        Const_SysCommon::C_STOP,
        Const_SysCommon::C_RESTART,
        Const_SysCommon::C_KILL
    );
    protected $sCmd;
    protected $aMan; //手册内容
    protected $sJobClass;
    protected $aParams;
    protected $aOptions;
    private static $oIns;
    protected $sLogFile;
    protected $iDNum; //Deamon进程个数
    protected $bQuiet;
    
    /**
     * instance of JobCore
     * @return Core
     */
    public static function &getIns() {
        if (!self::$oIns) {
            self::$oIns = new Core();
        }
        return self::$oIns;
    }
    
    /**
     * get current job class name
     * @return string
     */
    public function getJobClass() {
        return $this->sJobClass;
    }
    
    /**
     * get current params
     * @return array
     */
    public function getParams() {
        return $this->aParams;
    }
    
    /**
     * get current options
     * @return array
     */
    public function getOptions() {
        return $this->aOptions;
    }
    
    /**
     * get current command
     * @return string
     */
    public function getCmd() {
        return $this->sCmd;
    }
    
    /**
     *
     * @param type $sCmd
     * @return type
     */
    public function setCmd($sCmd) {
        $this->sCmd = $sCmd;
        return $this->sCmd;
    }
    
    /**
     * init of JobCore
     * @return Core
     */
    public function init($argv) {
        unset($argv[0]);
        list($this->sJobClass, $this->aParams, $this->aOptions, $this->sCmd) = Util_SysUtil::hashArgv($argv, $this->aDCmds);
        return self::$oIns;
    }
    
    /**
     * run job
     * @return Core
     */
    public function run() {
        Hook::getIns()->pre();
        foreach ($this->aOptionMaps as $sOps => $sFunc) {
            if (in_array($sOps, $this->aOptions) && method_exists(self::$oIns, $sFunc)) {
                call_user_func(array(
                    self::$oIns,
                    $sFunc
                ));
            }
        }
        $this->rCmd();
        Hook::getIns()->post();
        return self::$oIns;
    }
    
    /**
     * 执行不同命令
     * @todo 执行多条命令？
     */
    protected function rCmd() {
        if (empty($this->sCmd) || !reqClass($sCmdClass = ucfirst($this->sCmd))) {
            Util::output('Command not found!');
            $this->showHelp();
            return false;
        }
        $sCmdClass::getIns()->run();
    }
    
    public function getMan() {
        if (!isset($this->aMan)) {
            $this->aMan = json_decode(Util::getFileCon(Util::getConfig('MAN_PAGE')) , true);
        }
        return $this->aMan;
    }
    
    public function showVersion() {
        $aMan = $this->getMan();
        $sVersion = isset($aMan['version']) ? $aMan['version'] : '';
        echo trim($sVersion) , PHP_EOL;
        exit;
    }
    
    public function showLog() {
        $aMan = $this->getMan();
        $aCLog = isset($aMan['changelog']) ? $aMan['changelog'] : array();
        foreach ($aCLog as $sDate => $aLog) {
            echo $sDate, PHP_EOL;
            foreach ($aLog as $sLog) {
                echo Const_SysCommon::R_TAB, $sLog, PHP_EOL;
            }
            echo Const_SysCommon::R_HR, PHP_EOL;
        }
        exit;
    }
    
    public function daemon() {
        if (empty($this->sJobClass)) {
            Util::output('Class is not exsit!');
            $this->showHelp();
        }
        $this->setQuiet();
        Daemonize::getIns()->daemon();
    }
    
    public function setQuiet() {
        if (!isset($this->bQuiet)) {
            fclose(STDOUT);
            fclose(STDERR);
            global $STDOUT, $STDERR;
            Util::setFileCon($this->getLogFile(), '', FILE_APPEND);
            $STDERR = $STDOUT = fopen($this->getLogFile() , 'a');
            $this->bQuiet = true;
        }
        return $this->bQuiet;
    }
    
    public function showHelp() {
        $aMan = $this->getMan();
        $aHelp = isset($aMan['help']) ? $aMan['help'] : '';
        foreach ($aHelp as $k => $v) {
            echo $k . ':';
            if (is_string($v)) {
                echo "  ", $v, PHP_EOL;
            } else {
                echo PHP_EOL;
                foreach ($v as $vv) {
                    echo Const_SysCommon::R_TAB, $vv, PHP_EOL;
                }
            }
        }
        exit;
    }
    
    public function getDaemonNum() {
        if (!isset($this->iDNum)) {
            $iDNum = 1;
            if (isset($this->aParams[Const_SysCommon::P_DAEMON_NUM])) {
                $iDNum = intval($this->aParams[Const_SysCommon::P_DAEMON_NUM]);
                if ($iDNum <= 0 || $iDNum > Util::getConfig('MAX_DAEMON_NUM')) {
                    $iDNum = 1;
                }
            }
            $this->iDNum = $iDNum;
        }
        return $this->iDNum;
    }
    
    public function getLogFile() {
        if (!isset($this->sLogFile)) {
            if (!isset($this->aParams[Const_SysCommon::P_LOG_FILE])) {
                $this->sLogFile = Util::getConfig('LOG_FILE');
            } else {
                $this->sLogFile = $this->aParams[Const_SysCommon::P_LOG_FILE];
            }
        }
        return $this->sLogFile;
    }
    
    public function showTodo() {
        $aMan = $this->getMan();
        $aTodo = isset($aMan['todo']) ? $aMan['todo'] : array();
        ksort($aTodo);
        foreach ($aTodo as $iLv => $aDoList) {
            echo "优先级:", $iLv, PHP_EOL;
            foreach ($aDoList as $sDo) {
                echo Const_SysCommon::R_TAB, $sDo, PHP_EOL;
            }
            echo Const_SysCommon::R_HR, PHP_EOL;
        }
        exit;
    }
    
}
