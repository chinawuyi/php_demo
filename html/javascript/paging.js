$.extend($,{
    AJAXSCROLL : false,
    paging:function(fun){
        var _this=this;
        $(window).scroll(function(){
            var scrollTop = $(this).scrollTop(),
                scrollHeight = $(document).height(),
                windowHeight = $(this).height();
            if(scrollTop + windowHeight >= scrollHeight-20){
                if(_this.AJAXSCROLL == false)
                {
                    if($.isFunction(fun))
                    {
                        fun();
                    }
                }
                _this.AJAXSCROLL = true;
            }
        });
    }
});
