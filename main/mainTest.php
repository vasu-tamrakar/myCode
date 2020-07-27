<?php
require_once 'App.php';

$user = new App();
$user->setUser('Basu','BABA',20);
echo $user->getfirst_name();
echo $user->getlast_name();
$data = $user->getUser();
print_r($data);
