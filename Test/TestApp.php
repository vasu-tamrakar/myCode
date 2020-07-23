<?php
require_once 'App.php';
use PHPUnit\Framework\TestCase;
class TestApp extends TestCase
// class TestApp extends App
{
    public function testFailure()
    {
        $user = new App();
        $user->setUser('BLUE','LAL',60);
        $this->assertIsArray($user->getUser());
    }

    public function testEmpty()
    {

        $use2 = new App();
        $use2->setUser('BLUE','LAL', 17); 
        $age = $use2->getage2();
        // $this->assertEmpty($age);
        return $age;
    }
    

}