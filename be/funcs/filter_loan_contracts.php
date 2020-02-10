<?php
    function filter_loan_contracts_helper($query,$str){
        if(is_string($str) == false || strlen(($str = trim($str))) == 0){
            return false;
        }
        $str = strtolower($str);
        if (strpos("~".$str, $query) !== false) {
            return true;
        }
        return false;
    }

    function filter_loan_contracts(){
        global $BEE_BR_HONEY;
        global $BEE_BOX;
        $loan_contracts  = $BEE_BR_HONEY[0]["loan_contracts"];
        $search = strtolower($BEE_BOX["search"]);
        $filtered = array();
        for ($i=0; $i < count($loan_contracts); $i++) { 
            $contract = $loan_contracts[$i];
            $client = $contract["client"];
            if(filter_loan_contracts_helper($search,$client["first_name"]) ||
                filter_loan_contracts_helper($search,$client["last_name"]) ||
                filter_loan_contracts_helper($search,$client["computer_number"]) 
            ){
                array_push($filtered, $contract);
            }
        }
        $BEE_BR_HONEY[0]["loan_contracts"] = $filtered;
    }

    
?>