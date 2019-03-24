$(function() {
    initWindow();

    $(window).resize(function() {
        initWindow();
    })

    function initWindow() {
        wh = $(window).height(); //窗口高度
        ww = $(window).width(); //窗口宽度
        bh = $("#brand").height();
        $(".rightBox").height(wh - bh - 141).css({ top: bh + 121 });
        $(".rightBox").width(ww - 154);
    }
})