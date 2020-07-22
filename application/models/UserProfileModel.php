<?php

defined('BASEPATH') or exit('No direct script access allowed');

class UserProfileModel extends CI_Model {

    public function getUserDetails($request_body){
		$userId = $request_body->user_id;
		$personId = $this->CommonModel->getDataWhere(
        	['person_id'],
        	['id'=>$userId, 'archive'=>0],
        	TBL_PREFIX.'user',
        	['result_type'=>3]
		);
		require_once APPPATH . 'classes/person/person.php';
		$objPerson = new PersonClass\Person();
		$objPerson->setPersonId($personId->person_id);
		$objPerson->setPersonType(1);
		return $objPerson->getPersonDetails(); 
	 }
	 
	  
     
     public function updateUserDetails($request_data){
		$request_all_data = $request_data->data;
		$userId = $request_data->user_id;
		$personDetails= $this->CommonModel->getDataWhere(
        	['person_id'],
        	['id'=>$userId, 'archive'=>0],
        	TBL_PREFIX.'user',
        	['result_type'=>3]
		);
		$personId = $personDetails->person_id;
		require_once APPPATH . 'classes/person/person.php';
		$objPerson = new PersonClass\Person();
		$objPerson->setPersonId($personId);
		if(!empty($request_all_data->phone)){  
			$objPerson->setPersonPhone([(object)['phone'=>strip_tags($request_all_data->phone)]]);
			$objPerson->updatePhone();
		  }
		  if(!empty($request_all_data->email)){
			$objPerson->setPersonEmail([(object)['email'=>strip_tags($request_all_data->email)]]);
			$objPerson->updateEmail();
		  }
		  if(!empty($request_all_data->address)){
			$objPerson->setPersonStreet($request_all_data->address);
			$objPerson->setPersonPostal($request_all_data->postal);
			$objPerson->setPersonState($request_all_data->state);
			$objPerson->setPersonCity($request_all_data->suburb);
			$objPerson->setPersonCountry($request_all_data->country);
			$objPerson->updateAddress();
		  }
		  if(!empty($request_all_data->firstname)||!empty($request_all_data->lastname)){
			$firstName = strip_tags($request_all_data->firstname??'');
			$lastName = strip_tags($request_all_data->lastname??'');
			$objPerson->setFirstName($firstName);
			$objPerson->setLastName($lastName);
			$objPerson->updatePerson();
		  }

		  $TimezoneDetails = getPrimaryTimezoneFromCountryId($request_all_data->country);
		  $this->CommonModel->UpdateData(
			 ['person_id'=>$personId, 'archive' => 0],
			 [
			   'email' => strip_tags($request_all_data->email),
			   'user_timezone'=>isset($TimezoneDetails)? $TimezoneDetails->id : 0
			 ],
			 TBL_PREFIX.'user'
		  );


		  $this->loges->setActivityType('update_profile');
		  $this->loges->setDescription(json_encode($request_data));
		  $this->loges->setTableID($userId);
		  $this->loges->setCreatedBy($userId);
		  $this->loges->setCreatedType(1);
		  $this->loges->createLog();
		return true;
	 }

}