function checkBilled(){for(var e=$("#cb_billed_type"),i=$(".for-month"),s=$(".for-year"),a=0;a<i.length;a++)e.is(":checked")?(s.eq(a).addClass("display-year"),i.eq(a).removeClass("display-month")):(s.eq(a).removeClass("display-year"),i.eq(a).addClass("display-month"))}!function(e){e(window).on("load",function(){e("#preloader-active").fadeOut("slow")});var i,s,a,n=e(".sticky-bar"),t=e(window);(t.on("scroll",function(){t.scrollTop()<200?(n.removeClass("stick"),e(".header-style-2 .categories-dropdown-active-large").removeClass("open"),e(".header-style-2 .categories-button-active").removeClass("open")):n.addClass("stick")}),e.scrollUp({scrollText:'<i class="fi-rr-arrow-small-up"></i>',easingType:"linear",scrollSpeed:900,animation:"fade"}),(new WOW).init(),e(".sticky-sidebar").length&&e(".sticky-sidebar").theiaStickySidebar(),e(".categories-button-active").length)&&e(".categories-button-active").on("click",function(i){i.preventDefault(),e(this).hasClass("open")?(e(this).removeClass("open"),e(this).siblings(".categories-dropdown-active-large").removeClass("open")):(e(this).addClass("open"),e(this).siblings(".categories-dropdown-active-large").addClass("open"))});e(".select-active").length&&e(".select-active").select2(),e(".count").length&&e(".count").counterUp({delay:10,time:2e3}),e(".grid").length&&e(".grid").imagesLoaded(function(){e(".grid").isotope({itemSelector:".grid-item",percentPosition:!0,layoutMode:"masonry",masonry:{columnWidth:".grid-item"}})}),i=e(".search-active"),s=e(".search-close"),a=e(".main-search-active"),i.on("click",function(e){e.preventDefault(),a.addClass("search-visible")}),s.on("click",function(){a.removeClass("search-visible")}),function(){var i=e(".burger-icon"),s=e(".mobile-menu-close"),a=e(".mobile-header-active"),n=e("body");n.prepend('<div class="body-overlay-1"></div>'),i.on("click",function(e){i.toggleClass("burger-close"),e.preventDefault(),a.toggleClass("sidebar-visible"),n.toggleClass("mobile-menu-active")}),s.on("click",function(){a.removeClass("sidebar-visible"),n.removeClass("mobile-menu-active")}),e(".body-overlay-1").on("click",function(){a.removeClass("sidebar-visible"),n.removeClass("mobile-menu-active"),i.removeClass("burger-close")})}();var o=e(".mobile-menu"),l=o.find(".sub-menu");l.parent().prepend('<span class="menu-expand"><i class="fi-rr-angle-small-down"></i></span>'),l.slideUp(),o.on("click","li a, li .menu-expand",function(i){var s=e(this);s.parent().attr("class").match(/\b(menu-item-has-children|has-children|has-sub-menu)\b/)&&("#"===s.attr("href")||s.hasClass("menu-expand"))&&(i.preventDefault(),s.siblings("ul:visible").length?(s.parent("li").removeClass("active"),s.siblings("ul").slideUp()):(s.parent("li").addClass("active"),s.closest("li").siblings("li").removeClass("active").find("li").removeClass("active"),s.closest("li").siblings("li").find("ul:visible").slideUp(),s.siblings("ul").slideDown()))}),e(".mobile-language-active").on("click",function(i){i.preventDefault(),e(".lang-dropdown-active").slideToggle(900)}),e(".categories-button-active-2").on("click",function(i){i.preventDefault(),e(".categori-dropdown-active-small").slideToggle(900)});var r=e(".tm-demo-options-wrapper");e(".view-demo-btn-active").on("click",function(e){e.preventDefault(),r.toggleClass("demo-open")}),e(".more_slide_open").slideUp(),e(".more_categories").on("click",function(){e(this).toggleClass("show"),e(".more_slide_open").slideToggle()}),e(".swiper-group-6").each(function(){new Swiper(this,{spaceBetween:30,slidesPerView:6,spaceBetween:30,slidesPerGroup:2,loop:!0,navigation:{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"},autoplay:{delay:1e4},breakpoints:{1199:{slidesPerView:6},800:{slidesPerView:4},400:{slidesPerView:2},350:{slidesPerView:2,slidesPerGroup:1,spaceBetween:15}}})}),
e(".swiper-group-3").each(function () {
    new Swiper(this, {
        spaceBetween: 30,
        slidesPerView: 4,
        slidesPerGroup: 1,
        loop: true,

        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        },

        pagination: {
            el: ".swiper-pagination3",
            clickable: true,
            type: "custom",
            renderCustom: function (swiper, current, total) {
                var html = "";

                for (var i = 0; i < total; i++) {
                    html += i === current - 1
                        ? '<span class="swiper-pagination-customs swiper-pagination-customs-active"></span>'
                        : '<span class="swiper-pagination-customs"></span>';
                }

                return html;
            }
        },

        autoplay: {
            delay: 10000,
            disableOnInteraction: false
        },

        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 3
            },
            1400: {
                slidesPerView: 4
            }
        }
    });
}),e(".swiper-group-2").each(function(){new Swiper(this,{spaceBetween:30,slidesPerView:2,spaceBetween:30,slidesPerGroup:1,loop:!0,navigation:{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"},pagination:{el:".swiper-pagination",type:"custom",renderCustom:function(e,i,s){for(var a="",n=0;n<s;n++)a+=n==i-1?'<span class="swiper-pagination-customs swiper-pagination-customs-active"></span>':'<span class="swiper-pagination-customs"></span>';return a}},autoplay:{delay:1e4},breakpoints:{1199:{slidesPerView:2},800:{slidesPerView:1},600:{slidesPerView:1},400:{slidesPerView:1},350:{slidesPerView:1}}})}),e(".dropdown-menu li a").on("click",function(i){/*i.preventDefault(),*/e(this).parents(".dropdown").find(".btn span").html(e(this).text()+' <span class="caret"></span>'),e(this).parents(".dropdown").find(".btn").val(e(this).data("value"))}),e(".list-tags-job .remove-tags-job").on("click",function(i){i.preventDefault(),e(this).closest(".job-tag").remove()}),e(".popup-youtube").length&&e(".popup-youtube").magnificPopup({type:"iframe",mainClass:"mfp-fade",removalDelay:160,preloader:!1,fixedContentPos:!1}),checkBilled()}(jQuery);const ps=new PerfectScrollbar(".mobile-header-wrapper-inner");