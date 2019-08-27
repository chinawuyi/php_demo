var CallBack = {
    orderReceive: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.info == true)
            {
                $.easyErrorBox('提交成功', function() {
                    window.location.reload();
                })
            }
        }
    },
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
            _orderList(json,'paylist')
        }

    }
};
var PageWaitget = {
    construct: function()
    {
        $.getApi({
            'status': '待收货'
        }, 'front/order/orderlist', 'CallBack.appendData');
    }
}