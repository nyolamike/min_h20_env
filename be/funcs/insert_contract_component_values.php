<?php
    //this function is going to be executed for all dbs/hives
    function insert_contract_component_values(){
        global $BEE_BOX;
        global $foo;
        $bee = $BEE_BOX["BEE"];
        //get all loan contracts
        $querydata = array(
            "loan_contracts" => array(
                "loan_product" => array(
                    "component_values" => array(
                        "interest_rate_component" => array()
                    )
                )
            )
        );
        $brp_res = bee_run_get($querydata,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
        if(count($brp_res[BEE_EI]) > 0){
            $foo = array_merge($foo,$brp_res[BEE_EI]);
        }else{
            $loan_contracts = $brp_res[BEE_RI];
            foreach ($loan_contracts["loan_contracts"] as $loan_contract) {
                $component_values = $loan_contract["loan_product"]["component_values"];
                $contract_components = array();
                foreach ($component_values as $component_value) {
                    
                    array_push($contract_components,array(
                        "loan_contract_id" => $loan_contract["id"],
                        "interest_rate_component_id" => $component_value["interest_rate_component_id"],
                        "value" =>  $component_value["value"]
                    ));
                }
                //post this contracts sub
                $brp_res3 = bee_run_post(array(
                    "contract_component_values" => $contract_components
                ),$bee,0);
                if(count($brp_res3[BEE_EI]) > 0){
                    $foo = array_merge($foo,$brp_res3[BEE_EI]);
                }
            }
           // tools_dumpx("@a tracing null errors",__FILE__,__LINE__, $loan_contracts);
        }
        //tools_dumpx("inside this funct",__FILE__,__LINE__,$bee);
    }
?>