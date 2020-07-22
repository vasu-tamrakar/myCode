<?php
class App
{
    private $first_name;
    private $last_name;
    private $age;
    private $name;
    private $model;
    private $price;


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


    public function setCar($name=false,$model=false)
    {
        $this->name = $name?$name:'N/a';
        $this->model = $model?$model:'N/a';
    }

    public function setCar1($name=false,$model=false)
    {
        $this->name = $name?$name:'N/a';
        $this->model = $model?$model:'N/a';
    }
    public function setCar2($name=false,$model=false)
    {
        $this->name = $name?$name:'N/a';
        $this->model = $model?$model:'N/a';
    }
    
}
