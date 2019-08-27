var CallBack = {
    'adddatabatch':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            //购物车同步成功
            window.location.href = '../home.html';

        }
    },
    'wxshoplogin':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            var emptycart = false;
            if (!$.getLocalCache('shopCart') || JSON.parse($.getLocalCache('shopCart')).length == 0)
            {
                emptycart = true;
            }
            $.easyErrorBox('登录成功',function(){
                $.setSessionCache('ISLOGIN',true);
                if(emptycart == true)
                {
                    window.location.href = './home.html';
                }
               //
            });
            if(emptycart == false)
            {
                var products = [];
                $.getApi({
                    'products':JSON.parse($.getLocalCache('shopCart'))
                },'front/shoppingcart/adddatabatch','CallBack.adddatabatch');
            }
            //front/shoppingcart/adddatabatch

        }
    }
};
var PageLogin = {
    construct : function()
    {
        $('#jssub').click(function(){
            var user = $('#jsuser').val(),
                pass = $('#jspass').val();
            if(user == '')
            {
                $.easyErrorBox('用户名不可为空');
                return;
            }
            if(pass == '')
            {
                $.easyErrorBox('密码不可为空');
                return;
            }
            $.getApi({
                'loginId':user,
                'password':pass
            },'public/nhportal/wxshoplogin','CallBack.wxshoplogin');
        });
    }
};