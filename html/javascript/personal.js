var CallBack = {
    appendData:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            console.log(json);
            $('#cartNums').text(json.user.cartNums);
            $('#favoriteNums').text(json.user.favoriteNums);
            $('#preferenceNums').text(json.user.preferenceNums);
            $('#jsusername').append(json.user.userName+'的信息');
            $('#integralno').text(json.user.point);
            if(json.user.waitPayNums == '0')
            {
                $('#waitPayNums').addClass('pub_hidden');
            }
            else
            {
                $('#waitPayNums').text(json.user.waitPayNums);
            }
            //
            if(json.user.waitSendNums == '0')
            {
                $('#waitSendNums').addClass('pub_hidden');
            }
            else
            {
                $('#waitSendNums').text(json.user.waitSendNums);
            }
            //
            if(json.user.waitAcceptNums == '0')
            {
                $('#waitAcceptNums').addClass('pub_hidden');
            }
            else
            {
                $('#waitAcceptNums').text(json.user.waitAcceptNums);
            }
            //
            if(json.user.waitComNums == '0')
            {
                $('#waitComNums').addClass('pub_hidden');
            }
            else
            {
                $('#waitComNums').text(json.user.waitComNums);
            }
            //
            if(json.user.afterSaleNums == '0')
            {
                $('#afterSaleNums').addClass('pub_hidden');
            }
            else
            {
                $('#afterSaleNums').text(json.user.afterSaleNums);
            }

        }
    },
    'logout':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $.deleteSessionCache('ISLOGIN');
            $.easyErrorBox('退出成功',function(){
                window.location.href = 'login.html';
            });
        }
    }
}
var PagePersonal = {
    construct:function()
    {
        //front/user/detaildata
        $.getApi({
        },'front/user/mydata','CallBack.appendData');
    },
    defaultEvent:function()
    {
        $('#jsshoppingcart').click(function(){
            if(!$.getSessionCache('ISLOGIN') || $.getSessionCache('ISLOGIN') == 'false')
            {
                window.location.href = 'login.html';
                return false;
            }
            window.location.href = 'shoppingcart.html';
        });
        $('#jscoupon').click(function(){
            window.location.href = 'coupons.html';
        });
        $('#jsaddress').click(function(){
            window.location.href = 'myaddress.html';
        });
        $('#jsmyfavourite').click(function(){
            window.location.href = 'collection.html';
        });
        $('#waitpay').click(function(){
            window.location.href = 'waitpay.html';
        });
        $('#waitsend').click(function(){
            window.location.href = 'waitsend.html';
        });
        $('#waitget').click(function(){
            window.location.href = 'waitget.html';
        });
        $('#waitreply').click(function(){
            window.location.href = 'waitpingjia.html';
        });
        $('#waittuikuan').click(function(){
            window.location.href = 'aftersell.html';
        });
        $('#jsalllist').click(function(){
            window.location.href = 'allstatus.html';
        });
        $('#jsmypingjia').click(function(){
            window.location.href = 'mypingjia.html';
        });
        $('#jsdiscount').click(function(){
            window.location.href = 'bargainlist.html';
        });
        $('#jsintegral').click(function(){
            window.location.href = 'points.html';
        });
    },
    quit:function()
    {
        $('#jsquit').click(function(){
            //front/user/logout
            $.getApi({
            },'front/user/logout','CallBack.logout');
        });
    }
}