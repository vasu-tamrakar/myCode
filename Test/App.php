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
        return array('first_name'=>$this->first_name, 'last_name'=>$this->last_name,'age'=>$this->age);
    }
    public function getUser2(){
        if($this->age >10){
            echo 'age grether than 10';
        }else{
            echo 'age less than 10';
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
    }

    public function getfirst_name(){
        return $this->first_name;
    }
    public function getlast_name(){
        return $this->last_name;
    }
    public function getage(){
        if($this->age >10){
            return $this->age;
        }else{
            return 'less than 10';
        }
    }
    public function getage2(){
        if($this->age >10){
            return $this->age;
        }else{
            return 'less than 10';
        }
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
