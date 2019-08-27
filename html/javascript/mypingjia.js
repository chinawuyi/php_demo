var CallBack = {
    appendData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.orders.length == 0)
            {
                $('#jsnocoomment').removeClass('pub_hidden');
            }
            $(json.orders).each(function() {
                $('#paylist').append(_dispListOrder(this));
            });
            $('.jsmypingjia').each(function() {
                $(this).click(function(e) {
                    e.stopPropagation();
                    var orderno = $(this).attr('ono');
                    window.location.href = "myevaluate.html?orderid=" + orderno;
                });
            });
            $('#paylist li').each(function() {
                $(this).click(function() {
                    window.location.href = 'orderdetail.html?orderid=' + $(this).attr('orderId');
                });
            });
        }
    }
};
var PageMypingjia = {
    construct: function()
    {
        $.getApi({
            'status': '已评价'
        }, 'front/order/orderlist', 'CallBack.appendData');
    }
}