var CallBack = {
    'skuData':[],
    'oldData':null,
    'product':null,
    'favouriteIndex':false,
    'OwnCollect':'0',
    '_subAttr':{
    //    '数量':'1'
    },
    '_appendBanner':function(banner)
    {
        $(banner).each(function(){
            if(this.img.indexOf('http://')>=0)
            {
                $('#bannerShift').append('<img src="'+this.img+'">');
            }
            else{
                $('#bannerShift').append('<img src="'+$.appendBasePath(this.img)+'">');
            }
        });
        var len = $('#bannerShift img').length;
        $('#bannerShift img').each(function(){
            this.onload = function(){
                len -- ;
                if(len == 0)
                {
                    $('#bannerShift').bxSlider({
                        controls:true,
                        auto:true
                    });
                }
            }
        });
    /*    $('#bannerShift').bxSlider({
            controls:true,
            auto:true
        });*/
    },
    '_appendSku':function(sku)
    {
        var _this = this;
        var data = [];
        this.oldData = sku;
        console.log(sku);
        for(var i in sku)
        {
            var obj = sku[i];
            for(var j in sku[i])
            {
                if(j != 'groupId' && j !='pic' && j!='prodId' && j!= 'inventory' && j!= 'price')
                {
                    var beend = false;
                    this._subAttr[j]='';
                    $(data).each(function(){
                        if(this.name == j)
                        {
                            if($.inArray(sku[i][j],this.data)<0)
                            {
                                this.data.push(sku[i][j])
                            }
                            beend = true;
                        }
                    });
                    if(beend == false)
                    {
                         var o = {
                             'name':j,
                             'data':[sku[i][j]]
                         };
                         data.push(o);
                    }
                }
            }
        }
        this.skuData = data;
        if(this.skuData.length == 0)
        {
            $('#jschoosecolor').addClass('pub_hidden');
        }
        $(this.skuData).each(function(){
            var odiv = document.createElement('div');
            $(odiv).addClass('item')
            $(odiv).append('<h2>'+this.name+'</h2>');
            var html = '<ul class="lisAcitve" name="'+i+'">',name = this.name;
            $(this.data).each(function(){
                html += '<li name="'+name+'">'+this+'</li>';
            });
            html += '</ul>';
            $(odiv).append(html);
            $('#jssku').append(odiv);
        });
        $('#jssku ul').each(function(){
            var name = $(this).attr('name');
            var lis = $(this).find('li');
            lis.each(function(i){
                $(this).click(function(){
                    if($(this).hasClass('unselect'))
                    {
                        return;
                    }
                    var val = $(this).text();
                    var name = $(this).attr('name');
                    if($(this).hasClass('active'))
                    {
                        $(this).removeClass('active');
                        _this.removeUnselect(val,name);
                    }
                    else{

                        lis.each(function(j){
                            if(j == i)
                            {
                                $(this).addClass('active');
                                _this.addUnselect(val,name);
                            }
                            else{
                                $(this).removeClass('active');

                            }
                        });
                    }
                    _this.updateSubAttr();

                });
            });
        });
    },
    creatediscount:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
             window.location.href = 'bargainhelp.html?id='+json.disinfo.Id+'&proid='+json.disinfo.prodId
        }
    },
    //更新选中的 规格 数据
    updateSubAttr:function()
    {
        for(var i in CallBack._subAttr)
        {
            CallBack._subAttr[i] = '';
        }
        $('#jssku div.item').each(function(){
            var title = $(this).find('h2').text();
            var lis = $(this).find('li');
            lis.each(function(){
                if($(this).hasClass('active'))
                {
                    CallBack._subAttr[title] = $(this).text();
                }
            });

        });
        var proobj ;
        for(var x in CallBack.oldData)
        {
            for(var x2 in CallBack.oldData[x]){
                var same = true;
                for(var x3 in CallBack._subAttr)
                {
                    if(CallBack._subAttr[x3] != CallBack.oldData[x][x3])
                    {
                        same = false;
                    }
                }
                if(same == true)
                {
                    proobj = CallBack.oldData[x];
                }
            }
        }
        if(proobj)
        {
            console.log(proobj);
            $('#price span').text(proobj.price+'.00');
            $('#num span').text(proobj.inventory);
            if(proobj.pic != null)
            {
                if(proobj.pic.indexOf('http://')>=0)
                {
                    $('#selectpic').html('<img src="'+proobj.pic+'" />');
                }
                else{
                    $('#selectpic').html('<img src="'+UPLOADIMG+proobj.pic+'">');
                }
            }
        }
    },
    removeUnselect:function(val,name)
    {
        $('#jssku div.item').each(function(){
            var title = $(this).find('h2').text();
            if(title == name)
            {
                return;
            }
            $(this).find('li').each(function(){
                $(this).removeClass('unselect');
            });

        });
    },
    addUnselect:function(val,name)
    {
        var data = this.oldData,_data= [];
        for(var j in data)
        {
            for(var i in data[j]){
                if(i != 'groupId' && i !='pic' && i!='prodId')
                {
                    if(i == name)
                    {
                        if(data[j][i] == val)
                        {
                            _data.push(data[j]);
                        }
                    }
                }
            }
        }
        $('#jssku div.item').each(function(){
            var title = $(this).find('h2').text();
            if(title == name)
            {
                return;
            }
            $(this).find('li').each(function(){
                var val = $(this).text();
                var bein = false;
                $(_data).each(function(){
                    if(this[title] == val)
                    {
                        bein = true;
                    }
                });
                if(bein == false)
                {
                    $(this).addClass('unselect');
                }
                else{
                    $(this).removeClass('unselect');
                }
            });

        });
    },
    '_appendAttrs':function(attrs)
    {
        $(attrs).each(function(){
            $('#jsattrs').append('<div class="row"><div class="label"><span>'+this.name+'</span></div><p>'+this.content+'</p></div>');
        });
    },
    '_appendRelated':function(related)
    {
        $(related).each(function(){
            $('#jsrelated').append('<li proid="'+this.id+'"><img src="'+this.img+'" alt=""><h1>'+this.name+'</h1><span class="price"><span>￥</span>'+this.price+'</span></li>');
        });
    },
    'appendCount':function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            var html = '';
            if(json.info.OwnCollect == true)
            {
                html = '<span class="heart heart2"><img src="./images/heart2.png" alt=""><span id="jsfavourite" owncollect="'+json.info.OwnCollect+'">'+json.info.nums+'</span></span>';
            }
            else{
                html = '<span class="heart"><img src="./images/heart.png" alt=""><span id="jsfavourite"  owncollect="'+json.info.OwnCollect+'">'+json.info.nums+'</span></span>';

            }
            $('#jsbrand').append(html);
            $('.heart').click(function(){
                if($(this).find('#jsfavourite').attr('owncollect')=='true')
                {
                    $.easyErrorBox('您已经收藏过了');
                    return;
                }
                if(CallBack.favouriteIndex == true)
                {
                    $.easyErrorBox('您已经收藏过了');
                    return;
                }
                $.getApi({
                    'prodId': $.getUrlData().id
                },'front/collect/adddata','CallBack.addFavourite');
            });
        }
    },
    //秒杀处理
    _seckill:function(obj)
    {
        var start = $.getMicroTime(obj.STARTTIME),
            end   = $.getMicroTime(obj.ENDTIME),
            now   = (new Date()).getTime();
        $('#seckstart').text(obj.STARTTIME+'开始');
        $('#seckend').text(obj.ENDTIME+'结束');
        //已经结束...
        if(now > end)
        {
            $('#seckstart').addClass('pub_hidden');
            $('#jsseckillbtn').text('秒杀结束');
            $('#seckhour').text('00');
            $('#seckmin').text('00');
            $('#secksec').text('00');
            $('#jsseckillbtn').attr('start','false');
        }
        //开始中。。。
        else if(now>=start && now <= end){
            $('#seckstart').addClass('pub_hidden');
            $('#seckstart').text(obj.STARTTIME+'开始');
            var miao = parseInt((end-now)/1000);
            var hour = parseInt(miao/(60*60)),
                min  = parseInt((miao-(hour*60*60))/(60)),
                sec  = miao-hour*60*60 - min*60;
            $('#seckhour').text(hour);
            $('#seckmin').text(min);
            $('#secksec').text(sec);
            var i = 1;
            window.setInterval(function(){
                var miao = parseInt((end-now)/1000);
                miao = miao - i;
                i++;
                var hour = parseInt(miao/(60*60)),
                    min  = parseInt((miao-(hour*60*60))/(60)),
                    sec  = miao-hour*60*60 - min*60;
                $('#seckhour').text(hour);
                $('#seckmin').text(min);
                $('#secksec').text(sec);
                if(miao <=0)
                {
                    window.location.reload();
                }
            },1000);
            $('#secksec').text(sec);
            $('#jsseckillbtn').text('拼命秒杀中');
            $('#jsseckillbtn').attr('start','true');
        }
        //没有开始
        else if(now < start)
        {
            $('#seckend').addClass('pub_hidden');
            var miao = parseInt((start-now)/1000);
            var hour = parseInt(miao/(60*60)),
                min  = parseInt((miao-(hour*60*60))/(60)),
                sec  = miao-hour*60*60 - min*60;
            $('#seckhour').text(hour);
            $('#seckmin').text(min);
            $('#secksec').text(sec);
            var i = 1;
            window.setInterval(function(){
                var miao = parseInt((start-now)/1000);
                miao = miao - i;
                i++;
                var hour = parseInt(miao/(60*60)),
                    min  = parseInt((miao-(hour*60*60))/(60)),
                    sec  = miao-hour*60*60 - min*60;
                $('#seckhour').text(hour);
                $('#seckmin').text(min);
                $('#secksec').text(sec);
            },1000);
            $('#jsseckillbtn').text('即将开始');
            $('#jsseckillbtn').attr('start','false');
        }
        $('#jsseckillbtn').click(function(){
            if($(this).text()=='已售罄')
            {
                return;
            }
            if($(this).attr('start') == 'true')
            {
                //front/order/orderselect
                $.getApi({
                    'prodId':obj.id,
                    'num':'1'
                },'front/order/orderselectbyprodid','CallBack.seckillBack');
            }
        });
    },
    seckillBack:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            if(!json.info.code || json.info.code == '0001')
            {
                $.easyErrorBox('提交成功',function(){
                    var shopCarInfo = JSON.stringify(json.info);
                    $.deleteLocalCache('shopCarInfoCoupons');
                    $.setLocalCache('shopCarInfo',shopCarInfo);
                    window.location.href = 'ordercreate2.html?id='+ $.getUrlData().id+'&num=1';
                });
            }
            else{
                $.easyErrorBox(json.info.msg);
            }

        }
    },
    'appendData':function(json)
    {
        console.log(json);
         if($.checkStatus(json) === false)
         {
             return;
         }
         if(json.returnValue.code == '0001')
         {
             var inventory = true;
             if(new Number(json.product[0].inventory) <= 0)
             {
                  inventory = false;
             }
             //预售
             if(json.product[0].status == '2')
             {
                 $('#jsyushou').removeClass('pub_hidden');
                 $('#jsyushoutime').text(json.product[0].STARTTIME);
             }
             //秒杀处理
             if(json.product[0].status == '3')
             {
                 if(inventory == false)
                 {
                     $('#jsjoincart').addClass('pub_hidden');
                     $('#jsseckill').addClass('pub_hidden');
                 }
                 else{
                     $('#jsjoincart').addClass('pub_hidden');
                     $('#jsseckill').removeClass('pub_hidden');
                     this._seckill(json.product[0]);
                 }

             }
             //砍价处理
             if(json.product[0].status == '4')
             {
                 if(inventory == false){
                     $('.jskanjia').addClass('pub_hidden');
                 }
                 else{
                     $('.jskanjia').removeClass('pub_hidden');
                 }
                 var now = (new Date()).getTime(),
                     starttime = $.getMicroTime(json.product[0].STARTTIME),
                     endtime = $.getMicroTime(json.product[0].ENDTIME);
                 if(endtime >= now && now >=starttime)
                 {
                     var i = 1;
                     var miao = parseInt((endtime - now)/1000);
                     window.setInterval(function(){
                         miao = miao - 1 ;
                         var day = parseInt(miao/(24*60*60)),
                             hour = parseInt((miao - (day*24*60*60))/(60*60)),
                             min = parseInt((miao - day*24*60*60 - hour*60*60)/60),
                             sec = miao - day*24*60*60 - hour*60*60 - min*60;
                         $('#jskanday').text(day);
                         $('#jskanhour').text(hour);
                         $('#jskanmin').text(min);
                         $('#jskansec').text(sec);
                     },1000);

                 }
                 else if(now < starttime)
                 {
                     var i = 1;
                     $('#startstr').text('开始时间');
                     $('#jsdiscount').addClass('pub_hidden');
                     var miao = parseInt((starttime - now)/1000);

                     window.setInterval(function(){
                         miao = miao - 1 ;
                         var day = parseInt(miao/(24*60*60)),
                             hour = parseInt((miao - (day*24*60*60))/(60*60)),
                             min = parseInt((miao - day*24*60*60 - hour*60*60)/60),
                             sec = miao - day*24*60*60 - hour*60*60 - min*60;
                         $('#jskanday').text(day);
                         $('#jskanhour').text(hour);
                         $('#jskanmin').text(min);
                         $('#jskansec').text(sec);
                     },1000);
                 }
                 else
                 {
                     $('#jsdiscount').addClass('pub_hidden');
                     $('#jskanday').text(0);
                     $('#jskanhour').text(0);
                     $('#jskanmin').text(0);
                     $('#jskansec').text(0);
                 }



             }
             //团购处理
             if(json.product[0].status == '5')
             {
                 //$('.countdown').removeClass('pub_hidden');
             }
             if(json.product[0].status == '6')
             {
                 $('#jsheadertitle').text('新品');
             }
             CallBack.product = json.product[0];
             if(CallBack.product.outOfStock !='0')
             {
                 $('#jsseckillbtn').text('已售罄');
                 $('#jsjoincart').addClass('pub_hidden');
             //    return;
             }
             //税金操作
             if(json.product['0'].tax == '0.00')
             {
                 $('#jsshuicon').addClass('pub_hidden');
             }
             else{
                 $('#jsshui').text(json.product['0'].tax);
             }

             var list = json.product.comment.list;
             $(list).each(function(){
                 $('#jspingjialist').append('<div class="row"><p>'+this.content+'</p><div class="from"><span class="person">来自'+this.username+'</span> <span class="time">'+this.comtime+'</span></div></div>');

             });
             $('#jsname').append(json.product['0'].name);
             $('#jsdes').append(json.product['0'].des);
             $('#jstotalnum').append(json.product.comment.totalnum);
             $('#num').html("库存<span>"+json.product['0'].inventory+"</span>件")

             if(json.product['0'].status == '0')
             {
                 $('#jsprice').append('销售价：￥'+json.product['0'].salePrice);
                 $('#jsprice3').append(json.product['0'].salePrice+'元购买');
                 $('#jsprice').css('color','#f56066');
                 $('#jsprice2').append('市场价：￥'+json.product['0'].price);
                 $('#price').html('￥<span>'+json.product['0'].salePrice+'</span>');
             }
             else{
                 $('#jsprice').append('活动价：￥'+json.product['0'].promotionprice);
                 $('#jsprice3').append(json.product['0'].promotionprice+'元购买');
                 $('#jsprice').css('color','#f56066');
                 $('#jsprice2').append('销售价：￥'+json.product['0'].salePrice);
                 $('#price').html('￥<span>'+json.product['0'].promotionprice+'</span>');
             }
             if(json.product['0'].listimage!=null)
             {
                 if(json.product['0'].listimage.indexOf('http://')>=0)
                 {
                     $('#selectpic').html('<img src="'+json.product['0'].listimage+'" />');
                 }
                 else{
                     $('#selectpic').html('<img src="'+UPLOADIMG+json.product['0'].listimage+'">');
                 }
             }
             this._appendBanner(json.product.banner);
             this._appendSku(json.product.skuattr);
             this._appendAttrs(json.product.attrs);
             this._appendRelated(json.product.related);

         }
    },
    addFavourite:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $('#jsfavourite').text(json.info);
            $('.heart').each(function(){
                $(this).addClass('heart2');
                $(this).find('img').attr('src','./images/heart2.png');
            });
            CallBack.favouriteIndex=true;
            $.easyErrorBox('收藏成功');

        }
    },
    addShopingcart:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $('#jscartround').css('display','block');
            $('#jscartround').text(json.info.cartnum);
            $.easyErrorBox('添加成功',function(){
                $('#close').click();
            });
        }
    },
    //立即购买
    buyShopingcart:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            if(json.info && json.info.code && json.info.code !='0001')
            {
                $.easyErrorBox(json.info.msg);
                return;
            }
            $.easyErrorBox('提交成功',function(){
                var shopCarInfo = JSON.stringify(json.info);
                $.deleteLocalCache('shopCarInfoCoupons');
                $.setLocalCache('shopCarInfo',shopCarInfo);
                window.location.href = 'ordercreate2.html?id='+ $.getUrlData().id+'&num='+$('#jsno').text();
            });
        }
    },
    createOrder:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $.easyErrorBox('提交成功',function(){
                var shopCarInfo = JSON.stringify(json.info);
                $.deleteLocalCache('shopCarInfoCoupons');
                $.setLocalCache('shopCarInfo',shopCarInfo);
                window.location.href = 'ordercreate.html';
            });
        }
    }
};
var  PageProdetail = {
    construct:function()
    {
        if($.getUrlData().userid)
        {
            $.getApi({
                'productId': $.getUrlData().id,
                'userId': $.getUrlData().userid,
                'test': $.getUrlData().test
            },'front/prodetail/listdata','CallBack.appendData');
        }
        else{
            $.getApi({
                'productId': $.getUrlData().id
            },'front/prodetail/listdata','CallBack.appendData');
        }
        //获取收藏数量
        if($.getSessionCache('ISLOGIN') == 'true')
        {
            $.getApi({
                'prodId': $.getUrlData().id
            },'front/collect/getcount','CallBack.appendCount');
        }


    },
    defaultEvent:function()
    {
        var _this = this;
        $('#jspingjia').click(function(){
            if($('#jstotalnum').text()=='0')
            {
                 return;
            }
            window.location.href = 'comment.html?id='+ $.getUrlData().id;
        });
        $('#jsjoincart,#jschoosecolor').click(function(){
            if(CallBack.skuData.length == 0)
            {
                $('#jssku').addClass('pub_hidden');
            }
            $('#productmask').removeClass('visibilityhidden');
        });
        $('#jssimi').click(function(){
            window.location.href = 'collocation.html';
        });
        $('#jsshuicon').click(function(){
            window.location.href = 'notice/overseainform.html';
        });
        $('#jsbuy,#jsprice3').click(function(){
            if(CallBack.product.outOfStock !='0')
            {
                $.easyErrorBox('没有库存了');
                return;
            }
            if(CallBack.skuData.length == 0)
            {
                $.getApi({
                    'num':$('#jsno').text(),
                    'prodId':CallBack.product.id
                },'front/order/orderselectbyprodid','CallBack.buyShopingcart');
                return;
            }
            for(var i in CallBack._subAttr)
            {
                if(CallBack._subAttr[i] == '')
                {
                    $.easyErrorBox(i+':不可为空');
                    return;
                }
            }
            var prodId = '';
            for(var x in CallBack.oldData)
            {
                for(var x2 in CallBack.oldData[x]){
                    var same = true;
                    for(var x3 in CallBack._subAttr)
                    {
                        if(CallBack._subAttr[x3] != CallBack.oldData[x][x3])
                        {
                            same = false;
                        }
                    }
                    if(same == true)
                    {
                        prodId = CallBack.oldData[x]['prodId'];
                    }
                }
            }
            $.getApi({
                'num':$('#jsno').text(),
                'prodId':prodId
            },'front/order/orderselectbyprodid','CallBack.buyShopingcart');
        });
        $('#jsjoin').click(function(){
            if(CallBack.product.outOfStock !='0')
            {
                $.easyErrorBox('没有库存了');
                return;
            }
            var islogin = true;
            var prodId = '';
            if(!$.getSessionCache('ISLOGIN') || $.getSessionCache('ISLOGIN') == 'false')
            {
                islogin = false;
            }
            if(CallBack.skuData.length ==  0)
            {
                prodId = CallBack.product.id;
                if(islogin == true)
                {
                    $.getApi({
                        'num':$('#jsno').text(),
                        'prodId':CallBack.product.id
                    },'front/shoppingcart/adddata','CallBack.addShopingcart');
                    return;
                }


            }
            for(var i in CallBack._subAttr)
            {
                if(CallBack._subAttr[i] == '')
                {
                    $.easyErrorBox(i+':不可为空');
                    return;
                }
            }
           // console.log(CallBack.oldData);

            for(var x in CallBack.oldData)
            {
                for(var x2 in CallBack.oldData[x]){
                    var same = true;
                    for(var x3 in CallBack._subAttr)
                    {
                        if(CallBack._subAttr[x3] != CallBack.oldData[x][x3])
                        {
                            same = false;
                        }
                    }
                    if(same == true)
                    {
                        prodId = CallBack.oldData[x]['prodId'];
                    }
                }
            }
            //加入购物车
            if(islogin == true)
            {
                $.getApi({
                    'num':$('#jsno').text(),
                    'prodId':prodId
                },'front/shoppingcart/adddata','CallBack.addShopingcart');
            }
            else
            {
                var _price = '';
                if(CallBack.product.status == '0')
                {
                    _price = CallBack.product.salePrice;
                }
                else{
                    _price = CallBack.product.promotionprice;
                }
                if(!$.getLocalCache('shopCart'))
                {
                    var shopCart = [{'prodId':prodId,'num':parseInt($('#jsno').text())
                     //   'img':CallBack.product.listimage,
                     //   'name':CallBack.product.name,
                     //   'status':CallBack.product.status,
                     //   'price':_price
                    }];
                }
                else{
                    var shopCart = JSON.parse($.getLocalCache('shopCart'));
                    var beenhave = false;
                    $(shopCart).each(function(){
                        if(this.prodId == prodId)
                        {
                            beenhave = true;
                            this.num = this.num + parseInt($('#jsno').text());
                        }
                    });
                    if(beenhave == false)
                    {
                        shopCart.push({
                            'prodId':prodId,
                            'num':parseInt($('#jsno').text())
                        //    'img':CallBack.product.listimage,
                        //    'name':CallBack.product.name,
                        //    'status':CallBack.product.status,
                        //    'price':_price
                        });
                    }
                }
                $.setLocalCache('shopCart',JSON.stringify(shopCart));
                $.easyErrorBox('添加成功',function(){
                    $('#close').click();
                });
            }
        });


        //添加收藏夹

    },
    //砍价
    discount:function()
    {
        $('#jsdiscount').click(function(){
            $.getApi({
                'prodId': $.getUrlData().id
            },'front/discount/creatediscount','CallBack.creatediscount');
        });
    }
}
