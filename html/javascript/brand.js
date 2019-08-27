CallBack = {
    _appendBanner:function(obj,data)
    {
        if(data.length == 0)
        {
            return;
        }
        $(data).each(function(data) {
            $(obj).append('<li><a href="' + $.appendBasePath(this.url) + '"><img src="'+ $.appendBasePath(this.img) + '" class="indexBanner"></a></li>');
        });

    },
    bannerclassdata:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            var type = json.type,_this = this;
            $('.bannerclass').each(function(){
                if($(this).attr('dataname') == type)
                {
                    _this._appendBanner(this,json.banner);
                }
            });

        }
    }
};
var PageBrand = {
    defaultEvent:function()
    {
        $('.bannerclass').each(function(i){
            var dataname = $(this).attr('dataname');
            $.getApi({
                'type':dataname
            },'front/promotion/bannerclassdata','CallBack.bannerclassdata');
        });
    },
    search:function()
    {
        $('#jssearch').click(function(){
            var jssearchtext = $('#jssearchtext').val();
            if(jssearchtext == '')
            {
                $.easyErrorBox('请输入查询的字符串');
                return;
            }
            else{
                window.location.href = 'prolist2.html?search='+jssearchtext;
            }
        });
    }
}