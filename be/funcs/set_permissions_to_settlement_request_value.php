<?php
    //this function is going to be executed for all dbs/hives
    function set_permissions_to_settlement_request_value(){
        global $BEE_BOX;
        global $foo;
        $bee = $BEE_BOX["BEE"];
        //get all roles
        $querydata = array(
            "roles" => array()
        );
        $brp_res = bee_run_get($querydata,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
        if(count($brp_res[BEE_EI]) > 0){
            $foo = array_merge($foo,$brp_res[BEE_EI]);
        }else{
            $roles = $brp_res[BEE_RI];
            $role_perms = array();
            foreach ($roles["roles"] as $role) {
                array_push($role_perms,array(
                    "role_id" => $role["id"],
                    "permission" => "settlement_request_value",
                    "can_create" =>  1,
                    "can_read" =>  1,
                    "can_update" =>  1,
                    "can_delete" =>  1
                ));
            }
            //post these permsions
            $brp_res3 = bee_run_post(array(
                "role_permisiions" =>  $role_perms
            ),$bee,0);
            if(count($brp_res3[BEE_EI]) > 0){
                $foo = array_merge($foo,$brp_res3[BEE_EI]);
            }
        }
    }
?>