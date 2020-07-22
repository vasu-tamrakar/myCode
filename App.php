<?php
class App{
    private $first_name;
    private $last_name;
    private $age;
    public function getUser(){
        echo 'First Name: '.$this->first_name.'<br>Last Name: '.$this->last_name;
        echo '<br>'.$this->age;
        $max="Max from 172222";
        if($this->age > 17){
            echo $max;
        }
        if($this->age > 17){
            echo $max;
        }
        if($this->age > 17){
            echo $max;
        }
        if($this->age > 17){
            echo $max;
        }
        if($this->age > 17){
            echo $max;
        }
        if($this->age > 17){
            echo $max;
        }        echo '<br>';
        if($this->age < 17){
            echo $max;
        } 
    }
    public function setUser($fname=false,$lname=false){
        $this->first_name = ($fname)?$fname:'N/A';
        $this->last_name = ($lname)?$lname:'N/A'; 
    }

    public function setUser1($fname=false,$lname=false, $age=false){
        $this->first_name = ($fname)?$fname:'N/A';
        $this->last_name = ($lname)?$lname:'N/A'; 
        $this->age = ($age)?$age:'N/A';
    }
}
?>