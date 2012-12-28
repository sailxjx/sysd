<?php
require '../common.php';
$orm = Mod_SysOrm::getIns();
$orm->id = 11;
$orm->email = 'test@51fanli.com';
$orm->template = 'birthday';
$orm->servicetype = 'emay';
var_dump($orm->set(array(
    'servicetype' => 'montnet'
    ))->save());
// var_dump($orm->filter('id', 10)->update());
// var_dump($orm->filter('id', 10)->del());
// print_r($orm->filter('template', 'happybirthday')->findAll());
// print_r($orm->find());
exit;
print_r($orm);
