<?php

    include("utils.php");

    //load templates
   
    $_ace_ts = utils_load_templates(array(
        "page_header",
        "layout_page",
        "bread_crump",
        "anchor",
        "content",
        "card",
        "info_box",
        "row",
        "resp_col",
        "card_solid_line_graph",
        "col",
        "card_list",
        "card_list_item"
    ));


    $bag = array(
        "title" => "Umbrella - Ministry Of Water And Environment",
        "title-favicon" => "images/logo.jpeg",
        "footer-right-label" => "Logged In As: Nyola Mike | <b>System Admin</b>",
        "footer-company-name" => "Umbrella",
        "footer-powered-by" => "Ministry Of Water And Environment",
        "footer-powered-by-link" => "https://www.mwe.go.ug/",
        "navbar-logo-link" => "",
        "navbar-logo" => "images/logo.jpeg",
        "navbar-logo-alt" => "",
        "navbar-company" => "Umbrella",
        "menu_items" => array(
            utils_menu_item("Dashboard","main/dashboard"),
            utils_menu_item("Transactions","",array(
                utils_menu_item("Transactions History","main/transactions/history"),
                utils_menu_item("Pending Uploaded Transactions","main/transactions/uploads/pending"),
                utils_menu_item("Reconciliations","main/transactions/uploads/form"),
                "divider",
                utils_menu_item("Reversals","main/transactions/reversals/list"),
                utils_menu_item("Reversal Form","main/transactions/reversals/form"),
                utils_menu_item("Failed Payments","main/transactions/failed")
            )),
            // utils_menu_item("Billings","",array(
            //     utils_menu_item("Billings History","main/billings/history"),
            //     utils_menu_item("Manual Billing","main/billings/manual"),
            //     utils_menu_item("Billing Period","main/billings/periods")
            // )),
            // utils_menu_item("Readings","",array(
            //     utils_menu_item("Readings History","main/readings/history"),
            //     utils_menu_item("Accept Taken Readings","main/readings/accept"),
            //     utils_menu_item("Pending Uploaded Readings","main/readings/uploads/pending"),
            //     utils_menu_item("Upload Readings","main/readings/uploads/form")
            // )),
            // utils_menu_item("Clients","",array(
            //     utils_menu_item("Clients List","main/clients/list"),
            //     utils_menu_item("Registration Form","main/clients/register"),
            //     utils_menu_item("Pending Uploaded Clients","main/clients/upload/pending"),
            //     utils_menu_item("Upload New Clients","main/clients/upload/form"),
            //     "divider",
            //     utils_menu_item("Meters List","main/meters/list"),
            //     utils_menu_item("Meters Map","main/meters/map")
            // )),
            // utils_menu_item("Messaging","",array(
            //     utils_menu_item("Sent SMS","main/messaging/list"),
            //     utils_menu_item("SMS Form","main/messaging/form"),
            //     utils_menu_item("Chat","main/messaging/chat"),
            //     utils_menu_item("Auto SMS","main/messaging/autosetup"),
            // )),
            // utils_menu_item("Reports","",array(
            //     utils_menu_item("Repayments Report","main/reports/repayments")
            // )),
            // utils_menu_item("Security","",array(
            //     utils_menu_item("System Users","main/security/users"),
            //     utils_menu_item("Delagations","main/security/delagations"),
            //     utils_menu_item("Permissions","main/security/permissions"),
            //     utils_menu_item("Roles","main/security/permissions"),
            // )),
            // utils_menu_item("Settings","",array(
            //     utils_menu_item("Charges","main/settings/charges"),
            //     utils_menu_item("Tarrifs and Tiers","main/settings/tarrifs"),
            //     utils_menu_item("Meter Status","main/settings/meters/ranges"),
            //     utils_menu_item("Geo Locations","main/settings/locations"),
            //     utils_menu_item("Organisation","main/settings/organisations"),
            // ))
        ),
        "layouts" => array(
            "layout_main" => array(
                "default_route" => "main/dashboard",
                "pages" => array(

                )
            ),
            "layout_login" => array(
                "default_route" => "login/",
                "pages" => array(
                    
                )
            )
        ),
        "default_hash_layout" => "main",
        "default_route" => "main/dashboard",
        "on_window_load" => "on_window_load.js",
        "on_logout" => "on_logout.js"
    );

    
    include("layout.php");
    
?>