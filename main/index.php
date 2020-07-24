<?php
include_once 'App.php';
$user1 = new App();
$user1->setUser('VA','',12);
$data = $user1->getUser();
echo '<br>First Name: '.$data['first_name'];
echo '<br>Last Name: '.$data['last_name'];
echo '<br>Age: '.$data['age'];
echo '<hr>';
/* second object */
$user2 = new App();
$user2->setUser('TestFirst','TestLast',30);
echo 'First Name2 :'.$user2->getfirst_name();
echo '<br>';
echo 'Last Name2 :'.$user2->getlast_name();
echo '<br>';
echo 'Age2 :'.$user2->getage();
echo '<br>';
/* third object */
echo '<hr>';
$user3 = new App();
$user3->setUser('TestFirst3','TestLast3',30);
// echo 'First Name2 :'.$user3->getfirst_name();
// echo '<br>';
// echo 'Last Name2 :'.$user3->getlast_name();
// echo '<br>';
echo 'Age2 :'.$user3->getage2();
echo '<br>';
?>