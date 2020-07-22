<?php

/*
 * Filename: Person.php
 * Desc: Deatils of Person
 * @author YDT <yourdevelopmentteam.com.au>
 */

namespace PersonClass;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Person {

    /**
     * @var personId
     * @access private
     * @vartype: integer
     */
    private $personId;

    /**
     * @var personType
     * @access private
     * @vartype: integer
     */
    private $personType = 0;

    /**
     * @var firstName
     * @access private
     * @vartype: varchar
     */
    private $firstName;

    /**
     * @var lastName
     * @access private
     * @vartype: varchar
     */
    private $lastName;

    /**
     * @array personEmail
     * @access private
     * 
     */
    private $personEmail = [];

    /**
     * @array personPhone
     * @access private
     */
    private $personPhone = [];




    private $country;
    private $street;
    private $city;
    private $state;
    private $postal;
    
    /**
     * @function getPersonIdinid
     * @access public
     * @returns $personId integer
     * Get Admin Id
     */
    public function getPersonId() {
        return $this->personId;
    }

    /**
     * @function setPersonId
     * @access public
     * @param $personId integer
     * Set Admin Id
     */
    public function setPersonId($personId) {
        $this->personId = $personId;
    }

        /**
     * @function getFirstName
     * @access public
     * @returns $firstName varchar
     * Get Firstname
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @function setFirstName
     * @access public
     * @param $firstName varchar
     * Set FirstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @function getLastName
     * @access public
     * @returns $lastName varchar
     * Get LastName
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @function setLastName
     * @access public
     * @param $lastName varchar
     * Set LastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * @function getPersonPhone
     * @access public
     * @returns $personPhone object array
     * Get phone
     */
    public function getPersonPhone() {
        return $this->personPhone;
    }

    /**
     * @function setPersonPhone
     * @access public
     * @param $phones object array
     * Set phones
     */
    public function setPersonPhone($phones=[]) {
        $this->personPhone = $phones;
    }

    /**
     * @function getPersonEmail
     * @access public
     * @returns $personEmail object array
     * Get eamils
     */
    public function getPersonEmail() {
        return $this->personEmail;
    }

    /**
     * @function setPersonEmail
     * @access public
     * @param $emails object array
     * Set eamils
     */
    public function setPersonEmail($emails=[]) {
        $this->personEmail = $emails;
    }

    /**
     * @function getPersonType
     * @access public
     * @param $personType varchar
     * Set personType
     */
    public function getPersonType() {
        return $this->personType;
    }

    /**
     * @function setPersonType
     * @access public
     * @param $personType varchar
     * Set personType
     */
    public function setPersonType($personType) {
        $this->personType = (int) $personType;
    }


    /**
     * @function setPersonCountry
     * @access public
     * @param $country varchar
     * Set country
     */
    public function setPersonCountry($country) {
        $this->country =  $country;
    }


    /**
     * @function getPersonCountry
     * @access public
     * @param $country varchar
     * Get country
     */
    public function getPersonCountry() {
        return $this->country;
    }


    /**
     * @function setPersonStreet
     * @access public
     * @param $street varchar
     * Set street
     */
    public function setPersonStreet($street) {
        $this->street =   $street;
    }


    /**
     * @function getPersonStreet
     * @access public
     * @param $street varchar
     * Get street
     */
    public function getPersonStreet() {
        return $this->street;
    }


    /**
     * @function setPersonCity
     * @access public
     * @param $city varchar
     * Set city
     */
    public function setPersonCity($city) {
        $this->city =  $city;
    }


    /**
     * @function getPersonCity
     * @access public
     * @param $city varchar
     * Get city
     */
    public function getPersonCity() {
        return $this->city;
    }

     /**
     * @function setPersonstate
     * @access public
     * @param $state varchar
     * Set state
     */
    public function setPersonState($state) {
        $this->state =   $state;
    }


    /**
     * @function getPersonstate
     * @access public
     * @param $state varchar
     * Get state
     */
    public function getPersonState() {
        return $this->state;
    }

     /**
     * @function setPersonpostal
     * @access public
     * @param $postal varchar
     * Set postal
     */
    public function setPersonPostal($postal) {
        $this->postal =   $postal;
    }


    /**
     * @function getPersonpostal
     * @access public
     * @param $postal varchar
     * Get postal
     */
    public function getPersonPostal() {
        return $this->postal;
    }

    /**
     * @function getPersonTypeByKey
     * @access public
     * @param $personTypeKey varchar
     * return  int personTypeId
     */
    public function getPersonTypeIdByKey($personTypeKey='') {
        $personTypeData = ['user'=>1,'vendor'=>2,'admin'=>3]; 
        return $personTypeData[$personTypeKey]??0;
    }

    /**
     * @function setPersonTypeByKey
     * @access public
     * @param $personTypeKey varchar
     * Set personTypeKey
     */
    public function setPersonTypeIdByKey($personTypeKey='') {
        $this->personType = $this->getPersonTypeIdByKey($personTypeKey);
    }

    public function createPerson() {
        $CI = & get_instance();      
        $personData = array(
            'firstname' => $this->firstName, 
            'lastname' => $this->lastName,  
            'type' => $this->personType, 
            'created' => create_date_store_in_db()
        );
        $personId = $CI->BasicModel->insertRecords(TBL_PREFIX.'person', $personData);
        $this->setPersonId($personId);
        return $personId;
    }

    public function insertPhone($inputName='name') {
        $CI = & get_instance();
        $CI->BasicModel->updateRecords(TBL_PREFIX.'person_phone',['archive'=>1],['person_id' => $this->personId]);
        if (count($this->personPhone) > 0) {
            $addional_phone_number = [];
            $i = 0;
            foreach ($this->personPhone as $key => $val) {
                if(empty($val->{$inputName})){
                    continue;
                }
                $temp = ['phone' => $val->{$inputName}, 'person_id' => $this->personId,'created' => create_date_store_in_db(), 'primary_phone' => 2, 'archive'=>0];
                if ($i == 0) {
                    $temp['primary_phone'] =1;
                } 
                $addional_phone_number[] = $temp;
                $i++;
            }
            if(!empty($addional_phone_number)){
                $CI->BasicModel->insertRecords(TBL_PREFIX.'person_phone', $addional_phone_number,true);
            }
        }
    }

    public function insertEmail($inputName='name') {
        $CI = & get_instance();
        $CI->BasicModel->updateRecords(TBL_PREFIX.'person_email',['archive'=>1],['person_id' => $this->personId]);
        if (count($this->personEmail) > 0) {
            $addional_email = [];
            $i = 0;
            foreach ($this->personEmail as $key => $val) {
                if(empty($val->{$inputName})){
                    continue;
                }
                $temp = ['email' => $val->{$inputName}, 'person_id' => $this->personId,'primary_email'=>2, 'created' => create_date_store_in_db(), 'archive'=>0];
                if ($i == 0) {
                    $temp['primary_email'] =1;
                } 
                $addional_email[] = $temp;
                $i++;
            }
            if(!empty($addional_email)){
                $CI->BasicModel->insertRecords(TBL_PREFIX.'person_email', $addional_email, $multiple = true);
            }
        }
    }

    public function insertAddress(){
        $CI = & get_instance();
        if($this->country || $this->street || $this->city || $this->state || $this->postal){
            $personAddressData = array(
                'person_id' => $this->personId,
                'country_id' =>  $this->country,
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'postal' => $this->postal
            );
            $CI->BasicModel->insertRecords(TBL_PREFIX.'person_address', $personAddressData);
        }  
    }

    public function updatePhone(){
        $CI = & get_instance();
        foreach ($this->personPhone as $key => $val) {
            $personPhoneData = $val->phone;
        }
        $exist =  $CI->BasicModel->getRecordWhere(
            TBL_PREFIX.'person_phone',
            'id',
            array('person_id'=>$this->personId, 'primary_phone'=> 1, 'archive'=>0)
        );
        if(!empty($exist) && $exist->id > 0){
             $CI->BasicModel->updateRecords(TBL_PREFIX.'person_phone',['phone'=>$personPhoneData],['person_id' => $this->personId,'id'=>$exist->id]);
        } else {
            $this->insertPhone('phone');
        }
    }

    public function updateEmail(){
        $CI = & get_instance();
        
        foreach ($this->personEmail as $key => $val) {
            $personEmailData = $val->email;
        }
        $exist =  $CI->BasicModel->getRecordWhere(
            TBL_PREFIX.'person_email',
            'id', 
            array('person_id'=>$this->personId, 'primary_email'=> 1, 'archive'=>0)
        );
       if(!empty($exist) && $exist->id > 0){
        $CI->BasicModel->updateRecords(TBL_PREFIX.'person_email',['email'=>$personEmailData],['person_id' => $this->personId,'id'=>$exist->id]);
        } else {
            $this->insertEmail('email');
        }
    }

    public function updateAddress(){
        $CI = & get_instance();
        $exist =  $CI->BasicModel->getRecordWhere(
            TBL_PREFIX.'person_address',
            'id',
            array('person_id'=>$this->personId));
        if(!empty($exist) && $exist->id > 0){
        if($this->street || $this->city || $this->state || $this->postal){
            $personAddressData = array(
                'country_id' => $this->country,
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'postal' => $this->postal
            );
            $CI->BasicModel->updateRecords(
                TBL_PREFIX.'person_address', 
                $personAddressData, 
                ['person_id' => $this->personId,'id'=>$exist->id]
            );
        } 
        } else {
            $this->insertAddress();
        }
    }

    public function updatePerson() {
        $CI = & get_instance();
        if($this->firstName && $this->lastName){
            $personData = array('firstname' => $this->firstName, 'lastname' => $this->lastName);
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person', $personData, ['id' => $this->personId]);
        } else 
        if($this->firstName){
            $firstname = array('firstname' => $this->firstName);
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person', $firstname, ['id' => $this->personId]);
        } else
        if($this->lastName){
            $lastname = array('lastname' => $this->lastName);
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person', $lastname, ['id' => $this->personId]);
        }
       return true;
    }

    function getPersonDetails($request_body=null){
        $CI = & get_instance();
        if($this->personId){
        $CI->load->model('PersonModel');
        return $CI->PersonModel->get_person_details_by_id($this->personId,$this->personType, $request_body);
        }
    }

    function deletePerson(){
        $CI = & get_instance();
        if($this->personId){
            $this->getPersonTypeById();
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person', ['archive'=>1], ['id' => $this->personId]);
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person_phone', ['archive'=>1], ['person_id' => $this->personId]);
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person_email', ['archive'=>1], ['person_id' => $this->personId]);
            $CI->BasicModel->updateRecords(TBL_PREFIX.'person_address', ['archive'=>1], ['person_id' => $this->personId]);
            if(!empty($this->personType) && $this->personType==3){
                $CI->BasicModel->updateRecords(TBL_PREFIX.'admin_user', ['archive'=>1], ['person_id' => $this->personId]);
            } else if($this->personType==1) {
                $CI->BasicModel->updateRecords(TBL_PREFIX.'user', ['archive'=>1], ['person_id' => $this->personId]);
            } else if($this->personType==2){
                $CI->BasicModel->updateRecords(TBL_PREFIX.'vendor', ['archive'=>1], ['person_id' => $this->personId]);
            }
        }
    }

    function getPersonTypeById(){
        $CI = & get_instance();
        if($this->personId){
            $this->personType = (int) $CI->CommonModel->getDataWhere(['type'],array('id'=>$this->personId),TBL_PREFIX.'person',['result_type'=>3])->type;
       }
    }



}