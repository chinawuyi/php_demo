/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function _dispListOrder(order) {
    var payhtml = '', products = '', result = '';
    if (order.status == '待收货')
    {
        payhtml = '<div class="bottom"><span class="blue jsshouhuo" orderno="' + order.orderId + '" ono="' + order.orderNo + '" ">确认收货</span></div>';
    }
    if (order.status == '待付款')
    {
        payhtml = '<div class="bottom"><span class="red jscancle" orderno="' + order.orderId + '" ono="' + order.orderNo + '" ">取消订单</span> '
                + ' <span class="blue jspay" orderno="' + order.orderNo + ' ">立即支付</span></div>';
    }
    if (order.status == '待评价')
    {
        payhtml = '<div class="bottom"><span class="blue jspingjia" orderno="' + order.orderId + '" ono="' + order.orderNo + '" ">立即评价</span></div>';
    }
    if (order.status == '已评价')
    {
        payhtml = '<div class="bottom"><span class="blue jsmypingjia" orderno="' + order.orderId + '" ono="' + order.orderNo + '"  ">查看评价</span></div>';
    }
    $(order.products).each(function() {
        products += '<div class="middle"><img src="' + $.appendBasePath(this.img) + '" alt=""><div class="right"><h1>' + this.name + '</h1><p>单价：￥<span class="num">' + this.price
                + '</span> X' + this.num + '</p></div></div>';
    });
    result = '<li orderId="' + order.orderId + '"><div class="top"><p>订单号：' + order.orderNo + '</p><span class="remind">' + order.status
            + '</span> <span class="money">合计：<span class="blue">￥<span class="num">' + order.amount + '</span></span></span></div>' + products + payhtml + '</li>';
    return result;
}

function _orderList(json, listid) {
    $(json.orders).each(function() {
        $('#' + listid).append(_dispListOrder(this));
    });
    $('#' + listid + ' li').each(function() {
        $(this).click(function() {
            window.location.href = 'orderdetail.html?orderid=' + $(this).attr('orderId');
        });
    });
    $('#' + listid + ' .jscancle').each(function() {
        $(this).click(function(e) {
            e.stopPropagation();
            var orderno = $(this).attr('orderno');
            $.getApi({
                'orderId': orderno
            }, 'front/order/ordercancel', 'CallBack.orderCancle');
        });
    });
    $('#' + listid + ' .jspingjia').each(function() {
        $(this).click(function(e) {
            e.stopPropagation();
            var orderno = $(this).attr('orderno');
            window.location.href = "evaluate.html?orderid=" + orderno;
        });
    });
    $('#' + listid + ' .jsmypingjia').each(function() {
        $(this).click(function(e) {
            e.stopPropagation();
            var orderno = $(this).attr('ono');
            window.location.href = "myevaluate.html?orderid=" + orderno;
        });
    });
    $('#' + listid + ' .jspay').each(function() {
        $(this).click(function(e) {
            e.stopPropagation();
            var orderno = $(this).attr('orderno');
            $.getApi({
                'orderNo': orderno
            }, 'front/order/payorder', 'CallBack.payOrder');
        });
    });
    $('#' + listid + ' .jsshouhuo').each(function() {
        $(this).click(function(e) {
            e.stopPropagation();
            var orderno = $(this).attr('orderno');
            $.getApi({
                'orderId': orderno
            }, 'front/order/orderreceive', 'CallBack.orderReceive');
        });
    });
}

function _inputComment(item) {
    var html = '';
    html = '<div class="productitem" orderNo="' + item.orderNo + '" prodId="' + item.prodId + '">'
            + '<div class="wait_pay " style="background-color:#ffffff;"><div class="middle" style="margin:0px 5%;">'
            + '<img src="' + UPLOADIMG + item.listimage + '" alt="">'
            + '<div class="right"><h1>' + item.prodName + '</h1><p>单价：￥<span class="num">' + item.price + '</span> X' + item.no + '</p></div></div></div>'
            + '<div class="col"><div class="star"><span class="label">总体评价：</span>'
            + '<div class="click"><span class="starno star0"></span><ul><li></li><li></li><li></li><li></li><li></li></ul></div></div><ul class="comType"></ul></div>'
            + '<div class="textarea marTop" style="margin-bottom:2em;"><textarea class="textcontent" placeholder="请输入您对此产品的意见！"></textarea><p>还可以输入<span id="wordNum"></span>个字</p></div></div>';
    return html;
}

function _dispComment(comment, product) {
    var html = '';
    html = '<div class="productitem" orderNo="' + comment.orderNo + '" prodId="' + comment.prodId + '">'
            + '<div class="wait_pay "  style="background-color:#ffffff;"><div class="middle" style="margin:0px 5%;"><img src="' + $.appendBasePath(product.listimage)
            + '" alt=""><div class="right"><h1>' + product.name + '</h1><p>单价：￥<span class="num">' + product.price + '</span> </p></div></div></div>'
            + '<div class="col"><div class="star"><span class="label">总体评价：</span><div class="click"><span class="starno star' + comment.star
            + '"></span><ul><li></li><li></li><li></li><li></li><li></li></ul></div></div><ul class="comType"></ul></div>'
            + '<div class="textarea marTop" style="margin-bottom:2em;"><textarea class="textcontent" readonly>' + comment.content + '</textarea><p><span id="wordNum"></span></p></div></div>';
    return html;
}
