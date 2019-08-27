var CallBack = {
    'discountId':'',
    'discountcancel':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            if(json.info == true)
            {
                $.easyErrorBox('取消成功',function(){
                    window.location.reload();
                });
            }

        }
    },
    'orderselectbydiscountid':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            var shopCarInfo = JSON.stringify(json.info);
            $.deleteLocalCache('shopCarInfoCoupons');
            $.setLocalCache('shopCarInfo',shopCarInfo);
            window.location.href = 'ordercreate2.html?type=discount&id='+ CallBack.discountId+'&num=1';

        }
    },
    listdata:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            //<li><div class="top"><span class="remind">砍价中</span><span class="money">现价：<span class="blue">￥<span class="num">320</span></span></span></div><div class="middle"><img src="./images/cart_img.jpg"alt=""><div class="right"><h1>意大利DOLCE&GABBAN香水</h1><p>单价：￥<span class="num">160</span></p></div></div><div class="bottom"><span class="red">取消订单</span><span class="blue">立即支付</span></div></li>
            $(json.listdata.doing).each(function(){
                var html = '<li proid="'+this.prodId+'"  id="'+this.Id+'"><div class="top"><span class="remind">砍价中</span><span class="money">现价：<span class="blue">￥<span class="num">'+this.lastamount+'</span></span></span></div><div class="middle"><img src="'+$.appendBasePath(this.thumbnailImage)+'"alt=""><div class="right"><h1>'+this.prodName+'</h1><p>单价：￥<span class="num">'+this.amount+'</span></p></div></div><div class="bottom"><span class="red"  proid="'+this.prodId+'"  id="'+this.Id+'">取消订单</span><span class="blue"  proid="'+this.prodId+'"  id="'+this.Id+'">立即支付</span></div></li>';
                $('#jsstart').append(html);
            });
            $(json.listdata.finish).each(function(){
                var status = '',statushtml = '';
                if(this.PAYSTATUS == '2')
                {
                    status = '已取消';
                }
                else if(this.PAYSTATUS == '1')
                {
                    status = '已支付';
                }
                else if(this.PAYSTATUS == '0')
                {
                    status = '已结束';
                    statushtml = '<div class="bottom"><span class="red"  proid="'+this.prodId+'"  id="'+this.Id+'">取消订单</span><span class="blue"  proid="'+this.prodId+'"  id="'+this.Id+'">立即支付</span></div>';
                }
                var html = '<li proid="'+this.prodId+'"  id="'+this.Id+'"><div class="top"><span class="remind">'+status+'</span><span class="money">现价：<span class="blue">￥<span class="num">'+this.lastamount+'</span></span></span></div><div class="middle"><img src="'+ $.appendBasePath(this.thumbnailImage)+'"alt=""><div class="right"><h1>'+this.prodName+'</h1><p>单价：￥<span class="num">'+this.amount+'</span></p></div></div>'+statushtml+'</li>';
                $('#jsend').append(html);
            });
            $('.wait_pay > li').each(function(){
                $(this).click(function(e){
                    e.stopPropagation();
                    var proid = $(this).attr('proid'),id = $(this).attr('id');
                    window.location.href = 'bargainhelp.html?id='+id+'&proid='+proid;
                  //  alert(proid);
                });
            });
            $('.wait_pay span.red').each(function(){
                $(this).click(function(e){
                    e.stopPropagation();
                    var id = $(this).attr('id');
                    $.getApi({
                        'discountId':id
                    },'front/discount/discountcancel','CallBack.discountcancel');
                });
            });
            $('.wait_pay span.blue').each(function(){
                $(this).click(function(e){
                    e.stopPropagation();
                    var id = $(this).attr('id');
                    CallBack.discountId = id;
                    $.getApi({
                        'num':'1',
                        'discountId':id
                    },'front/order/orderselectbydiscountid','CallBack.orderselectbydiscountid');
                });
            });
        }
    }
};
var PageBargainlist = {
    construct:function()
    {
        $.getApi({
        },'front/discount/listdata','CallBack.listdata');
    }
}