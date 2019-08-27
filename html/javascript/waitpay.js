var CallBack = {
    orderCancle:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            if(json.info == true)
            {
                $.easyErrorBox('订单取消成功',function(){
                    window.location.reload();
                })
            }
        }
    },
    payOrder:function(json)
    {
        $.payOrder(json);
        
    },
    appendData:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            if(json.orders.length == 0)
            {
                $('#jsnocoomment').removeClass('pub_hidden');

            }
            _orderList(json,'paylist')
        }
    }
};
var PageWaitpay = {
    construct:function()
    {
        $.getApi({
            'status': '待付款'
        },'front/order/orderlist','CallBack.appendData');
    }
}