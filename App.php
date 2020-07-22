<?php
class App
{
    private $first_name;
    private $last_name;
    private $age;
    public function getUser(){
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        if($this->age >10){
            echo 'age grether than 10';
        }
        return array('first_name'=>$this->first_name, 'last_name'=>$this->last_name,'age'=>$this->age);
    }

    public function setUser($fname=false,$lname=false,$age=false)
    {
        $this->first_name = $fname?$fname:'N/a';
        $this->last_name = $lname?$lname:'N/a';
        $this->age = $age?$age:'N/a';
    }
}
