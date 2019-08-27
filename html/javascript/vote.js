var CallBack = {
    'prodId':[],
    'votelist':function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            console.log(json);
            $(json.voteproducts).each(function(){
                //<div class="item"><div class="bigPic"><img src="./images/uploadimg.jpg" alt="" class="pic"><span class="icon6"></span><div class="progress-con"><div class="progress"><div class="progress-bar"></div></div><div class="progress-txt">65%已被抢购</div></div></div><div class="other"><div class="word"><p>意大利DOLCE&amp;GABBAN香水</p></div><div class="price"><div class="top">促销价：￥</div><div class="bottom">销售价：￥</div></div></div><div class="checkbox"></div></div>
                var html = '<div class="item"><div class="bigPic"><img src="./images/uploadimg.jpg" alt="" class="pic"><div class="progress-con"><div class="progress"><div class="progress-bar" style="width:'+this.per+'%;"></div></div><div class="progress-txt">'+this.per+'%已被抢购</div></div></div><div class="other"><div class="word"><p>'+this.name+'</p></div><div class="price"><div class="top">促销价：￥?</div><div class="bottom">销售价：'+this.price+'</div></div></div><div class="checkbox" prodId="'+this.prodId+'"></div></div>';
                $('#jslist').append(html);
            });
            if(json.votedetail.length >0)
            {
                $('#jssub').addClass('active');
                $('#jslist div.checkbox').each(function(){
                    var prodId = $(this).attr('prodId');
                    var inarray = false;
                    $(json.votedetail).each(function(){
                        if(this.prodId == prodId)
                        {
                            inarray = true;
                        }
                    });
                    if(inarray == true)
                    {
                        $(this).addClass('active');
                    }
                });
                return;
            }
            $('#jslist div.checkbox').each(function(){
                $(this).click(function(){
                    var len = 0;
                    $('#jslist div.checkbox').each(function(){
                        if($(this).hasClass('active'))
                        {
                            len++;
                           // CallBack.push($(this).attr('prodId'));
                        }
                    });
                    if($(this).hasClass('active'))
                    {
                        $(this).removeClass('active');
                        CallBack.prodId=[];
                        $('#jslist div.checkbox').each(function(){
                            if($(this).hasClass('active'))
                            {
                               // len++;
                                CallBack.prodId.push($(this).attr('prodId'));
                            }
                        });
                    }
                    else{
                        if(len >=3)
                        {
                            $.easyErrorBox('最多只能选择3个');
                        }
                        else{
                            $(this).addClass('active');
                            CallBack.prodId=[];
                            $('#jslist div.checkbox').each(function(){
                                if($(this).hasClass('active'))
                                {
                                    // len++;
                                    CallBack.prodId.push($(this).attr('prodId'));
                                }
                            });
                        }
                    }

                });
            });
        }
    }
};
var PageVote = {
    construct:function(){
        if($.getLocalCache('Vote') && $.getLocalCache('Vote') == 'true')
        {
            $('#jssub').addClass('active');
        }
        $.getApi({
            'action':'获取'
        },'front/vote/vote','CallBack.votelist');

    },
    defaultEvent:function()
    {
        $('#jssub').click(function(){
            if($(this).hasClass('active'))
            {
                return;
            }
            if(CallBack.prodId.length == 0)
            {
                $.easyErrorBox('请选择您要投票的产品');
            }
            else{
                $.getApi({
                    'action':'提交',
                    'prodId':CallBack.prodId.join(',')
                },'front/vote/vote','CallBack.votelist');
            }
        });
    }
}