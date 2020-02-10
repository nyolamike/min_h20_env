<?php
    function utils_nbsp($count=1){
        $space = "";
        for($i=0;$i<$count;$i++){
            $space .= "&nbsp;";
        }
        return $space;
    }

    function utils_menu_item($label,$link,$children=array(),$page=""){
        if(strlen($link) > 0){
            // we need default page pages
            // remove the layout
            $page = str_replace("/","_",$link);
        }
        return array(
            "label" => $label,
            "link" => "#" . $link,
            "children" => $children,
            "page" => $page
        );
    }
?>