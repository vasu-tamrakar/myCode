<?php
require __DIR__ .'/../vendor/autoload.php';
use PHPUnit\Framework\TestCase;
// use App;

    
class TestApp extends TestCase
// class TestApp extends App
{
    /**
     * @covers \App
     */
    public function testgetUser()
    {
        $user = new App();
        $setr = $user->setUser('BLUE','LAL',60);
        $this->assertTrue($setr);

        $d = $user->getUser();
        $this->assertIsArray($d);
        
        $str = $user->getfirst_name();
        $this->assertIsString($str);

        $str2 = $user->getlast_name();
        $this->assertIsString($str2);

        $fn = $user->getage();
        $this->assertIsInt($fn);

        $fn1 = $user->getage2();
        $this->assertTrue($fn1);

        $der = $user->getUser2();
        $this->assertIsString($der);

        $setcar = $user->setCar('xub','2020');
        // $this->assertTrue($setcar);
        $a = $user->getCar();
        $this->assertIsArray($a);
    }
}