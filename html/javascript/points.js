var CallBack = {
    'pointlist':function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            console.log(json);
            $('#jsmypoint').append(json.userpoint.point);
            //<li><div class="mainLine"><div class="left"><span>购买产品</span></div><div class="middle"><span class="red">+2</span></div><div class="right"><span>2016.02.25 10:00:05</span></div><div class="tri"><img src="./images/fr_other.png"alt=""></div></div><div class="hideLine"><span>订单号E2016030201的订单购买成功，获得5积分</span></div></li>
            $(json.signinfo).each(function(){
                var point = '';
                if(this.point.indexOf('-') == '0')
                {
                    point = '<span >'+this.point+'</span>';
                }
                else{
                    point = '<span class="red">+'+this.point+'</span>';
                }
                $('#records').append('<li><div class="mainLine"><div class="left"><span>'+this.type+'</span></div><div class="middle">'+point+'</div><div class="right"><span>'+this.createdatetime+'</span></div><div class="tri"><img src="./images/fr_other.png"alt=""></div></div><div class="hideLine"><span>'+this.desc+'</span></div></li>');

            });

        }
    }
};
var PagePoints = {
    construct : function()
    {
        $.getApi({
        },'front/user/pointlist','CallBack.pointlist');

        $('#records').on('click','li',function(){
            if($(this).hasClass('active')){
                $(this).find('.hideLine').slideUp(200);
                $(this).removeClass('active');
            }
            else{
                $('#records li').removeClass('active').find('.hideLine').slideUp(200);
                $(this).find('.hideLine').slideDown(200);
                $(this).addClass('active');
            }
        });
    }
}