<?php
require __DIR__ .'/../vendor/autoload.php';
use PHPUnit\Framework\TestCase;
use App;

    
class TestApp extends TestCase
// class TestApp extends App
{

    
    /**
     * @covers App::getUser
     */

    public function testgetUser()
    {
        $user = new App();
        $user->setUser('BLUE','LAL',60);
        $d = $user->getUser();
        $this->assertIsArray($d);
        return $d;
    }

    /**
     * @covers App::getage2
     */


    public function testEmpty()
    {

        $use2 = new App();
        $use2->setUser('BLUE','LAL', 17); 
        $age = $use2->getage2();
        $this->assertTrue($age > 0);
        return $age;
    }

    public function testEmpty2()
    {
        $u1 = new App();
        $u1->setUser('BLUE','LAL', 17);
        $str = $u1->getfirst_name();
        $str2 = $u1->getfirst_name();
        $this->assertIsString($str);
        $this->assertIsString($str2);
        return $u1->getfirst_name();
    }
}