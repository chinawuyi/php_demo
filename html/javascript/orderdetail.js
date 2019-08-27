var CallBack = {
    orderNo:'',
    status:'',
    _checkStatus:function(obj)
    {
        if (obj.orderstatus == '已取消'){
            this.status = '已取消';
            return;
        }
        if (obj.orderstatus == '已关闭'){
            this.status = '已关闭';
            return;
        }
        if (obj.applystatus == '未支付'){
            this.status = '待付款';
            return;
        }
        if (obj.aftersalestatus== '无售后'){
            this.status = obj.aftersalestatus;
        }
        if (obj.sendstatus == '待发货'){
            this.status = '待发货';
        }
        if (obj.sendstatus == '无物流'){
            this.status = '待发货';
        }
        if (obj.sendstatus == '待签收'){
            this.status = '待收货';
        }
        if ((obj.orderstatus == '已完成')&&(obj.commentstatus == '未评价')){
            this.status = '待评价';
        }
        if ((obj.orderstatus == '已完成')&&(obj.commentstatus== '已评价')){
            this.status = '已评价';
        }

    },
    orderDetail:function(json)
    {
        console.log(json);
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            this.orderNo = json.orders.order.orderNo;
            this._checkStatus(json.orders.order);
            $('#jsorderstatus').append(this.status);
           // $('#jsorderstatus').text(json.orders.order.);
            $('#jsorderno').text(json.orders.order.orderNo);
            $('#jsordertime').text(json.orders.order.createTime);
            $('#jsid').attr('value',json.orders.order.certNo);
            $('#jsmsg').attr('value',json.orders.order.buyerMsg);
            $('#jsaddressname').text(json.orders.receiver.name);
            $('#jsaddressphone').text(json.orders.receiver.phone);
            $('#jsaddress').text(json.orders.receiver.address);
            $('#tax').text(json.orders.order.TAXFEE);
            $('#freight').text(json.orders.order.postFee);
            $('#jspaytotal').text(json.orders.order.amount);
            //插入订单中的所有商品
            $(json.orders.items).each(function(){
                $('#products').append('<div class="proDetail" prodId="'+this.prodId+'"><img src="'+$.appendBasePath(this.listimage)+'"alt=""class="pic"><div class="right"><h1>'+this.prodName+'</h1><p>单价：<span class="small">￥</span><span class="price">'+this.price+'</span><span class="num">X'+this.count+'</span></p></div></div>');
            });
            $('#products div.proDetail').each(function(){
                $(this).click(function(){
                    var prodId = $(this).attr('prodId');
                    window.location.href = 'prodetail.html?id='+prodId;
                });
            });
            //插入所有的已经使用的coupons
            $(json.orders.cuppons).each(function(){
                var html = '<div class="item" couponid = "'+this.Id+'"><div class="left">￥<span>'+this.price+'</span></div><div class="center"><h1>'+this.name+'</h1><p class="deta">'+this.des+'</p><p class="time">'+this.startDate.split(' ')[0]+'至'+this.endDate.split(' ')[0]+'</p></div></div>';
                $('#jscoupons').append(html);
            });
/*            
            if(json.orders.order.applystatus == '未支付')
            {
                $('#jspay').removeClass('pub_hidden');
            }
            else{
                $('#jspaystatus').append(json.orders.order.applystatus);
                $('#jspaytime').append(json.orders.pays[0].paydate);
                $('#jspayobj').removeClass('pub_hidden');
            };
*/
            if(json.orders.expresses.length >0)
            {
                $('#jsexpresses').removeClass('pub_hidden');
                if(json.orders.expresses[0].items.length <= 0)
                {
                    $('#jsexpresses img').addClass('pub_hidden');
                    $('#jsexpresses h1').text('物流信息 ')
                    $('#jsexpresses h1').append('<span style="margin-left:3em;">'+json.orders.expresses[0].company+'（'+json.orders.expresses[0].no+'）</span>')
                    $('#jsexpresstitle').append('物流公司已经揽件');
                }
                else{
                    $('#jsexpresstitle').append(json.orders.expresses[0].items[0].context);
                    $('#jsexpresstime').append(json.orders.expresses[0].items[0].t);
                    $('#jsexpresses').click(function(){
                        $.setLocalCache('expressesInfo',JSON.stringify(json.orders.expresses));
                        window.location.href = 'logisticsinfo.html';
                    });
                }
            }
            else{

            }

        }
    },
    payOrder:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            if(json.info.success == true)
            {
                window.location.href = json.info.url;
            }
            else{
                $.easyErrorBox(json.info.msg);
            }
        }
    }
};
var PageOrderdetail = {
    construct:function()
    {
        $.deleteLocalCache('expressesInfo');
        //front/order/orderdetail?data={"callback":"bb","orderId":"1"}
        $.getApi({
            'orderId': $.getUrlData().orderid
        },'front/order/orderdetail','CallBack.orderDetail');
    },
    defaultEvent : function()
    {
        $('#jspay').click(function(){
            $('#jsmask').removeClass('pub_hidden');
        });
        $('#jsmask .closeBtn').click(function(){
            $('#jsmask').addClass('pub_hidden');
        });
        $('#jsreadconfirm').click(function(){
            $.getApi({
                'orderNo':CallBack.orderNo
            },'front/order/payorder','CallBack.payOrder');
        });
    }
}