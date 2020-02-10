<?php

function schedul_audit($loan_contracts){
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
        "unmatching_installment_end_date_and_time"  => array(),
        "unmatching_installment_start_date_and_time"  => array(),
        "victims"=>array()
    );
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


        //look for  contractors whoes end time is not the same as end time
        if($loan_contract["status"] == "toppedup"){
            $hasEntry = false;
            for ($i=0; $i < count($loan_contract["installments"]); $i++) { 
                $installment = $loan_contract["installments"][$i];
                $expected_start_time = $installment["start_time"];
                $expected_start_time2 = time($installment["start_date"]);
                $end_time = "";
                $expected_date_time = $installment["end_time"];
                $expected_date_time2 = time($installment["end_date"]);
                if($expected_date_time != $expected_date_time2){
                    //add to troubled values
                    array_push($flagged["unmatching_installment_end_date_and_time"],$loan_contract);
                    array_push($flagged["victims"],$loan_contract["id"]);
                    break;
                }
                if($expected_start_time != $expected_start_time2){
                    //add to troubled values
                    array_push($flagged["unmatching_installment_start_date_and_time"],$loan_contract);
                    array_push($flagged["victims"],$loan_contract["id"]);
                    break;
                }
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

    return $flagged;
}

function ahc_get_fiscal_year($fiscal_years,$entryTime){
    $fiscal_year = null;
    for($fy=0;$fy<count($fiscal_years); $fy++){
        $tfy = $fiscal_years[$fy];
        if($tfy["open_time"] >= $entryTime && $tfy["end_time"] <= $entryTime ){
            $fiscal_year = $tfy;
            break;
        }
    }
    return $fiscal_year;
}

function  ahc_process_accounting(
    $accounting_events,$amount_source,$entryTime,$entryDateStr,
    $amount,$fiscal_year_id,$context_msg, $loan_id, $installment_id){
    //debits and credits must balance
    $entries = array();
    $transactions = array();
    $errors = array();
    //an accouting closure
    $closure = array(
        "occurancy_time" =>  $entryTime,
        "occurancy_date" =>  $entryDateStr,
        "remarks" => "Historical data interest accrual for #" . $loan_id . "-" . $installment_id,
        "user_id" => 0
    );
    //every event create an accounting transaction
    for ($aei = 0; $aei < count($accounting_events); $aei++) { //aei => accounting event index
        $event = $accounting_events[$aei];
        $rules = $event["accounting_rules"];
        $jornals = array();
        $debitsTotal = 0;
        $creditsTotal = 0;
        if(count($rules) == 0) {
            //this event is meaningless
            continue;
        }
        for($j = 0; $j < count($rules); $j++) {
            $rule = $rules[$j];
            $jId = intval($rule["journal_id"]);
            if(count($jornals) == 0) {
                array_push($errors, $jId);
            }elseif (in_array($jId,$jornals) == true) {
                $msg = $context_msg . "More than one journal is involved in this entry, transaction failed";
                array_push($errors,$msg);
                return array($entries,$transactions,$errors,$closure);
            }
            $accounting_entry = array(
                "_fk_accounting_transaction_id" => "accounting_transactions@" . $aei,
                "account_id" => $rule["account_id"],
                "accounting_rule_id" => $rule["id"],
                "is_accounting_rule" =>  1,
                "amount" =>  0,
                "direction" =>  $rule["direction"],
                "remarks" => "Loan Interest Accrual for #" . $loan_id . "-" . $installment_id,
                "ledger_id" =>  $rule["ledger_id"],
                "is_manuall_entry" =>  0
            );
            $thisAmount = 0;
            if (isset($rule["attached_amount"]) && isset($amount_source[$rule["attached_amount"]])) {
                $thisAmount = $amount_source[$rule["attached_amount"]];
            } else {
                $msg = $context_msg . "An accounting rule for the event: " . $event["name"] . ", is missing an attached value@:" . $rule["attached_amount"];
                array_push($errors,$msg);
                return array($entries,$transactions,$errors,$closure);
            }
            $accounting_entry["amount"] = $thisAmount;
            array_push($entries, $accounting_entry);
            if ($rule["direction"] == "debit") {
                $debitsTotal = $debitsTotal + $thisAmount;
            }elseif ($rule["direction"] == "credit") {
                $creditsTotal = $creditsTotal + $thisAmount;
            }
        }
        if(count($jornals) == 0) {
            $msg = $context_msg . "An accounting event: " . $event["name"] . ", is missing acounting jornals";
            array_push($errors,$msg);
            return array($entries,$transactions,$errors,$closure);
        }
        $acc_tra = array(
            "accounting_event_id" => $event["id"],
            "journal_id" =>  $jornals[0],
            "occurancy_time" =>  $entryTime,
            "occurancy_date" =>  $entryDateStr,
            "amount" =>  $amount, 
            "fiscal_year_id" =>  $fiscal_year_id,
            "description" =>  "Loan Interest Accrual",
            "user_id" => 0,
            "is_closed" =>  1,
            "_fk_accounting_closure_id" =>  "accounting_closure",
            "is_manuall_entry" => 0,
            "remarks" => "Loan Interest Accrual for #" . $loan_id . "-" . $installment_id,
            "direction" =>  "__"
        );
        array_push($transactions, $acc_tra);
        //check for a balanced transaction
        //first roundoff
        $debitsTotal =  round($debitsTotal,2);
        $creditsTotal = round($creditsTotal,2); 
        if ($debitsTotal != $creditsTotal) {
            $msg = $context_msg . "This transaction is not balanced [dbt:" . $debitsTotal . ", cdt:" . $creditsTotal . "], please check your accounting rules";
            array_push($errors,$msg);
            return array($entries,$transactions,$errors,$closure);
        }
    }
    return array($entries,$transactions,$errors,$closure);
}

function acrual_historical_contracts(){
    global $BEE_BOX;
    global $foo;
    $bee = $BEE_BOX["BEE"];
    $audits = array();//contracts which are under auditing
    //get all roles
    $querydata = array(
        "loan_contracts" => array(
            "installments" => array(
                "installment_repayments" => array(),
                "interest_accruals" => array()
            )
        ),
        "fiscal_years" => array(),
        "accounting_events" => array(
            "accounting_rules" => array(),
            "_asc" => array("sort_order"),
            "_w" => array(
                array("system_event_name", "e", "LIAE") //Loan Interest Accrual Event
            )
        )
    );
    $brp_res = bee_run_get($querydata,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
    if(count($brp_res[BEE_EI]) > 0){
        $foo = array_merge($foo,$brp_res[BEE_EI]);
    }else{
        $now = time();
        $loan_contracts = $brp_res[BEE_RI]["loan_contracts"];
        $fiscal_years = $brp_res[BEE_RI]["fiscal_years"];
        $accounting_events = $brp_res[BEE_RI]["accounting_events"];
        //accounting events defined
        if (count($accounting_events)== 0) {
            //nyd
            array_push($foo, "There is no accounting event defined for interest accrual ");
            return; //exit the function
        }
        $victims = schedul_audit($loan_contracts);
        //you cannot acrual before the first installment date
        //and you cannot also accrual after the last installment date
        for($i=0; $i < count($loan_contracts); $i++){
            $loan = $loan_contracts[$i];
            //must not be among the contracts under auditing
            if(in_array(intval($loan["id"]),$auditsvictims)){
                $msg = $cntx_msg . " contract is till under auditing loan id " . $loan["id"];
                array_push($foo, $msg);
                continue; //
            }

            //skip all contracts with status == "written off"
            if($loan["status"] == "writtenoff" || $loan["status"] == "pending" || $loan["status"] == "rejected" 
            || $loan["status"] == "acknowledged"  ){
                continue; //all these loans right now have no repayments
            }

            $mint = $loan["monthly_interest_amount"]; //monthly interest amount
            $start = $loan["start_time"];
            $end = $loan["end_time"];
            if($start < $now){
                continue; //this means that this loan has not yet started as of this date. today
            }
            
            //if a loan is settled, earlysettled, or toppedup then we accrual according to its reapyments
            if($loan["status"] == "settled" || $loan["status"] == "earlysettled" || $loan["status"] == "toppedup"){
                //accrual the entire loan
                for($i=0; $i < count($loan_contract["installments"]); $i++) { 
                    $installment = $loan_contract["installments"][$i];
                    $cntx_msg = "LIAE4" . $installment["loan_contract_id"] . "-" . $installment["id"];
                    $repayments = $installment["installment_repayments"];
                    $amount_acrrued = 0;
                    for($ri=0; $ri < count($repayments); $ri++) {
                        $repayment = $repayments[$ri];
                        $amount_acrrued = $amount_acrrued + $repayment["interest_repaid_amount"];
                    }
                    //since this was a normal this all installments accrued their interests
                    $entryTime = $installment["end_time"]; 
                    //accounting rules
                    $fiscal_year = ahc_get_fiscal_year($fiscal_years,$entryTime);
                    if($fiscal_year == null) {
                        $msg = $cntx_msg . " There is no fiscal year for the date " . $installment["end_date"] . " when to trying to accrual historical data ";
                        array_push($foo, $msg);
                        continue; //go to the next installment for this loan contract
                    }

                    //process accounting logic
                    $acc_res = ahc_process_accounting(
                        $accounting_events,
                        array(
                            "LIAE_aia" => $amount_acrrued
                        ),
                        $installment["end_time"],
                        $installment["end_date"],
                        $amount_acrrued,
                        $fiscal_year["id"],
                        $cntx_msg, 
                        $installment["loan_contract_id"], 
                        $installment["id"]
                    );
                    $acc_erors = $acc_res[2];
                    $acc_entries = $acc_res[0];
                    $acc_transactions = $acc_res[1];
                    $acc_closure = $acc_res[3];
                    if(count($acc_erors) > 0){
                        $foo = array_merge($foo,$acc_erors);
                        continue; //go to the next installment for this loan contract
                    }
                    
                    //a new accrual row has to be insertted
                    $acc_data = array(
                        "loan_contract_id" => $loan_contract["id"],
                        "installment_id" => $installment["id"],
                        "total_interest_accrued" => $amount_acrrued,
                        "expected_installment_time" => $installment["end_time"],
                        "expected_installment_date" => $installment["end_date"],
                        "history" => $history,
                        "last_accrued_time" => $installment["end_time"],
                        "last_accrued_date" => $installment["end_date"],
                        "last_amount_accrued" => $amount_acrrued,
                        "remarks" => ""
                    );
                    $data_to_post = array(
                        "accounting_closure" => $acc_closure,
                        "accounting_transactions" => $acc_transactions,
                        "accounting_entries" => $acc_entries,
                        "interest_accrual" => $acc_data
                    );
                    $dtp_res = bee_run_post($data_to_post,$bee,0);
                    //bee_run_get($querydata,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
                    if(count($dtp_res[BEE_EI]) > 0){
                        $foo = array_merge($foo,$brp_res[BEE_EI]);
                    }else{
                        //was fine
                    }
                    
                }
            }elseif ($loan["status"] == "disbursed") {
                //acrrual untill today
                for($i=0; $i < count($loan_contract["installments"]); $i++) { 
                    $installment = $loan_contract["installments"][$i];
                    $entryTime = $installment["end_time"];
                    $startTime = $installment["start_time"];
                    $entryDate = $installment["end_date"];
                    $amount_acrrued = 0;
                    if($startTime > $now && $entryTime > $now ){ //.||
                        continue; //because this installment has not yet reached accrual time
                    }if($startTime < $now && $entryTime >= $now ){ //|.|
                        //accrual only for these number of days for this installment
                        $monthly_interest_to_pay = doubleval($installment["interest_amount"]);
                        $lastDayThisMonth = date("Y-m-t");
                        $no_of_days = intval(explode("-",$lastDayThisMonth)[2]);
                        $daily_interest = round(($monthly_interest_to_pay / $no_of_days),2);
                        $amount_acrrued = $daily_interest * $no_of_days;
                        $entryTime = $now;
                        $entryDate = date("Y-m-d");
                    }elseif($startTime < $now && $entryTime < $now ){ //||.
                        //accrual all the interest for this installment
                        $amount_acrrued = $installment["interest_amount"];
                        $entryDate = $installment["end_date"];
                    }
                    $cntx_msg = "LIAE4" . $installment["loan_contract_id"] . "-" . $installment["id"];
                    //accounting rules
                    $fiscal_year = ahc_get_fiscal_year($fiscal_years,$entryTime);
                    if($fiscal_year == null) {
                        $msg = $cntx_msg . " There is no fiscal year for the date " . $installment["end_date"] . " when to trying to accrual historical data ";
                        array_push($foo, $msg);
                        continue; //go to the next installment for this loan contract
                    }

                    //process accounting logic
                    $acc_res = ahc_process_accounting(
                        $accounting_events,
                        array(
                            "LIAE_aia" => $amount_acrrued
                        ),
                        $entryTime,
                        $entryDate,
                        $amount_acrrued,
                        $fiscal_year["id"],
                        $cntx_msg, 
                        $installment["loan_contract_id"], 
                        $installment["id"]
                    );
                    $acc_erors = $acc_res[2];
                    $acc_entries = $acc_res[0];
                    $acc_transactions = $acc_res[1];
                    $acc_closure = $acc_res[3];
                    if(count($acc_erors) > 0){
                        $foo = array_merge($foo,$acc_erors);
                        continue; //go to the next installment for this loan contract
                    }
                    
                    //a new accrual row has to be insertted
                    $acc_data = array(
                        "loan_contract_id" => $loan_contract["id"],
                        "installment_id" => $installment["id"],
                        "total_interest_accrued" => $amount_acrrued,
                        "expected_installment_time" => $entryTime,
                        "expected_installment_date" => $entryDate,
                        "history" => "",
                        "last_accrued_time" => $entryTime,
                        "last_accrued_date" => $entryDate,
                        "last_amount_accrued" => $amount_acrrued,
                        "remarks" => ""
                    );
                    $data_to_post = array(
                        "accounting_closure" => $acc_closure,
                        "accounting_transactions" => $acc_transactions,
                        "accounting_entries" => $acc_entries,
                        "interest_accrual" => $acc_data
                    );
                    $dtp_res = bee_run_post($data_to_post,$bee,0);
                    //bee_run_get($querydata,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
                    if(count($dtp_res[BEE_EI]) > 0){
                        $foo = array_merge($foo,$brp_res[BEE_EI]);
                    }else{
                        //was fine
                    }
                }
            }

        }  
    }
}

?>