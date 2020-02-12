<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper hidden" id="main_dashboard">
    <?php 
        $pgid = "dashboard_";
        $clients_count_id = $pgid."clients_count";
        $meters_count_id = $pgid."meters_count";
        $meters_disconnected_id = $pgid."meters_disconnected";
        $consumed_id =  $pgid."consumed_id";
    ?>

    <!-- Content Header (Page header) -->
    <?php 
        echo _ace_page_header("Dashboard",array(
            utils_bread_crump_item("Home","main/dashboard"),
            utils_bread_crump_item("Dashboard","","active")
        )); 
    ?>



    <!-- Main content -->
    <?php 
    $stats = _ace_row_of_resp_cols(
        _ace_info_box("NO. Clients","3,507,000","","users","bg-info",$clients_count_id),
        _ace_info_box("NO. Meters","3,960,050","","tachometer-alt","bg-warning",$meters_count_id),
        _ace_info_box("Avg. Reading","25.57","units","tint","bg-success",$consumed_id),
        _ace_info_box("NO. Disconnected Meters","6,706","","trash-alt","bg-danger",$meters_disconnected_id)
    );

    $graph_row = _ace_row(
        _ace_col_7(_ace_card_solid_line_graph())
        ._ace_col_5(_ace_card_list("Alert &nbsp;&nbsp;<small>7 items need attention</small>",array(
            utils_card_list_item("45 New pending uploaded users"),
            utils_card_list_item("Auto Billing is turned off currently","info"),
            utils_card_list_item("7 Clients dont have any meters",),
            utils_card_list_item("05 Messages left, please replenish"),
            utils_card_list_item("38 Meters have no readings","warning"),
            utils_card_list_item("457 New pending meter reading, need comfirmation"),
            utils_card_list_item("121 Pending Uploaded transactions are waiting approval")
        )))
    );

    //"Hi Ganda, <br/> your framework is Awesome"



    // $card = _ace_card("Ace on " . utils_file_name_only(__FILE__),
    // "!Start creating your awesome application.",
    // "footer");
    echo _ace_content($stats.$graph_row); 
    ?>
    <!-- /.content -->

    



</div>
<!-- /.content-wrapper -->

<script>
layout_main_dashboard_page = {
    clients_count_id: "<?php echo $clients_count_id; ?>",
    meters_count_id: "<?php echo $meters_count_id; ?>",
    meters_disconnected_id: "<?php echo $meters_disconnected_id; ?>",
    init: function() {
        //initialisation code goes here
        this.helper();
    },
    on_close: function() {

    },
    helper() {
        // Sales graph chart
        var salesGraphChartCanvas = $('#line-chart').get(0).getContext('2d');
        //$('#revenue-chart').get(0).getContext('2d');

        var salesGraphChartData = {
            labels: ['2019 Mch', '2019 Apr', '2019 May',  '2019 Jun', '2019 Jul', '2019 Aug', '2019 Sep', '2019 Oct', '2019 Nov', '2019 Dec',
                '2020 Jan', '2020 Feb'
            ],
            datasets: [{
                label: 'Domestic Meters',
                fill: false,
                borderWidth: 1.5,
                lineTension: 0.5,
                spanGaps: true,
                borderColor: '#efefef',
                pointRadius: 3,
                pointHoverRadius: 7,
                pointColor: '#efefef',
                pointBackgroundColor: '#efefef',
                data: [2666, 2778, 4912, 3767, 6810, 5670, 4820, 15073, 10687, 8432, 5676,3278]
            },{
                label: 'Commercial Meters',
                fill: false,
                borderWidth: 1.5,
                lineTension: 0.5,
                spanGaps: true,
                borderColor: '#ffc107',
                pointRadius: 3,
                pointHoverRadius: 7,
                pointColor: '#ffc107',
                pointBackgroundColor: '#ffc107',
                data: [4912,  15073, 10687, 2666, 2778, 3321,3278, 8432, 5676, 3767, 6810, 5670, 4820]
            },{
                label: 'Institution Meters',
                fill: false,
                borderWidth: 1.5,
                lineTension: 0.5,
                spanGaps: true,
                borderColor: '#fd7e14',
                pointRadius: 3,
                pointHoverRadius: 7,
                pointColor: '#fd7e14',
                pointBackgroundColor: '#fd7e14',
                data: [15073, 2666, 2778 ,15073, 10687,  5670, 4820,10687, 5676, 3767, 6810, 4912]
            }]
        }

        var salesGraphChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: false,
            },
            scales: {
                xAxes: [{
                    ticks: {
                        fontColor: '#efefef',
                    },
                    gridLines: {
                        display: false,
                        color: '#efefef',
                        drawBorder: false,
                    }
                }],
                yAxes: [{
                    ticks: {
                        stepSize: 5000,
                        fontColor: '#efefef',
                    },
                    gridLines: {
                        display: true,
                        color: '#efefef',
                        drawBorder: false,
                    }
                }]
            }
        }

        // This will get the first returned node in the jQuery collection.
        var salesGraphChart = new Chart(salesGraphChartCanvas, {
            type: 'line',
            data: salesGraphChartData,
            options: salesGraphChartOptions
        })
    }
};
</script>