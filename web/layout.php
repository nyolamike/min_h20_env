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
    <!-- jQuery UI 1.11.4 -->
    <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
    $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- ChartJS -->
    <script src="plugins/chart.js/Chart.min.js"></script>
    <!-- Sparkline -->
    <script src="plugins/sparklines/sparkline.js"></script>
    <!-- JQVMap -->
    <script src="plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Summernote -->
    <script src="plugins/summernote/summernote-bs4.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

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
    <script src="dist/js/adminlte.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <!-- <script src="dist/js/pages/dashboard.js"></script> -->

    <?php
        //frameworks bag
        echo "<script>_ace.bag = JSON.parse('". json_encode($bag) . "');</script>";
    ?>
    
</body>

</html>