(function ($) {


    $('.component-slideshow').each(function () {
        var $self = $(this);
        var rtl = $self.data('rtl') == 0;
        var time = $self.data('time');
        if ($("#slideshow .slide", $self).length < 2) return;

        $("#slideshow", $self).owlCarousel({
            items: 1,
            loop: true,
            autoplay: true,
            rtl: rtl,
            autoplayTimeout: time
        });
    });


    var iScrolls = new Array();

    $(window).on('orientationchange', function (e) {
        $('.iscroll-wrapper').each(function (i, el) {
            var width = 0;
            var $self = $(this);
            $('.nav-item', $self).each(function () {
                width += $(this).outerWidth();
            });

            $('.scroll-content', $self).width(width + 5);
            iScrolls[i].refresh()
        })
    });


    $('.iscroll-wrapper').each(function (i, el) {
        var width = 0;
        var $self = $(this);
        $('.nav-item', $self).each(function () {
            width += $(this).outerWidth();
        });

        $('.scroll-content', $self).width(width + 5);

        var iS = new IScroll(el, {eventPassthrough: true, scrollX: true, scrollY: false, preventDefault: false});

        iScrolls.push(iS);

        if (iS.maxScrollX < 0) {
            $('.more-right', $self).show()
        }

        iS.on('scrollEnd', function () {
            if (this.x <= -5) {
                $('.more-left', $self).show()
            } else {
                $('.more-left', $self).hide()
            }

            if (this.x >= this.maxScrollX + 5) {
                $('.more-right', $self).show()
            } else {
                $('.more-right', $self).hide()
            }
        });
    })

    function countTimeLeft() {

        $('.time-left').each(function () {
            var from = new Date(Date.parse($(this).data('from').replace(/-/g, "/")));
            var to = new Date(Date.parse($(this).data('to').replace(/-/g, "/")));
            var now = new Date();
            if (from <= now <= to) {
                var time_left = Math.floor((to - now) / 1000);
                var day = Math.floor(time_left / (24 * 3600));
                var hour = Math.floor((time_left - (24 * 3600) * day) / 3600);
                var min = Math.floor((time_left - (24 * 3600) * day - 3600 * hour) / 60);
                var sec = time_left - 24 * 3600 * day - 3600 * hour - 60 * min;
                //var string = '活动结束：' + day + '天' + hour + '时' + min + '分' + sec + '秒';
                if(day >=1){
                    var string = '活动结束：' + day + '天' + hour + '时';
                }else if(hour >=1){
                    var string = '活动结束：' + hour + '时' + min + '分';
                }else{
                    var string = '活动结束：' + min + '分' + sec + '秒';
                }
            }
            if (now < from) {
                var time_left = Math.floor((from - now) / 1000);
                var day = Math.floor(time_left / (24 * 3600));
                var hour = Math.floor((time_left - (24 * 3600) * day) / 3600);
                var min = Math.floor((time_left - (24 * 3600) * day - 3600 * hour) / 60);
                var sec = time_left - 24 * 3600 * day - 3600 * hour - 60 * min;
                //var string = '活动开始：' + day + '天' + hour + '时' + min + '分' + sec + '秒';
                if(day >=1){
                    var string = '活动结束：' + day + '天' + hour + '时';
                }else if(hour >=1){
                    var string = '活动结束：' + hour + '时' + min + '分';
                }else{
                    var string = '活动结束：' + min + '分' + sec + '秒';
                }
            }
            if (now > to) {
                var string = '活动已结束!';
            }

            $('span.time', this).text(string);
        })
    }

    setInterval(countTimeLeft, 1000);


    //商品橱窗切换tab

    $('.component-product').each(function () {
        var $component = $(this)
        $('.tab-list-menu li', $component).on('click', function () {
            var index = $(this).index();
            $('.tab-list-menu li', $component).removeClass('active')
            $(this).addClass('active');
            $('.tab-content', $component).addClass('hide').eq(index).removeClass('hide');
        })
    })


})(jQuery)
//# sourceMappingURL=component.js.map