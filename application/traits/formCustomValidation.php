<?php

trait formCustomValidation {


    public function valid_password($password){
        
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        if (!preg_match($pattern, $password))
        {
            $this->form_validation->set_message('valid_password', 'Password must be minimum 8 characters contain atleast 1 capital letter, number and special character');
            return FALSE;
        }

        return TRUE;
    }


    public function no_special_characters($query, $array){

        $parms = json_decode($array, true);
        $message = $parms['message'] ?? "Value shouldn't have special characters.";

        $pattern = '/^[A-Za-z0-9\s]+$/';
        
        if (!preg_match($pattern, $query))
        {
            $this->form_validation->set_message('no_special_characters', $message);
            return FALSE;
        }

        return TRUE;


    }

    
    public function valid_amt(string $amt){
        $pattern = AMOUNT_REGEX_KEY;
        if (!preg_match($pattern, $amt))
        {
            $this->form_validation->set_message('valid_amt', 'The %s field will be 8 digits positive number with 2 decimals.');
            return FALSE;
        }
        return TRUE;
    }
    public function valid_balance(string $balance){        
        $pattern2 = BALANCE_REGEX_KEY;
        if (!preg_match($pattern2, $balance))
        {
            $this->form_validation->set_message('valid_balance', 'The %s field will be 8 digits number with 2 decimals.');
            return FALSE;
        }
        return TRUE;
    }

    public function alpha_num_nospace(string $invnum){
        if($invnum !=''){
            if (!preg_match('/^[\S+]+$/i', $invnum))
            {
                $this->form_validation->set_message('alpha_num_nospace', 'The %s field will be accept without space alfa-numeric.');
                return FALSE;
            }
        }
        return TRUE;
    }
    
}

