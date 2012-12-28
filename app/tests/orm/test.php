<?php
require '../common.php';
$orm = Mod_SysOrm::getIns();
$orm->id = 10;
$orm->email = 'test@51fanli.com';
$orm->template = 'happybirthday';
var_dump($orm->insert());
// print_r($orm->filter('template', 'happybirthday')->findAll());
// print_r($orm->find());
exit;
print_r($orm);
