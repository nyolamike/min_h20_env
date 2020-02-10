<!DOCTYPE html>
<html lang="en">
<?php
        include("header.php");
    ?>

<body class="hold-transition layout-top-nav layout-footer-fixed layout-navbar-fixed">
    <?php
        foreach ($bag["layouts"] as $layout_file_name => $config) {
            include($layout_file_name . ".php");
        }
    ?>

    <!-- REQUIRED SCRIPTS -->
    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Application framework js -->
    <script>
        default_hash_layout = "<?php echo $bag['default_hash_layout']; ?>";
        default_route = "<?php echo $bag['default_route']; ?>";
    </script>
    <script src="dist/js/_ace.js"></script>
    <?php
        //include the on window load if one has been setup
        if(array_key_exists("on_window_load",$bag)){
            echo '<script src="dist/js/on_window_load.js"></script>';
        }
        //include the onlogout if one has been setup
        if(array_key_exists("on_logout",$bag)){
            echo '<script src="dist/js/on_logout.js"></script>';
        } 
    ?>

    <?php
        //the layout javascript files
        foreach ($bag["layouts"] as $layout_file_name => $config) {
            echo '<script src="'.$layout_file_name.'.js"></script>';
        }
    ?>

    

    

    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>

    <?php
        //frameworks bag
        echo "<script>_ace.bag = JSON.parse('". json_encode($bag) . "');</script>";
    ?>
    
</body>

</html>