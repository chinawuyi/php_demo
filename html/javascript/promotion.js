CallBack = {
    _appendBanner:function(obj,data)
    {
        if(data.length == 0)
        {
            return;
        }
        $(data).each(function(data) {
            var imgsrc = '';
            if(this.img.indexOf('http:')>=0)
            {
                imgsrc = this.img;
            }
            else{
                imgsrc = UPLOADIMG+this.img;
            }
            $(obj).append('<a href="' + this.url + '"><img src="'+imgsrc + '" class="indexBanner"></a>');
        });
        if($(obj).hasClass('loopbanner'))
        {
            $(obj).bxSlider({
                controls: false,
                auto: true
            });
        }


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
    },
    labelclassdata:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $('.labelclass').each(function(){
                var dataname = $(this).attr('dataname');
                if(dataname == json.dataname)
                {
                    _productList(json,this);
                }
            });


        }
    },
    listclassdata:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $('.listclass').each(function(){
                var dataname = $(this).attr('dataname');
                if(dataname == json.dataname)
                {
                    _productList(json,this);
                }
            });


        }
    }
};
var PagePromotion = {
    defaultEvent:function()
    {
        $(document).on('click', '.list-item  div.item', function() {
            window.location.href = '../prodetail.html?id=' + $(this).attr('proid');
        });


        $('.bannerclass').each(function(i){
            var dataname = $(this).attr('dataname');
            $.getApi({
                'type':dataname
            },'front/promotion/bannerclassdata','CallBack.bannerclassdata');
        });

        $('.labelclass').each(function(){
            var dataname = $(this).attr('dataname');
            $.getApi({
                'dataname':dataname
            },'front/proa/listdata','CallBack.labelclassdata');
        });

        $('.listclass').each(function(){
            var dataname = $(this).attr('dataname');
            $.getApi({
                'dataname':dataname
            },'front/proa/listdata','CallBack.listclassdata');
        });
    }
};
$(document).ready(function(){
    PagePromotion.defaultEvent();
});