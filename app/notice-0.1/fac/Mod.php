<?php
class Fac_Mod extends Fac_SysMod{
    /**
     *
     * @return Mod_RTask
     */
    public function loadModTask() {
        if (!isset($this->oModTask)) {
            $this->oModTask = Mod_RTask::getIns();
        }
        return $this->oModTask;
    }
}