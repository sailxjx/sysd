<?php

/**
 * Document: Rule
 * Created on: 2012-8-27, 15:47:23
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Rule_Rule extends Mod_SysBase {

	protected $aRdRule = array(
		'1' => 60, //60s
		'2' => 300, //60*5s
		'3' => 1800, //60*30s
		'4' => 3600, //60*60s
		'5' => 14400, //60*60*4s
		'6' => 43200, //60*60*12s,
		'extra' => array(
			'7-10' => 86400, //60*60*24s
			'10+' => 'deliError'
		)
	);
	protected $sNumRKey;
	protected $oRedis;

	protected function __construct() {
		$this->oRedis = Fac_SysDb::getIns()->loadRedis();
		$this->sNumRKey = $this->getDelNumRKey();
	}

	abstract protected function getDelNumRKey();

	public function redeliver($iNoteId) {
		$iRdNum = $this->oRedis->zincrby($this->sNumRKey, 1, $iNoteId);
		$sRdRule = isset($this->aRdRule[$iRdNum]) ? $this->aRdRule[$iRdNum] : null;
		if (!isset($sRdRule)) {
			foreach ($this->aRdRule['extra'] as $sRKey => $mRule) {
				if (strpos($sRKey, '-') != -1) {
					$aRange = explode('-', $sRKey);
					if ($aRange[0] >= $iRdNum && $aRange[1] <= $iRdNum) {
						$sRdRule = $mRule;
						break;
					}
				}
				elseif (strpos($sRKey, '+') != -1) {
					$aRange = explode('+', $sRKey);
					if ($aRange[0] < $iRdNum) {
						$sRdRule = $mRule;
						break;
					}
				}
			}
		}
		if (!isset($sRdRule)) {
			return false;
		}
		if (!is_numeric($sRdRule) && is_string($sRdRule) && method_exists($this, $sRdRule)) {
			call_user_method($sRdRule, $this, $iNoteId);
		}
		//@todo how to deliver
		
	}

	protected function deliError($iNoteNum) {

	}

}
