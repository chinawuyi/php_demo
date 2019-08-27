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
            _orderList(json,'paylist')
        }
    }
};
var PageWaitsend = {
    construct: function()
    {
        $.getApi({
            'status': '待发货'
        }, 'front/order/orderlist', 'CallBack.appendData');
    }
}