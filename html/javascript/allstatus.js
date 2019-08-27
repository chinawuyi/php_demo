var CallBack = {
    orderCancle: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.info == true)
            {
                $.easyErrorBox('订单取消成功', function() {
                    window.location.reload();
                })
            }
        }
    },
    payOrder: function(json)
    {
        $.payOrder(json);
    },
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
            else
            {
                _orderList(json,'paylist');
            }

        }

    }
};
var PageAllstatus = {
    construct: function()
    {
        $.getApi({
            'status': ''
        }, 'front/order/orderlist', 'CallBack.appendData');
    }
}