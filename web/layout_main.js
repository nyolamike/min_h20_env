layout_main = {
    current_page: "",
    init: function(init_page){
        console.log("init layout_main", init_page);
        this.current_page = init_page;
        this.go_to_page(init_page);
    },
    go_to_page:function(page){
        console.log("Go to page ", page);
        var next_page_ref = "layout_"+page+"_page";
        var curent_page_ref = "layout_"+page+"_page";
        var next_page_vm = window[next_page_ref];
        var current_page_vm = window[curent_page_ref];
        current_page_vm.on_close();//purge data for the current active pages
        _hide(this.current_page);//hide the current active page 
        _show(page);//show the next page
        this.current_page = page//set this page as the current page
        next_page_vm.init();//initialise the current page
    }
};