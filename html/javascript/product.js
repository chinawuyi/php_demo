/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function _dispListProduct(product) {
    var pricehtml = '', labelhtml = '';
    if (product.outOfStock >= '1') {
        labelhtml = '<div class="none"><img src="./images/none.png" alt=""></div>';
    }
    //labelhtml = labelhtml + '<span class="icon5"></span>';
    //正常
    if (product.status == '0') {

    }
    //促销，特价
    else if (product.status == '1') {
        labelhtml = labelhtml + '<span class="icon5"></span>';
    }
    //预售
    else if (product.status == '2') {
        var time = '';
        var time = $.getNormalTime($.getMicroTime(product.STARTTIME));
        labelhtml = labelhtml + '<span class="icon3"></span>';
        labelhtml += '<div class="foreshow">' + time + '预售</div>';
    }
    //秒杀
    else if (product.status == '3') {
        var starttime = $.getMicroTime(product.STARTTIME),
            endtime = $.getMicroTime(product.ENDTIME),
            nowtime = (new Date()).getTime();

        //秒杀中
        if (nowtime >= starttime && nowtime <= endtime) {
            labelhtml = labelhtml + '<span class="icon6"></span>';
            var per = Math.ceil((product.orderNum / product.storeNum) * 100) + '%';
            var html = '<div class="progress-con"><div class="progress " ><div class="progress-bar" style="width:' + per + '"></div></div><div class="progress-txt">' + per + '已被抢购</div></div>';
            labelhtml += html;
        }
        //秒杀未开始
        else if (nowtime < starttime) {
            labelhtml = labelhtml + '<span class="icon1"></span>';
            labelhtml += '<div class="foreshow">' + product.STARTTIME + '开抢</div>';
        }
        //秒杀结束
        else if (nowtime > endtime) {

        }
    }
    //砍价
    else if (product.status == '4') {
        var start = $.getNormalTime($.getMicroTime(product.STARTTIME), 'yyyy.MM.dd');
        var end = $.getNormalTime($.getMicroTime(product.ENDTIME), 'yyyy.MM.dd');
        labelhtml = labelhtml + '<span class="icon4"></span>';
        labelhtml += '<div class="foreshow">' + start + ' - ' + end + '</div>';
    }
    //团购
    else if (product.status == '5') {
        labelhtml = labelhtml + '<span class="icon7"></span>';
    }
    //新品
    else if (product.status == '6') {
        labelhtml = labelhtml + '<span class="icon2"></span>';
    }
    if (product.status == '0') {
        pricehtml = '<div class="price" ><div style="font-size:0.75em;color:#009edb;">' + '销售价：￥' + product.salePrice
            + '</div><div  style="text-decoration:line-through;color:#424242;font-size:0.55em;">' + '市场价：￥' + product.price + '</div></div>';
    }
    else {
        pricehtml = '<div class="price"><div  style="color:#f56066;font-size:0.75em;">' + '活动价：￥' + product.promotionprice
            + '</div><div style="text-decoration:line-through;font-size:0.75em;color:#009edb;">' + '销售价：￥' + product.salePrice + '</div></div>';
    }
    var html = '<div class="item" proid="' + product.id + '"><div class="bigPic"><img src="' + $.appendBasePath(product.img)
        + '"alt=""class="pic">' + labelhtml + '</div><div class="other"><div class="word"><p>' + $.cutStr(product.name, 50)
        + '</p></div>' + pricehtml + '</div></div>';
    return html;
}

function _productList(json, listid) {
    $(json.products.list).each(function () {
        var html = _dispListProduct(this);
        $.packing(listid).append(html);
    })
}
function _productListString(json)
{
    var html = '';
    $(json).each(function () {
        html += _dispListProduct(this);
    });
    return html;
}

function _dispBannerProduct(product) {
    var pricehtml = '', picsale = '', html = '', hearthtml = '';
    if (product.status == '0') {
        pricehtml = '<p class="now"><span style="margin-right:10px;">' + '销售价：￥' + product.salePrice
            + '</span><span style="text-decoration:line-through;color:#424242;" >' + '市场价：￥' + product.price + '</span></p>';
    }
    else {
        pricehtml = '<p class="now"><span style="margin-right:10px;color:#f56066;" class="red">' + '活动价：￥' + product.promotionpice
            + '</span><span style="text-decoration:line-through;">' + '销售价：￥' + product.salePrice + '</span></p>';
        picsale += '<img src="./images/sale.png"alt="" class="sale" style="width:3.4em;position:absolute;top:0px;left:0px;">';

    }
    if (product.OwnCollect == '1') {
        hearthtml = '<span class="heart heart2 pub_hidden" OwnCollect="' + product.OwnCollect + '" productid="' + product.id
            + '"><img src="./images/heart2.png"alt=""><span class="heartno">' + product.collect + '</span></span>';
    }
    else {
        hearthtml = '<span class="heart heart pub_hidden" OwnCollect="' + product.OwnCollect + '" productid="' + product.id
            + '"><img src="./images/heart.png"alt=""><span class="heartno">' + product.collect + '</span></span>';
    }
    html = '<div class="item difBg"><div class="itemTitle"><span>' + $.cutStr(product.name, 50) + '</span></div>'
        + '<div class="itemBody"><div class="picWrap" productid="' + product.id + '">' + picsale + '<img src="' + $.appendBasePath(product.img) + '"alt=""class="big">' + hearthtml + '</div>'
        + '<div class="opera"><div class="left">' + pricehtml + '</div>'
        + '<div class="right pub_hidden"><img src="./images/buy.png"alt=""><span>加入购物车</span></div></div></div></div>';
    return html;
}

function _dispListCollect(product) {
    var html = '';
    html = '<li proid="' + product.id + '"><div class="main"><img src="' + $.appendBasePath(product.img)
        + '"alt=""class="pic"><div class="right"><h1>' + product.name + '</h1><p>单价：<span class="price"><span class="small">￥</span><span class="num">' + product.price
        + '</span></span></p></div></div><div class="ope"><span class="red">取消收藏</span></div></li>';
    return html;
}

function _dispListShopcart(cart) {
    var html = '';
    html = '<li rowId="' + cart.rowId + '"  proid="' + cart.prodId + '"><div class="main classi"><div class="classify" proid="' + cart.prodId + '">编辑</div>'
        + '<div class="checkbox"></div><img src="' + $.appendBasePath(cart.img) + '" alt=""class="pic"><div class="right"><div class="top"><h1>' + cart.name
        + '</h1><p>单价：<span class="price"><span class="small">￥</span><span class="num">' + cart.price + '</span></span>x<span class="buyno">' + cart.num
        + '</span></p></div><div class="adjust"><div class="reduce"><img src="./images/reduce.png"alt=""></div><div class="num"><span>' + cart.num
        + '</span></div><div class="add"><img src="./images/add.png"alt=""></div></div></div></div><div class="ope"><span class="red jsdelete" rowId="' + cart.rowId + '" >删除</span></div></li>';
    return html;
}
