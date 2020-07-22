<?php
class App{
    private $first_name;
    private $last_name;
    private $age;
    public function getUser(){
        echo 'First Name: '.$this->first_name.'<br>Last Name: '.$this->last_name;
        echo '<br>'.$this->age;
        if($this->age > 17){
            echo "Max from 172222";
        }
        if($this->age > 17){
            echo "Max from 172222";
        }
        if($this->age > 17){
            echo "Max from 172222";
        }
        if($this->age > 17){
            echo "Max from 172222";
        }
        if($this->age > 17){
            echo "Max from 172222";
        }
        if($this->age > 17){
            echo "Max from 17";
        }        echo '<br>';
        if($this->age < 17){
            echo "Min from 172222";
        } 
    }
    public function setUser($fname=false,$lname=false){
        $this->first_name = ($fname)?$fname:'N/A';
        $this->last_name = ($lname)?$lname:'N/A'; 
    }
}
?>