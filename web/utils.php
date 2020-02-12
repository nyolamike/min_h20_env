<?php

    function tools_dump($mark,$file,$line,$item,$k=null){
        $can_dump = true;
        if($k != null){
            $can_dump = false;
            if(is_array($item) && array_key_exists($k,$item)){
                $can_dump = true;
            }
        }

        if($can_dump){
            echo "<br/><br/>...............................<br/>Start: ".$mark." <br/>In: &nbsp;&nbsp;&nbsp;&nbsp;" . $file . " <br/>On: &nbsp;&nbsp;&nbsp;" . $line ."<br/>...............................<br/><pre><code>";
            var_dump($item);
            echo "</code></pre><br/>...............................<br/>End: ".$mark."<br/>...............................<br/><br/>";
        }
    }

    function tools_dumpx($mark,$file,$line,$item,$k=null){
        tools_dump($mark,$file,$line,$item,$k=null);
        exit(0);
    }

    function utils_nbsp($count=1){
        $space = "";
        for($i=0;$i<$count;$i++){
            $space .= "&nbsp;";
        }
        return $space;
    }

    function utils_menu_item($label,$link,$children=array(),$page=""){
        global $_ace_ts;
        if(strlen($link) > 0){
            // we need default page pages
            // remove the layout
            $page = str_replace("/","_",$link);
            //nyd
            //check if page file for this link is there
            //if not then create a default page
            $file_name = "layout_".$page."_page.php";
            if(!file_exists($file_name)){
                $page_template = $_ace_ts["layout_page"];
                $cont = str_replace("_ace_page_id",$page,$page_template);
                $cont = str_replace("_ace_page_title",$label,$cont);
                $cont = str_replace("_ace_page_name",$page,$cont);
                file_put_contents($file_name, $cont);
            }
        }
        
        return array(
            "label" => $label,
            "link" => "#" . $link,
            "children" => $children,
            "page" => $page
        );
    }

    function utils_get_template($name){
        $template_path = "_ace_templates/_ace_";
        return file_get_contents($template_path . $name . ".html");
    }

    function utils_load_templates($names){
        $t = array();
        for ($i=0; $i < count($names); $i++) { 
            $t[$names[$i]] = utils_get_template($names[$i]);
        }
        return $t;
    }

    function _ace_page_header($page_title, $bread_crump_items=array()){ 
        global $_ace_ts;
        $temp_text = $_ace_ts["page_header"];
        $cont = str_replace("_ace_page_title",$page_title,$temp_text);
        $bread_crump_cont = _ace_bread_crump($bread_crump_items);
        
        $cont = str_replace("_ace_page_bread_crump",$bread_crump_cont,$cont);
        return $cont;
    }

    function utils_bread_crump_item($label, $link = "", $active_class = ""){
        return array(
            "label" => $label,
            "link" => "#" . $link,
            "active_class" => $active_class
        );
    }

    function _ace_anchor($label,$link){
        global $_ace_ts;
        $temp_text = $_ace_ts["anchor"];
        $t = str_replace("_ace_link",$link,$temp_text);
        $t = str_replace("_ace_label",$label,$t);
        return $t;
    }

    function _ace_bread_crump($items){
        global $_ace_ts;
        $temp_text = $_ace_ts["bread_crump"];
        $t = "";
        //get the looping item
        $start_flag = "_ace_[";
        $end_flag = "]_ace_";
        $start = stripos($temp_text,$start_flag);
        $end = stripos($temp_text,$end_flag);
        $len = ($end-$start);
        $looping_item_template = substr(substr($temp_text, $start, $len),strlen($start_flag));
        //tools_dumpx("looping_item_template",__FILE__,__LINE__,$looping_item_template);
        $t_children = "";
        foreach($items as $index => $bread_crump_item){
            $temp = $looping_item_template;
            $active_class = $bread_crump_item["active_class"];
            if(strlen($active_class) > 0){
                $cont = str_replace("_ace_active_class","",$temp);
                $cont = str_replace("_ace_anchor",$bread_crump_item["label"],$cont);
            }else{
                $cont = str_replace("_ace_active_class",$active_class,$temp);
                $anchor_t = _ace_anchor($bread_crump_item["label"],$bread_crump_item["link"]);
                $cont = str_replace("_ace_anchor",$anchor_t,$cont);
                //tools_dumpx("cont1",__FILE__,__LINE__,$cont);
            }
            $t_children .= $cont;
        }
        $loop_to_replace = "_ace_[" . $looping_item_template . "]_ace_";
        $t = str_replace($loop_to_replace, $t_children,$temp_text);
        return $t;
    }


    function _ace_content($content){
        global $_ace_ts;
        $temp_text = $_ace_ts["content"];
        $t = str_replace("_ace_page_content", $content,$temp_text);
        return $t;
    }

    function _ace_card($title,$body,$footer){
        global $_ace_ts;
        $temp_text = $_ace_ts["card"];
        $t = str_replace("_ace_card_title", $title,$temp_text);
        $t = str_replace("_ace_card_body", $body,$t);
        $t = str_replace("_ace_card_footer", $footer,$t);
        return $t;
    }

    function utils_file_name_only($file_name){
        $parts = explode("/",$file_name);
        return str_replace(".php","",$parts[count($parts) - 1]);
    }

    function _ace_info_box($title,$body="",$units="",$icon="cog",$info_class="bg-info",$id=""){
        global $_ace_ts;
        $temp_text = $_ace_ts["info_box"];
        $t = str_replace("_ace_box_title", $title,$temp_text);
        $t = str_replace("_ace_box_body", $body,$t);
        $t = str_replace("_ace_icon", $icon,$t);
        $t = str_replace("_ace_box_units", $units,$t);
        $t = str_replace("_ace_class", $info_class,$t);
        $t = str_replace("_ace_id", $id,$t);
        return $t;
    }

    function _ace_row($content){
        global $_ace_ts;
        $temp_text = $_ace_ts["row"];
        $t = str_replace("_ace_content", $content,$temp_text);
        return $t;
    }

    function _ace_resp_col($content){
        global $_ace_ts;
        $temp_text = $_ace_ts["resp_col"];
        $t = str_replace("_ace_content", $content,$temp_text);
        return $t;
    }

    

    function _ace_resp_cols(){
        $num = func_num_args();
        $arg_list = func_get_args();
        $t = "";
        for ($i=0; $i < $num; $i++) { 
            $arg = $arg_list[$i];
            $t .= _ace_resp_col($arg);
        }
        return $t;
    }

    function _ace_row_of_resp_cols(){
        $num = func_num_args();
        $arg_list = func_get_args();
        $t = "";
        for ($i=0; $i < $num; $i++) { 
            $arg = $arg_list[$i];
            $t .= _ace_resp_col($arg);
        }
        $t = _ace_row($t);
        return $t;
    }

    function _ace_card_solid_line_graph(){
        global $_ace_ts;
        $temp_text = $_ace_ts["card_solid_line_graph"];
        //$t = str_replace("_ace_content", $content,$temp_text);
        return $temp_text;
    }

    function _ace_col($size=12,$content=""){
        global $_ace_ts;
        $temp_text = $_ace_ts["col"];
        //col-12 col-sm-6 col-md-3
        if(!is_string($size)){
            $size= "col-" . $size; 
        }
        $t = str_replace("_ace_size", $size,$temp_text);
        $t = str_replace("_ace_content", $content,$t);
        return $t;
    }

    function _ace_col_6($content=""){
        $t = _ace_col(6,$content);
        return $t;
    }

    function _ace_col_7($content=""){
        $t = _ace_col(7,$content);
        return $t;
    }

    function _ace_col_8($content=""){
        $t = _ace_col(8,$content);
        return $t;
    }

    function _ace_col_5($content=""){
        $t = _ace_col(5,$content);
        return $t;
    }

    function _ace_card_list_item($text,$color){
        global $_ace_ts;
        $temp_text = $_ace_ts["card_list_item"];
        $t = str_replace("_ace_list_item_text", $text,$temp_text);
        $t = str_replace("_ace_color",$color,$t);
        return $t;
    }

    function _ace_card_list($title,$items){
        global $_ace_ts;
        $temp_text = $_ace_ts["card_list"];
        $t = str_replace("_ace_card_title", $title,$temp_text);
        //items
        $items_t = "";
        for ($i=0; $i < count($items); $i++) { 
            $item = $items[$i];
            $t_item = _ace_card_list_item($item["title"],$item["color"]);
            $items_t .= $t_item;
        }
        $t = str_replace("_ace_card_list_items", $items_t,$t);
        return $t;
    }

    function utils_card_list_item($title,$color="danger"){
        return array(
            "title" => $title,
            "color" => $color
        );
    };


?>