<?php
/**
 * Document: Rule
 * Created on: 2012-8-27, 15:47:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Rule_Rule extends Mod_SysBase {
    
    protected $aRdRule;
    protected $sNumRKey;
    protected $oRedis;
    
    protected function __construct() {
        $this->oRedis = Fac_SysDb::getIns()->loadRedis();
        $this->sNumRKey = $this->getRdNumQueue();
    }
    
    abstract protected function getRdNumQueue();
    
    public function redeliver($iNoteId) {
        $iRdNum = $this->oRedis->zincrby($this->sNumRKey, 1, $iNoteId); //redeliver times
        $mRdRule = isset($this->aRdRule[$iRdNum]) ? $this->aRdRule[$iRdNum] : null;
        if (!isset($mRdRule)) {// can not find the rule, search in extra rules
            foreach ($this->aRdRule['extra'] as $sRKey => $mRule) { // - both side of - is included
                if (strpos($sRKey, '-') !== false) {
                    $aRange = explode('-', $sRKey);
                    if ($aRange[0] <= $iRdNum && $aRange[1] >= $iRdNum) {
                        $mRdRule = $mRule;
                        break;
                    }
                } elseif (strpos($sRKey, '+') !== false) {// + not included
                    $aRange = explode('+', $sRKey);
                    if ($aRange[0] < $iRdNum) {
                        $mRdRule = $mRule;
                        break;
                    }
                }
            }
        }
        return isset($mRdRule)?$mRdRule:false;
    }
    
}
