var CallBack = {
    //立即购买
    orderselectbyprodid:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('提交成功',function(){
                var shopCarInfo = JSON.stringify(json.info);
                $.deleteLocalCache('shopCarInfoCoupons');
                $.setLocalCache('shopCarInfo',shopCarInfo);
                window.location.href = 'ordercreate2.html?id='+ $.getUrlData().proid+'&num=1';
            });
        }
    },
    orderselectbydiscountid:function(json)
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
            window.location.href = 'ordercreate2.html?type=discount&id='+ $.getUrlData().id+'&num=1';

        }
    },
    discount:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if($.isString(json.discountprice))
            {
                $('#mask').css('display','block');
                $('#discountprice').text(json.discountprice);
            }
            else{
                $.easyErrorBox(json.discountprice.msg);
            }
        }
    },
    discountdata:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            console.log(json);
            this._appendDate(json.disinfo.endTime,json);
            this._appendBanner(json.product.banner);
            this._appendMsg(json);
            this._shareMsg(json);
            this._checkKill(json);
            if(json.disinfo.PAYSTATUS == '1' ||json.disinfo.PAYSTATUS=='2' )
            {
                $('#jskancon').addClass('pub_hidden');
            }
            if(json.disinfo.status == '0')
            {
                $('#jstitle').text('砍价结束');
                $('.countdown').addClass('pub_hidden');
              //  $('#jskanjia').css('visibility','hidden');
                $('#jskanjia').text(json.disinfo.lastamount+'元购买');
                $('#jskanjia').attr('finish','true');
                $('#jsaskhelp').text('砍价结束');
            }
        }
    },
    //判断是否已经砍过价格
    _checkKill:function(json)
    {
        var userid = json.disuserdata.user.Id,
            disrecord = json.disrecord,
            beenkill = false;
        $(disrecord).each(function(){
            if(this.userId == userid)
            {
                beenkill = true;
            }
        });
        if(beenkill == true)
        {
            $('#jskanjia').attr('finish','true');
            $('#jskanjia').attr('canbuy','false');
            $('#jskanjia').text('您已经砍价');
        }
    },
    //分享
    _shareMsg:function(json)
    {
		console.log(json);
        var imgUrl = $.appendBasePath(json.product[0].listimage);// 'http://qqfood.tc.qq.com/meishio/16/4585bf7c-be04-420f-ac8a-2dba61a7561f/0';
       // var lineLink = 'http://life.qq.com/weixin/r/lottery/13826036970196242008#wechat_redirect';
        var descContent = json.product[0].name;//"万达狂欢节, 夺宝幸运星大抽奖活动开始啦！";

	//	var imgUrl = 'http://qqfood.tc.qq.com/meishio/16/4585bf7c-be04-420f-ac8a-2dba61a7561f/0';
     //   var lineLink = 'http://life.qq.com/weixin/r/lottery/13826036970196242008#wechat_redirect';
    //    var descContent = "万达狂欢节, 夺宝幸运星大抽奖活动开始啦！";
       

        var shareTitle = '快来帮我砍价吧' ; // '万达狂欢节';
        var appid = 'wxe8c95b5d441ce729';
        var  time = parseInt((new Date()).getTime()/1000);
        var nonceStr = 'sdfsdfsdfsdf';
        var ticket = json.ticket;
		//var ticket = "sM4AOVdWfPE4DxkXGEs8VJEkuzoh72OytO8QZPKhHqy-ZZvE3wEBupM8d3AO4H9cjy6SOvHQP-uZrh1G897-Ow";
		var url = window.location.href;
        var link = 'http://nonghang.entropytao.com/nonghang/index.php/public/nhportal/bargainredirect?url=bargainhelp.html&param={"id":"'+ $.getUrlData().id+'","proid":"'+ $.getUrlData().proid+'"}';
		//alert(link);
        var string1 = 'jsapi_ticket='+ticket+'&noncestr='+nonceStr+'&timestamp='+time+'&url='+url;
	//	alert(url);
        signature=hex_sha1(string1);
        wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: appid, // 必填，公众号的唯一标识
            timestamp:time , // 必填，生成签名的时间戳
            nonceStr: nonceStr, // 必填，生成签名的随机串
            signature: signature,// 必填，签名，见附录1
            jsApiList: ['onMenuShareAppMessage','onMenuShareTimeline','onMenuShareWeibo'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
        });
        wx.ready(function(){
            wx.onMenuShareAppMessage({
                title: shareTitle, // 分享标题
                desc: descContent, // 分享描述
                link: link, // 分享链接
                imgUrl: imgUrl, // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户确认分享后执行的回调函数
                   // alert('success');
                },
                cancel: function () {
                  //  alert('cancel');
                    // 用户取消分享后执行的回调函数
                }
            });
			//分析到朋友圈
			wx.onMenuShareTimeline({
				title: shareTitle, // 分享标题
				link: link, // 分享链接
				imgUrl: imgUrl, // 分享图标
				success: function () { 
					// alert('success');
					// 用户确认分享后执行的回调函数
				},
				cancel: function () { 
					// alert('cancel');
					// 用户取消分享后执行的回调函数
				}
			});
            //分享到微博
            wx.onMenuShareWeibo({
                title:shareTitle, // 分享标题
                desc:descContent, // 分享描述
                link: link, // 分享链接
                imgUrl: imgUrl, // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                  //  alert('success');
                },
                cancel: function () {
                  //  alert('cancel');
                    // 用户取消分享后执行的回调函数
                }
            });


        });
    },
    _countdiscount:function(disrecord,lastamount)
    {
        console.log(disrecord);
        if($.isArray(disrecord) && disrecord.length == 0)
        {
            $('#jstotal').text('0');
            $('#jsdiscountprice').text('￥'+lastamount);
            $('#jskanlist').addClass('pub_hidden');
        }
        else{
            $('#jskanlist').removeClass('pub_hidden');
            $('#jstotal').text(disrecord.length);
            $(disrecord).each(function(){
                $('#jskanlistbody').append('<tr><td>'+this.userName+'</td><td>￥'+this.discount+'</td><td><span class="orange">￥'+this.price+'</span></td></tr>');
            });
            $('#jsdiscountprice').text('￥'+lastamount);
        }
    },
    //插入用户和砍价发起人
    _appendDiscountData:function(json)
    {
        //用户自己查看
        if(json.disuserdata.user.Id == json.disuserdata.disuser.Id)
        {
            //砍价结束
            if(json.disinfo.status == '0')
            {
                $('#jsuser').append('亲：'+'<span class="orange" >恭喜你，砍价结束。</span>');
                $('#jsusermsg').append('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;共有 <span class="orange" id="jstotal" ></span>  位亲友帮忙砍价了，最终价格为<span class="orange" id="jsdiscountprice"></span>，赶紧购买吧！');
            }
            else{
                $('#jsuser').append('亲：'+'<span>正在砍价中哦。</span>');
                $('#jsusermsg').append('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;共有 <span class="orange" id="jstotal" ></span>  位亲友帮忙砍价了，当前价格为<span class="orange" id="jsdiscountprice"></span>，赶紧继续邀请朋友来砍价吧！');

            }
            $('#jskanjia').text(json.disinfo.lastamount+'元购买');
            $('#jskanjia').attr('finish','true');

        }
        //非用户自己查看
        else{


            $('#jsuser').append('亲爱的'+json.disuserdata.user.userName+'：');
            $('#jsusermsg').append('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;已经有 <span class="orange" id="jstotal" ></span>  位亲友帮帮助<span >'+json.disuserdata.disuser.userName+'</span>砍价了，当前价格为<span class="orange" id="jsdiscountprice"></span>，你也来帮他砍一刀吧，也可邀请你的朋友帮他砍价哦！');
            $('#jskanlist').css('display','none');
          //  $('#jsdiscounter').text(json.disuserdata.disuser.userName);
        }
        this._countdiscount(json.disrecord,json.disinfo.lastamount);

    },
    _appendMsg:function(json)
    {
        $('#jsname').append(json.product['0'].name);
        //插入用户和砍价发起人
        this._appendDiscountData(json);

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
        //税金操作
        if(json.product['0'].tax == '0.00')
        {
            $('#jsshuicon').addClass('pub_hidden');
        }
        else{
            $('#jsshui').text(json.product['0'].tax);
        }
    },
    '_appendBanner':function(banner)
    {
        $(banner).each(function(){
            if(this.img.indexOf('http://')>=0)
            {
                $('#bannerShift').append('<a href="prodetail.html?id='+ $.getUrlData().proid+'"><img src="'+this.img+'"></a>');
            }
            else{
                $('#bannerShift').append('<a href="prodetail.html?id='+ $.getUrlData().proid+'"><img src="'+ $.appendBasePath(this.img)+'"></a>');
            }
        });
        var len = $('#bannerShift img').length;
        var beend = false;
        $('#bannerShift img').each(function(){
            this.onload = function(){
                len -- ;
                if(beend == false)
                {
                    $('#bannerShift').bxSlider({
                        controls:true,
                        auto:true
                    });
                    beend = true;
                }
            }
        });
        /*    $('#bannerShift').bxSlider({
         controls:true,
         auto:true
         });*/
    },
    //插入顶部的倒计时
    _appendDate:function(endtime,json)
    {
        var now = (new Date()).getTime(),
            endtime = $.getMicroTime(endtime);
        if(endtime >= now)
        {
            var i = 1;
            var miao = parseInt((endtime - now)/1000);

            var inter = window.setInterval(function(){
                if(miao  <= 0)
                {
                    window.clearInterval(inter);
                    $('#jskanday').text(0);
                    $('#jskanhour').text(0);
                    $('#jskanmin').text(0);
                    $('#jskansec').text(0);
                    return;
                }
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
        else{
            $('#jskanday').text(0);
            $('#jskanhour').text(0);
            $('#jskanmin').text(0);
            $('#jskansec').text(0);
            $('#jstitle').text('砍价结束');
            $('.countdown').addClass('pub_hidden');
            $('#jskanjia').text(json.disinfo.lastamount+'元购买');
            $('#jskanjia').attr('finish','true');
            $('#jsaskhelp').text('砍价结束');
        }
    }
};
var PageBargainhelp = {
    defaultEvent:function()
    {
        $('#jsshuicon').click(function(){
            window.location.href = 'notice/overseainform.html';
        });

        //立即购买
        $('#jsjoincart').click(function(){
            $.getApi({
                'num':'1',
                'prodId': $.getUrlData().proid
            },'front/order/orderselectbyprodid','CallBack.orderselectbyprodid');
        });
    },
    //砍价
    discountdata:function()
    {
        $('#jskanjia').click(function(){
            if($(this).attr('finish') == 'true')
            {
                if($(this).attr('canbuy') == 'false')
                {
                    return;
                }
                $.getApi({
                    'num':'1',
                    'discountId': $.getUrlData().id
                },'front/order/orderselectbydiscountid','CallBack.orderselectbydiscountid');

            }
            else{
                $.getApi({
                    'prodId': $.getUrlData().proid,
                    'id': $.getUrlData().id
                },'front/discount/discount','CallBack.discount');
            }

        });
    },
    construct:function(){
        var proid = $.getUrlData().proid,
            cutid = $.getUrlData().id;
        $.getApi({
            'prodId': proid,
            'id':cutid,
			'random':(new Date()).getTime()
        },'front/discount/discountdata','CallBack.discountdata');
    },
    share : function()
    {
        $('#jsaskhelp').click(function(){
            if($(this).text() == '砍价结束')
            {
                return;
            }
            $('#jssharemask').removeClass('pub_hidden');
        });
        $('#jssharemask').click(function(){
            $(this).addClass('pub_hidden');
        });
    }
}