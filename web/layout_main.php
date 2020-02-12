<div id="layout_main" class="wrapper layout hidden">
    <!-- Navbar -->
    <?php include("top_navigation_bar.php") ?>
    <!-- /.navbar -->


    <!-- application pages -->
    <?php
        foreach ($bag["menu_items"] as $index => $item) {
            if(array_key_exists("children",$item) && count($item["children"]) > 0){
                foreach ($item["children"] as $child_index => $child_item) {
                    $page = "";
                    if(!is_string($child_item) && $child_item != "divider" ){
                        $page = $child_item["page"];
                        if(strpos($page, "main_") == 0){
                            #this is a main page
                            include("layout_" . $page . "_page.php");
                        }
                    }
                }
            }else{
                //flat menu item
                $page = $item["page"];
                if(strpos($page, "main_") == 0){
                    #this is a main page
                    include("layout_" . $page . "_page.php");
                }
            }
        }
    ?>
    <!-- /.application pages -->

    <!-- Control Sidebar 
            <aside class="control-sidebar control-sidebar-dark">
                <div class="p-3">
                <h5>Title</h5>
                <p>Sidebar content</p>
                </div>
            </aside> -->

    <!-- Main Footer -->
    <?php include("footer.php"); ?>
</div>
<!-- ./wrapper -->

