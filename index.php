<?php
include_once 'App.php';
$user = new App();
$user->setUser('VA','',12);
$data = $user->getUser();
echo '<br>First Name: '.$data['first_name'];
echo '<br>Last Name: '.$data['last_name'];
echo '<br>Age: '.$data['age'];
?>