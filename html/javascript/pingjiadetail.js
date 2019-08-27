var CallBack = {
    orderDetail:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            console.log(json);
        }
    }
};
var PagePingjiadetail = {
    construct:function()
    {
        $.getApi({
            'orderId': $.getUrlData().orderid
        },'front/order/orderdetail','CallBack.orderDetail');
    }
}