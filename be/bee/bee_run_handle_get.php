<?php
function bee_run_handle_get($res,$bee,$querydata=null){
    global $BEE_GLOBALS;
    global $countries_list;
    global $bee_show_sql;
    global $xt_order;
    global $BEE_BR_HONEY;
    global $BEE_BOX;
    
    if(isset($_GET["q"]) && $querydata == null){
        $query_base64 = $_GET["q"];
        //tools_dumpx("_GET",__FILE__,__LINE__,$query_base64);
        $query = base64_decode($query_base64);
        //tools_dumpx("query",__FILE__,__LINE__,$query);
        $tsji_res = tools_suck_json_into($query, array());
        $res[BEE_EI] = array_merge($tsji_res[BEE_EI],$res[BEE_EI]);
        if(count($res[BEE_EI])==0){//no errors
            $querydata = $tsji_res[BEE_RI];
        }
    }
    
    if($querydata == null){
        //there is nothing to process
        array_push($res[BEE_EI],"Missing query parameter q in the url");
    }else{
        //check if we can return all the sqls
        if(array_key_exists("_sql",$querydata)){
            $bee_show_sql = true;
        }

        $included_before_functions_paths = array(); //we cant include a file twice
        $before_functions = array();


        //extraction order
        $xt_order = array();
        $isInAllowed = false;
        $rootNodesNumber = 0;
        foreach ($querydata as $keyF => $valueF) {
            $rootNodesNumber = $rootNodesNumber + 1;
            if($keyF == "_allowed"){
                $isInAllowed = true;
            }
            if(tools_startsWith($keyF, "_xtu_")){
                array_push($xt_order,"xtu");
            }
            if(tools_startsWith($keyF, "_nxtva_")){
                array_push($xt_order,"_nxtva_");
            }
            if(tools_startsWith($keyF, "_index_")){
                array_push($xt_order,"_index_");
            }
            if(tools_startsWith($keyF, "_indexk_")){
                array_push($xt_order,"_indexk_");
            }
            if(tools_startsWith($keyF, "_tree_sum_")){
                array_push($xt_order,"_tree_sum_");
            }
            if(tools_startsWith($keyF,"_f_br")){
                //list down the functions/flowers that will be visited/executed just
                //before the data/honey is returned
                
                if(is_string($valueF)){
                    $before_functions = explode(" ",$valueF);
                }else{
                    $before_functions = $valueF;
                }   
            }
            if(tools_startsWith($keyF,"_box") && $keyF == "_box"){
                $BEE_BOX = $valueF;
            }
        }

        if($isInAllowed == true && $rootNodesNumber > 1){
            array_push($res[BEE_EI],"_allowed must be the only root node in the structure");
            return $res;
        }

        //get system modules
        if(array_key_exists("_f_modules",$querydata)){
            $res[BEE_RI] = bee_security_modules($bee);
        }elseif(array_key_exists("_f_actions",$querydata)){
            $res[BEE_RI] = bee_security_actions($bee);
        }elseif(array_key_exists("_f_permissions",$querydata)){
            $res[BEE_RI] =  bee_security_permissions($bee);
        }elseif(array_key_exists("_f_countries",$querydata)){
            $res[BEE_RI] = array(
                "countries" => $countries_list
            );
        }elseif(array_key_exists("_f_now",$querydata)){
            $res[BEE_RI] = array(
                "now" => time()
            );
        }elseif(array_key_exists("_f_version",$querydata)){
            $v = $bee["BEE_HIVE_STRUCTURE"]["version"];
            $contents = file_get_contents("bee/updates/".$v.".json");
            $json_notes = tools_jsonify($contents);
            $res[BEE_RI] =  array(
                "version" => $json_notes[0] 
            );
            $res[BEE_EI] = array_merge($res[BEE_EI],$json_notes[BEE_EI]);
        }elseif(array_key_exists("_f_bee",$querydata)){
            $res[BEE_RI] =  array(
                "bee" => array(
                    "date" => date("Y-m-d") . "@" . BEE_GARDEN
                )
            );
        }elseif(array_key_exists("_f_version_updates",$querydata)){
            $res[BEE_RI] = $querydata["_f_version_updates"]["_from"];
        }elseif(array_key_exists("_f_xlsx",$querydata)){ //reading excell files
            //you must be logged in to access this
            if($bee["BEE_USER"]["id"] != 0){
                //this reads data from an excell file
                $resRead = array(
                    "_f_xlsx" => array(
                        "_from" => array()
                    )
                );
                //if _file_ part contains bee/temp_uploads/uf_ then it is uploaded first
                //else we assume that that is the file name
                $filePath = "";
                $source= $querydata["_f_xlsx"]["_from"];
                foreach($source as $sourceItemKey => $sourceItemObject) {
                    $resRead["_f_xlsx"]["_from"] = array();
                    $resRead["_f_xlsx"]["_from"][$sourceItemKey] = null;
                    foreach($sourceItemObject as $sourceItemObjectKey => $sourceItemObjectObject) {
                        $resRead["_f_xlsx"]["_from"][$sourceItemKey] = array();
                        $resRead["_f_xlsx"]["_from"][$sourceItemKey][$sourceItemObjectKey] = null;
                        if(tools_startsWith($sourceItemObjectKey,"_file_")){
                            $fileNameFromClient = $sourceItemObjectObject;
                            if(tools_startsWith($fileNameFromClient,"bee/temp_uploads/uf_")){ 
                                //this file needs to be uploaded
                                $section_name = $sourceItemObjectKey;
                                $value = $fileNameFromClient;
                                $comb_name = $sourceItemKey;
                                $structure = $bee["BEE_HIVE_STRUCTURE"]["combs"];
                                $bsefv_res = bee_segmentation_evaluate_file_value($section_name,$value,$comb_name,$structure);
                                if(count($bsefv_res[BEE_EI])==0){
                                    $res_section_name = $bsefv_res[BEE_RI][0];
                                    $filePath =  $bsefv_res[BEE_RI][1];
                                }else{
                                    $res[BEE_EI] = array_merge($res[BEE_EI],$bsefv_res[BEE_EI]); 
                                }
                            }else{
                                $filePath = $fileNameFromClient;
                            }
                        }
                        //now we need a path to this file
                        $target_file = $filePath; //uploads/batch_repayments/2019 01 police repyments.xlsx
                        if (file_exists($target_file)) {
                            //https://www.phpclasses.org/package/6279-PHP-Parse-and-retrieve-data-from-Excel-XLS-files.html
                            $xlsx = SimpleXLSX::parse($target_file);
                            if($xlsx){
                                $resRead["_f_xlsx"]["_from"][$sourceItemKey][$sourceItemObjectKey] =  $xlsx->rows();
                            }else {
                                $err = SimpleXLSX::parseError();
                                array_push($res[BEE_EI], $err);
                            }
                            
                        }else{
                            array_push($res[BEE_EI], "File : " . $target_file ." does not exist");
                        }
                    }
                }

                $res[BEE_RI] = $resRead;
                
            }else{
                array_push($res[BEE_EI],"You are not authorised to access this resource: reading server excell files");
            }
        }else{
            $isInAllowedContext = false;
            $allowedContext = "get";
            //check if we only have allowed
            if($isInAllowed == true && $rootNodesNumber == 1){
                $isInAllowedContext = true;
                $querydata = $querydata["_allowed"];
            }
            //tools_dumpx("allowed combs",__FILE__,__LINE__,array($isInAllowedContext,$allowedContext,$querydata));

            //authorise
            $bsv_res = bee_security_authorise(
                $bee["BEE_USER"],
                $querydata,
                $bee["BEE_HIVE_STRUCTURE"]["combs"],
                false, //create
                true, //read
                false, //update
                false, //delete
                $isInAllowedContext,
                $allowedContext
            );
            
            $res[BEE_EI] = array_merge($res[BEE_EI],$bsv_res[BEE_EI]);

            if($isInAllowed == true && $rootNodesNumber == 1){
                if($bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
                    //first login as a the db owner
                    $whole_honey = array();
                    $login_nector = array(
                        "_f_login" => array(
                            "email" => $bee["BEE_HIVE_STRUCTURE"]["_f_register"]["email"],
                            "password" => $bee["BEE_HIVE_STRUCTURE"]["_f_register"]["password"],
                        )
                    );
                    $BEE_GLOBALS["is_login_call"] = true;
                    $hrl_res = bee_hive_run_login($login_nector, $bee);
                    $BEE_GLOBALS["is_login_call"] = false;
                    $res[BEE_EI] = array_merge($res[BEE_EI],$hrl_res[BEE_EI]); 
                }else{
                    array_push($res[BEE_EI],"_allowed calls can only be made if is_registration_public == false, as the enginee will not be able to fetch the owners database in its multitenant environment");
                }
            }
            
           
            if(count($res[BEE_EI])==0){//no errors
                //tools_dumpx("querydata",__FILE__,__LINE__,$querydata);
                $brp_res = bee_run_get($querydata,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
                //tools_dump("@a tracing null errors",__FILE__,__LINE__,$brp_res[BEE_EI]);
                $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
                //tools_dump("tracing null errors ",__FILE__,__LINE__,$res[BEE_EI]);
                $res[BEE_RI] = $brp_res[BEE_RI];

                //start: execute server flowers/functions before return
                if(count($before_functions) > 0){;
                    $if_executed_smthing = false;
                    //we have some flowers to visit
                    //1.check _hive.for _f_br
                    $_f_br = null;
                    if(array_key_exists("_f_br",$bee["BEE_HIVE_STRUCTURE"])){
                        $_f_br = $bee["BEE_HIVE_STRUCTURE"]["_f_br"];
                    }
                    for ($ibf=0; $ibf < count($before_functions); $ibf++) {  //ibf => index of before function
                        $functions_name = $before_functions[$ibf];
                        //check if this function has path config
                        $path = "";
                        if($_f_br != null && array_key_exists($functions_name,$_f_br)){
                            $path = $_f_br[$functions_name];
                        }   
                        $fileToInclude = $path . $functions_name . ".php";
                        //include this functions config if its not alreday included
                        if(in_array($fileToInclude,$included_before_functions_paths) == false){
                            //its not yet included, so we include it
                            include($fileToInclude);
                            array_push($included_before_functions_paths,$fileToInclude);
                            //execute that function
                            $if_executed_smthing = true;
                            $BEE_BR_HONEY = $res;
                            $exec = $functions_name."();";
                            eval($exec);
                        }
                    }
                    if($if_executed_smthing == true){
                        $res = $BEE_BR_HONEY;
                    }
                }
                //end: execute server flowers/functions before return

            }
            

            if($isInAllowed == true && $rootNodesNumber == 1 && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
                $res[BEE_RI] = array(
                    "_allowed" => $res[BEE_RI]
                );
            }
        }  
    }
    if($BEE_BOX !== null){
        $res[BEE_RI]["_box"] = $BEE_BOX;
    }
    //array_push($res[BEE_EI],"Token expired test"); //epired test
    return $res;
}
?>