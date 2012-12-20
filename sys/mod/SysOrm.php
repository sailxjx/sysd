<?php

abstract class Mod_SysOrm extends Mod_SysBase {
    protected $oPdo;
    abstract protected $sTable;
    protected function __construct() {
        $this->loadDb();
    }
    protected function loadDb() {
        $this->oPdo = Fac_SysDb::getIns()->loadPdo();
    }
    protected function loadTable() {

    }
    public function set() {

    }
}
