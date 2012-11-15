<?php
class MakeTest extends Base {
    
    protected $aExts = array(
        'curl',
        'hash',
        'json',
        'mbstring',
        'pcntl',
        'PDO',
        'posix',
        'redis',
        'zmq'
    );
    
    public function main() {
        if ($this->test()) {
            Util::output('Test Success!');
        } else {
            Util::output('Test Failed!');
        }
        return true;
    }
    
    protected function test() {
        $aMissExts = array_diff($this->aExts, get_loaded_extensions());
        if (!empty($aMissExts)) {
            Util::output('This extensions are not loaded!');
            Util::output(array_values($aMissExts));
            return false;
        }
        return true;
    }
}
