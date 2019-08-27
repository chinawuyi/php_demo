var CallBack = {
    'gender':'男',
    'adduser':function(json)
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
            $.easyErrorBox('注册成功',function(){
                $.setSessionCache('ISLOGIN',true);
                if(emptycart == true)
                {
                    window.location.href = '../html/home.html';
                }
            });
            if(emptycart == false)
            {
                var products = [];
                $.getApi({
                    'products':JSON.parse($.getLocalCache('shopCart'))
                },'front/shoppingcart/adddatabatch','CallBack.adddatabatch');
            }
        }
    },
    'sendmessage':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $('#jssendcode').addClass('jssendcode2');
            $('#jssendcode').Countdown(60);
            console.log(json);
        }
    }
};
var PageRegister = {
    construct:function()
    {
        //public/nhportal/adduser?data={"callback":"bb","loginId":"test","password":"123456","tpassword":"123456","realName":"测试","gender":"男/女","mobilePhone":"138XXXXXXXX"}
        $('#jssub').click(function(){
            var jsuser = $('#jsuser').val(),
                jspass1 = $('#jspass1').val(),
                jspass2 = $('#jspass2').val(),
                jsname = $('#jsname').val(),
                jsmobile = $('#jsmobile').val();
            if(jsuser == '')
            {
                $.easyErrorBox('用户名不可为空');
                return;
            }
            if(jspass1 == '')
            {
                $.easyErrorBox('用户密码不可为空');
                return;
            }
            if(jspass1 != jspass2)
            {
                $.easyErrorBox('两次密码不一致');
                return;
            }
            if(jsname == '')
            {
                $.easyErrorBox('姓名不可为空');
                return;
            }
            if(jsmobile == '')
            {
                $.easyErrorBox('手机号码不可为空');
                return;
            }
            if (!(/^1[3|4|5|8][0-9]\d{4,8}$/.test(jsmobile)))
            {
                $.easyErrorBox('手机号码不正确');
                return;
            }
            if($('#jscode').val() == '')
            {
                $.easyErrorBox('验证码不可为空');
                return;
            }
            $.getApi({
                'loginId':jsuser,
                'password':jspass1,
                'tpassword':jspass2,
                'realName':jsname,
               // 'gender':CallBack.gender,
                'code':$('#jscode').val(),
                'mobilePhone':jsmobile
            },'public/nhportal/adduser','CallBack.adduser');
        });
    },
    //发送验证码
    sendCode:function()
    {
        $('#jssendcode').click(function(){
            if($('#jsmobile').val() == '')
            {
                $.easyErrorBox('手机号码不可为空');
                return;
            }
            //public/nhportal/sendmessage?data={"callback":"bb","phone":"182XXXXXX"}
            $.getApi({
                'phone':$('#jsmobile').val()
            },'public/nhportal/sendmessage','CallBack.sendmessage');
        });
    },
    //
    defaultEvent:function(){

        $("#allItem").find(".checkbox").click(function(){
            //alert(i);
            $("#allItem").find(".checkbox").removeClass("active");
            $(this).addClass("active");
            $("#allItem").find(".checkbox").each(function(i){
                if($(this).hasClass('active')){
                    if(i == 0)
                    {
                        CallBack.gender = '男';
                    }
                    else{
                        CallBack.gender = '女';
                    }
                }
            });
        });

    }
}