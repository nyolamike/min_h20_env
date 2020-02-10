function _id(id) {
    return $("#" + id);
}

function _hide(id) {
    return _id(id).addClass("hidden");
}

function _show(id) {
    return _id(id).removeClass("hidden");
}

_ace = {
    current_layout: "",
    current_hash_layout: "",
    go_to_layout(id) {
        _show(id);//show layout
        if (this.current_layout.length > 0) {
            _hide(this.current_layout);//hide current layout
        }
        this.current_layout = id; //set current layout
        //console.log("current_layout: ", this.current_layout);
        if (this.current_layout == "layout_") {
            //seek default behaviour
            window.location = "#" + this.bag.default_route;
            return;
        }
        this.evaluate_nav_bar();
        //nyd
        //call the init method of the layout
        window[this.current_layout].init();
    },
    evaluate_nav_bar() {
        var hash = location.hash;

        if (this.is_hash_undefined()) {
            hash = "#" + default_route;
        }

        $('#application_nav .nav-item a').each(function () {
            var that = $(this);
            //console.log((that.attr( 'href' ) == hash) ,that.attr( 'href' ), hash);
            that[that.attr('href') == hash ? 'addClass' : 'removeClass']('selected');
            //check if we need to highlight the parent
            var parent_a_id = that.attr("parent_a_id");
            if (typeof parent_a_id != 'undefined' && parent_a_id.length > 0 && that.hasClass('selected')) {
                //console.log("there is something", parent_a_id);
                window.setTimeout(function () {
                    _id(parent_a_id).addClass('selected');
                }, 100);
            } else if (typeof parent_a_id != 'undefined' && parent_a_id.length > 0) {
                _id(parent_a_id).removeClass('selected');
            }
        });
    },
    on_window_load: function (layout_to_load) {
        return layout_to_load;
    },
    on_logout: function () {
        return this.current_layout;
    },
    on_vm_logout: function () {
        window.location = "#" + this.bag.layouts[this.on_logout()].default_route;
    },
    is_hash_undefined: function () {
        var hash = location.hash;
        return (typeof hash == 'undefined' || hash == "" || hash == "/" || hash == "#/" || hash == "/#");
    },
    get_hash_layout_segment: function () {
        var hash = location.hash;
        var layout_segment = hash.split("/")[0].replace("#", "");
        return layout_segment;
    },
    get_page: function () {
        var page = "";
        for (var i = 0; i < _ace.bag.menu_items.length; i++) {
            var menu_item = _ace.bag.menu_items[i];
            //console.log("here", menu_item.hasOwnProperty("children"), menu_item.children.length > 0);
            if (menu_item.hasOwnProperty("children") && menu_item.children.length > 0) {
                for (var j = 0; j < menu_item.children.length; j++) {
                    var child_menu_item = menu_item.children[j];
                    //console.log(child_menu_item.link,location.hash);
                    if (child_menu_item.link == location.hash) {
                        page = child_menu_item.page;
                        break;
                    }
                }
            } else {
                //console.log(menu_item.link,location.hash);
                if (menu_item.link == location.hash) {
                    page = menu_item.page;
                    break;
                }
            }
        }
        return page;
    },
    init: function () {
        //listen to navigation events
        $(window).bind('hashchange', function () {
            var window_layout_segment = _ace.get_hash_layout_segment();
            console.log("Eval ", _ace.current_hash_layout, window_layout_segment);
            if (_ace.current_hash_layout != window_layout_segment) {
                //we have a layout change
                _ace.current_hash_layout = window_layout_segment;
                _ace.go_to_layout("layout_" + _ace.current_hash_layout);
            } else {
                //go to this page
                //continue from here
                var page = _ace.get_page();
                console.log("load page ", page);
                _ace.evaluate_nav_bar();
            }
        });
        //setup current current_hash_layout
        //and go to the layout
        var layout_to_load = "";
        if (this.is_hash_undefined()) {
            layout_to_load = "layout_" + default_hash_layout;
            this.current_hash_layout = default_hash_layout;
        } else {
            //split the hash #main/dashboard
            this.current_hash_layout = _ace.get_hash_layout_segment();
            layout_to_load = "layout_" + this.current_hash_layout;
        }

        this.go_to_layout(this.on_window_load(layout_to_load));
    }
};



$(function () {
    _ace.init();
})