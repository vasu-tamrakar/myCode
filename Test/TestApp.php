<?php
use PHPUnit\Framework\TestCase;
require_once '..\main\App.php';
class TestApp extends TestCase
// class TestApp extends App
{
    public function testFailure()
    {
        $user = new App();
        $user->setUser('BLUE','LAL',60);
        $this->assertIsArray($user->getUser());
    }
}