<?php

/**
 * Document: Other
 * Created on: 2012-5-29, 10:16:39
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Other extends Base {

	protected $sOther;

	protected function main() {
		$this->runOther();
	}

	protected function runOther() {
		$aParams = $this->oCore->getParams();
		$this->sOther = isset($aParams[Const_SysCommon::P_OTHER]) ? $aParams[Const_SysCommon::P_OTHER] : null;
		if (!isset($this->sOther)) {
			return null;
		}
		if (is_file($this->sOther)) {
			require $this->sOther;
			return true;
		}
		else {
			Util::output('Error: file[' . $this->sOther . '] not exsit!');
			return false;
		}
	}

}
