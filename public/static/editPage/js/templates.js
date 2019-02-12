jQuery(document).ready(function () {
    $('.preview-button').fancybox({
        width: 800,
        height: 730

    });
    $('.theme-groups-menu').pin({
        containerSelector: '#main-wrapper'
    });


    $('.group-menu').click(function () {
        var id = $(this).data('id');
        var top = $(id).offset().top;
        $(window).scrollTop(top - 80);
    })
    $(window).on('scroll', function () {
        var scrollTop = $(window).scrollTop();
        var i = Math.floor((scrollTop - 100) / 1180); //todo 1180
        i = i > 0 ? i : 0;
        $('.group-menu').removeClass('active').eq(i).addClass('active')
    })
});
//# sourceMappingURL=templates.js.map