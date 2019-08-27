CallBack = {
    _appendBanner:function(obj,data)
    {
        console.log(data);
        if(data.length == 0)
        {
            return;
        }
        $(data).each(function(data) {
            //this.img
            $(obj).append('<li><a href="' + $.appendBasePath(this.url) + '"><img src="'+ $.appendBasePath('/html/images/ad.jpg') + '" class="indexBanner"></a></li>');
            //
            var products = this.products;
            var producthtml = _productListString(products);
            $('#homeproducts').append('<div class="producttitle">'+this.name+'</div><div class="productcontent class-A">'+producthtml+'</div><div class="productmore"><a href="'+this.url+'">查看更多</a></div>');
        });
        var len = $('#homebanner img').length;
        $('#homebanner img').each(function(){
            this.onload = function(){
                len -- ;
                if(len == 0)
                {
                    $('#homebanner').bxSlider({
                        controls:true,
                        auto:true
                    });
                }
            }
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
    },
    'signinBack':function(json)
    {
        console.log(json);
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {

            if(json.signinfo.isLogin == true){

                $('#check').addClass('checked').html('明天签到<span class="add">+'+json.signinfo.nextpoint+'</span>');
            }
            else{
                $('#check').removeClass('checked').html('签到<span class="add">+'+json.signinfo.point+'</span>');
                $('#check').click(function(){
                    $.getApi({
                        'action':'签到'
                    }, 'front/user/signin', 'CallBack.signinBack');
                });
            }
        }
    }
};
var PageHome = {
    construct : function()
    {
        $('.bannerclass').each(function(i){
            var dataname = $(this).attr('dataname');
            $.getApi({
                'type':dataname
            },'front/promotion/bannerclassdata','CallBack.bannerclassdata');
        });
    /*    return;
        $.getApi({
            'action':'获取'
        }, 'front/user/signin', 'CallBack.signinBack');*/
    },
    defaultEvent:function()
    {
        $('#homeproducts').on('click', '.item', function() {
            window.location.href = './prodetail.html?id=' + $(this).attr('proid');
        });


    },
 /*   search:function()
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
    }, */
/*    notice:function()
    {
        $('#homenotice .noticebg,#homenotice .close').click(function(){
            $('#homenotice').addClass('pub_hidden');
        });
        window.setTimeout(function(){
            $('#homenotice').addClass('pub_hidden');
        },10000);
    }*/
}