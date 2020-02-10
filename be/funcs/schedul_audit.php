<?php

function schedul_audit(){
    global $BEE_BR_HONEY;
    global $BEE_BOX;
    $flagged = array(
        "less_installments" => array(),
        "more_installments_ne1" => array(),
        "more_installments_no_comment" => array(),
        "more_installments_unknown_comment" => array(),
        "wrong_year" => array(),
        "wrong_month" => array(),
        "expected_to_be_settled" => array(),
        "missing_top_up_entry" => array(),
        "missing_early_settlement_entry" => array(),
        "should_be_early_settlment_but_is_settled" => array(),
        "settled_but_needs_refund" => array(),
        "victims"=>array()
    );
    $loan_contracts  = $BEE_BR_HONEY[0]["loan_contracts"];
    for ($c=0; $c < count($loan_contracts); $c++) { 
        $loan_contract = $loan_contracts[$c];
        //the installments must be at least equal to the number of installments
        $num = intval($loan_contract["number_of_installments"]);
        $installments_count = count($loan_contract["installments"]);
        if($num != $installments_count){
            ///we suspect some thing here
            if($installments_count < $num){
                //this is a mistake
                //tools_dumpx("aaudit schedules ",__FILE__,__LINE__,array($installments_count,$num,$loan_contract["installments"]));
                array_push($flagged["less_installments"],$loan_contract);
                array_push($flagged["victims"],$loan_contract["id"]);
                continue;
            }else if($installments_count > $num ){
                //check if the extra is a topup or settlement payment
                $diff = $installments_count - $num;
                if($diff !=  1 ){
                    //we dont know what the fack is going on here
                    array_push($flagged["more_installments_ne1"],$loan_contract);
                    array_push($flagged["victims"],$loan_contract["id"]);
                    continue;
                }else{
                    //verif that this extra installment is for a top orsettlement
                    $last_installment = $loan_contract["installments"][$installments_count-1];
                    $cmnt = $last_installment["comment"];
                    if($cmnt == null || strlen($cmnt) == 0){
                        //this is not expected because all these such instllments will have a comment
                        array_push($flagged["more_installments_no_comment"],$loan_contract);
                        array_push($flagged["victims"],$loan_contract["id"]);
                        continue;
                    }else if(strpos($cmnt, 'tue:') === 0 || strpos($cmnt, 'ste:') === 0 ){
                        //this is fine then
                    }else{
                        //we dont know whats up
                        array_push($flagged["more_installments_unknown_comment"],$loan_contract);
                        array_push($flagged["victims"],$loan_contract["id"]);
                        continue;
                    }
                }
            }else{
                //this is probably right but other validations could be made in this section
            }
        }
        //audit the schedule dates
        $prev_year = 0;
        $prev_month = 0;
        $prev_day = 0;
        $assigned = false;
        $next_year = 0;
        $next_day = 0;
        $next_month = 0;
        for ($i=0; $i < count($loan_contract["installments"]); $i++) { 
            $installment = $loan_contract["installments"][$i];
            $end_date = $installment["end_date"];
            $parts = explode("-",$end_date); //yyyy-mm-dd
            if($assigned ==  false){
                //the first installment
                $prev_year = intval($parts[0]);
                $prev_month = intval($parts[1]);
                $prev_day = intval($parts[2]);
                $assigned = true;
                if($prev_month == 12){
                    $next_month = 1;
                }else{
                    $next_month = $prev_month + 1;
                }

                if($prev_month == 12){
                    $next_year = $prev_year + 1;
                }else{
                    $next_year = $prev_year;
                }
            }else{
                $prev_year = intval($parts[0]);
                $prev_month = intval($parts[1]);
                $prev_day = intval($parts[2]);
                if($prev_year != $next_year){
                    $loan_contract["reason"] = "year:" .$end_date . ":" . $prev_year . ":" . $next_year ;
                    array_push($flagged["wrong_year"],$loan_contract);
                    array_push($flagged["victims"],$loan_contract["id"]);
                    break;
                }
                if($prev_month != $next_month){
                    $loan_contract["reason"] = "month:" .$end_date . ":" . $prev_year . "-" . $prev_month . ":" . $prev_year . "-". $next_year ;
                    array_push($flagged["wrong_month"],$loan_contract);
                    array_push($flagged["victims"],$loan_contract["id"]);
                    break;
                }
                //if things are fine
                if($prev_month == 12){
                    $next_month = 1;
                }else{
                    $next_month = $prev_month + 1;
                }

                if($prev_month == 12){
                    $next_year = $prev_year + 1;
                }else{
                    $next_year = $prev_year;
                }
            }
        }
    }

    //we are looking for contracts which have more paid money than expected
    //yet they are still on disbursed status
    for ($c=0; $c < count($loan_contracts); $c++) { 
        $loan_contract = $loan_contracts[$c];
        if($loan_contract["status"] == "disbursed"){
            $total_debt = floatval($loan_contract["total_loan_amount"]);
            $total_paid = 0;
            //audit the schedule dates
            for ($i=0; $i < count($loan_contract["installments"]); $i++) { 
                $installment = $loan_contract["installments"][$i];
                for ($p=0; $p < count($installment["installment_repayments"]); $p++) { 
                    $int_rep = $installment["installment_repayments"][$p];
                    $total_paid = $total_paid + floatval($int_rep["repayment_amount"]);
                }
            }
            if($total_paid >= $total_debt){
                $loan_contract["reason"] = "Expected to be settled: ".$total_debt . " : " . $total_paid . " : " . ($total_paid - $total_debt) ;
                array_push($flagged["expected_to_be_settled"],$loan_contract);
                array_push($flagged["victims"],$loan_contract["id"]);
            }
        }

        
        //look for the guys with status toopedup but missing the topup entries
        if($loan_contract["status"] == "toppedup"){
            $hasEntry = false;
            for ($i=0; $i < count($loan_contract["installments"]); $i++) { 
                $installment = $loan_contract["installments"][$i];
                for ($p=0; $p < count($installment["installment_repayments"]); $p++) { 
                    $int_rep = $installment["installment_repayments"][$p];
                    $cmt = $int_rep["comment"];
                    if(strpos($cmt, 'tue:') === 0 || strpos($cmt, 'tue:' > 0 )){
                        $hasEntry = true;
                        break;
                    }
                }
                if($hasEntry == true){
                    break;
                }
            }
            if($hasEntry == false){
                $loan_contract["reason"] = "Missing topup entry: ";
                array_push($flagged["missing_top_up_entry"],$loan_contract);
                array_push($flagged["victims"],$loan_contract["id"]);
            }
        }

        //look for the guys who early settled but missing early settlement entry
        if($loan_contract["status"] ==  "earlysettled"){
            $hasEntry = false;
            for ($i=0; $i < count($loan_contract["installments"]); $i++) { 
                $installment = $loan_contract["installments"][$i];
                for ($p=0; $p < count($installment["installment_repayments"]); $p++) { 
                    $int_rep = $installment["installment_repayments"][$p];
                    $cmt = $int_rep["comment"];
                    if(strpos($cmt, 'ste:') === 0 || strpos($cmt, 'ste:' > 0 )){
                        $hasEntry = true;
                        break;
                    }
                }
                if($hasEntry == true){
                    break;
                }
            }
            if($hasEntry == false){
                $loan_contract["reason"] = "Missing Early Settlement entry: ";
                array_push($flagged["missing_early_settlement_entry"],$loan_contract);
                array_push($flagged["victims"],$loan_contract["id"]);
            }
        }

        //nyd
        //look for guys who settled but are supposed to have early settled
        //when interest and priciple are fully covered this is fine to be on settled status
        //when principle or interest was not fully covered then its suppossed to be early settlement
        if($loan_contract["status"] ==  "settled"){
            $loan_debt = doubleval($loan_contract["total_loan_amount"]);
            $total_paid =0;
            for ($i=0; $i < count($loan_contract["installments"]); $i++) { 
                $installment = $loan_contract["installments"][$i];
                for ($p=0; $p < count($installment["installment_repayments"]); $p++) { 
                    $int_rep = $installment["installment_repayments"][$p];
                    $total_paid = $total_paid + doubleval($int_rep["repayment_amount"]);
                }
            }
            if($loan_debt > $total_paid){
                $loan_contract["reason"] = "Should have been early settlement:" . $loan_debt . ":" . $total_paid . ":" . ($loan_debt - $total_paid);
                array_push($flagged["should_be_early_settlment_but_is_settled"],$loan_contract);
                array_push($flagged["victims"],$loan_contract["id"]);
            }else if($total_paid > $loan_debt){
                $loan_contract["reason"] = "Needs Toup:" . $loan_debt . ":" . $total_paid . ":" . ($loan_debt - $total_paid);
                array_push($flagged["settled_but_needs_refund"],$loan_contract);
                array_push($flagged["victims"],$loan_contract["id"]);
            }
        }

    }

    
    $BEE_BR_HONEY[0]["loan_contracts"] = array($flagged);
}

?>