<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper  hidden" id="main_transactions_uploads_pending">
    <!-- Content Header (Page header) -->
    <?php 
        echo _ace_page_header("Pending Uploaded Transactions",array(
            utils_bread_crump_item("Home","main/dashboard"),
            utils_bread_crump_item("Pending Uploaded Transactions","","active")
        )); 
    ?>



    <!-- Main content -->
    <?php echo _ace_content(
        _ace_card("Ace on " . utils_file_name_only(__FILE__),
        "!Start creating your awesome application.",
        "footer")
    ) ?>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
    <?php echo utils_file_name_only(__FILE__); ?> = {
        init: function () {
            //initialisation code goes here
        },
        on_close: function() {

        }
    };
</script>