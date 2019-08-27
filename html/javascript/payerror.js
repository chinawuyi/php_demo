var CallBack = {
    orderDetail:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $('#orderprice').text(json.orders.order.amount);
            $('#orderno').text(json.orders.order.orderNo);
            $('#orderuser').text(json.orders.receiver.name);
            $('#orderaddress').text(json.orders.receiver.address);
        }
    }
};
var PagePayerror = {
    construct:function()
    {
        $.getApi({
            'orderId': $.getUrlData().orderno
        },'front/order/orderdetail','CallBack.orderDetail');
    },
    defaultEvent:function()
    {
        $('#jspayback').click(function(){
            window.location.href = 'personal.html';
        });
    }
};