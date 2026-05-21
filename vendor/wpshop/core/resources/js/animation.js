jQuery(document).ready(function($) {

    function wpshop_animation_on_scroll( elements ) {

        if (0 !== elements.length) {
            var n = $(window).scrollTop();
            var i = n + $(window).height();

            if ($("body").is(":animated")) {
                console.log('animated');
                for (s = 0; s < elements.length; s++) {
                    if ( elements[s].curTopOffset <= n ) {
                        console.log('body anim');
                        $(elements[s]).removeClass("w-animate w-animate_no-hover");
                    }
                }
            }
            wpshop_animation_animate_elements( elements, i );

            //t_animate__animateGroups(t, i), t_animate__animateChainsBlocks(a, i), t_animate__animateElems(e, i)
        }

    }
    function wpshop_animation_start() {

        var elements = $(".w-animate");

        function n() {
            wpshop_animation_get_elements_offsets(elements);
        }

        wpshop_animation_set_state(elements);

        elements = elements.filter(".w-animate--wait");

        n();

        $(window).bind("resize", wpshop_timer(n, 200));

        $(window).bind("scroll", wpshop_timer(function() {
            wpshop_animation_on_scroll( elements );
        }, 200));

    }
    function wpshop_animation_set_state(elements) {
        var scrolltop = $(window).scrollTop(),
            scrollwithheight = scrolltop + $(window).height();

        elements.each(function() {
            var t = $(this),
                offsettop = t.offset().top;

            if (offsettop < scrolltop - 450) {
                t.removeClass("w-animate w-animate_no-hover");
                //t.is('[data-animate-style="animatednumber"]');
                //t_animate__animateNumbers(t);
            }

            var e = wpshop_animation_detect_trigger_offset(t, scrollwithheight);

            wpshop_animation_set_animation_settings(t, offsettop, scrollwithheight);

            if ( offsettop < e ) {
                //t_animate__removeNoHoverClassFromBtns(t);

                t.addClass("w-animate--started");

                if ( t.is('[data-animate-style="animatednumber"]') ) {
                    //t_animate__animateNumbers(t);
                }
            }

            if ( offsettop >= e ) {
                t.addClass("w-animate--wait");
            }
        });

    }
    function wpshop_animation_animate_elements(t, a) {
        if (t.length)
            for (var e = 0; e < t.length; e++) {
                var n = wpshop_animation_detect_trigger_offset($(t[e]), a);
                if ( t[e].curTopOffset < n ) {
                    $(t[e]).removeClass("w-animate--wait");
                    $(t[e]).addClass("w-animate--started");
                }
                // $(t[e]).is('[data-animate-style="animatednumber"]') && t_animate__animateNumbers($(t[e])), t.splice(e, 1), e--)
            }
    }
    function wpshop_animation_set_animation_settings(element, offsettop, scrollwithheight) {
        var style = element.attr("data-animate-style"),
            distance = element.attr("data-animate-distance");

        if ( 0 !== distance && "" !== distance ) {
            if ( typeof distance === 'undefined' ) distance = '40';
            distance = distance.replace("px", "");
            element.css({
                "transition-duration": "0s",
                "transition-delay": "0s"
            });
            if ("fadeinup" === style) {
                element.css("transform", "translate3d(0," + distance + "px,0)");
            }
            if ("fadeindown" === style) {
                element.css("transform", "translate3d(0,-" + distance + "px,0)");
            }
            if ("fadeinleft" === style) {
                element.css("transform", "translate3d(" + distance + "px,0,0)");
            }
            if ("fadeinright" === style) {
                element.css("transform", "translate3d(-" + distance + "px,0,0)");
            }

            wpshop_animation_force_in_viewport_repaint(element, offsettop, scrollwithheight);
            element.css({
                "transition-duration": "",
                "transition-delay": ""
            });
        }


        var scale = element.attr("data-animate-scale");
        if ( 0 !== scale && "" !== scale ) {
            element.css({
                "transition-duration": "0s",
                "transition-delay": "0s"
            });
            element.css("transform", "scale(" + scale + ")");
            wpshop_animation_force_in_viewport_repaint(element, offsettop, scrollwithheight);
            element.css({
                "transition-duration": "",
                "transition-delay": ""
            });
        }


        var delay = element.attr("data-animate-delay");
        if ( 0 !== delay && "" !== delay ) {
            if ( typeof delay === 'undefined' ) delay = Math.random() / 4;
            element.css("transition-delay", delay + "s");
        }


        var duration = element.attr("data-animate-duration");
        if ( typeof duration === 'undefined' ) duration = '1.5';
        if ( 0 !== duration && "" !== duration ) {
            element.css("transition-duration", duration + "s");
        }
    }

    function wpshop_animation_force_in_viewport_repaint(element, offsettop, scrollwithheight) {
        if ( offsettop < scrollwithheight + 450 ) {
            return element[0].offsetHeight;
        }
    }
    function wpshop_animation_detect_trigger_offset(element, scrollwithheight) {
        var offset = element.attr("data-animate-trigger-offset"),
            n = scrollwithheight;
        if ( typeof offset === 'undefined' ) offset = (element.innerHeight() / 3).toString();
        return void 0 !== offset && "" !== offset && (n = scrollwithheight - 1 * (offset = offset.replace("px", ""))), n;
    }
    function wpshop_animation_init() {
        if (
            ! wpshop_animation_check_ie() &&
            $(window).width() >= 980 &&
            ! window.isSearchBot &&
            ! window.isMobile && $(window).width() >= 980
        ) {
            setTimeout(function() {
                wpshop_animation_start();
            }, 300);
        } else {
            $(".w-animate").removeClass("w-animate");
        }
    }
    function wpshop_animation_get_elements_offsets(t) {
        for (var a = 0; a < t.length; a++) {
            t[a].curTopOffset = $(t[a]).offset().top;
        }
    }
    function wpshop_animation_check_ie() {
        var t = window.navigator.userAgent,
            a = t.indexOf("MSIE"),
            e = "",
            n = !1;
        return a > 0 && (8 !== (e = parseInt(t.substring(a + 5, t.indexOf(".", a)))) && 9 !== e || (n = !0)), n;
    }
    function wpshop_timer(func, delay, obj) {
        var i, n;
        return delay || (delay = 250),
            function() {
                var r = obj || this;
                var a = +new Date();
                var s = arguments;

                if ( i && a < i + delay ) {
                    clearTimeout(n);
                    n = setTimeout(function() {
                        i = a;
                        func.apply(r, s);
                    }, delay);
                } else {
                    i = a;
                    func.apply(r, s);
                }
            };
    }
    wpshop_animation_init();
});