//全局计算字体大小
//API接口配置变量
var GLOBALSIZE;
var objectPrototype = Object.prototype;
Date.prototype.format = function(format) {
    var o = {
        "M+": this.getMonth() + 1, //month
        "d+": this.getDate(), //day
        "h+": this.getHours(), //hour
        "m+": this.getMinutes(), //minute
        "s+": this.getSeconds(), //second
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter
        "S": this.getMilliseconds() //millisecond
    }

    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }

    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
};
$.extend($, {
    isArray: function(value)
    {
        return objectPrototype.toString.apply(value) === '[object Array]';
    },
    isString: function(value)
    {
        return typeof value === 'string';
    },
    isEmpty: function(value)
    {
        return (value === null) || (value === undefined) || (($.isArray(value) && !value.length));
    },
    isFunction: function(value) {
        return objectPrototype.toString.apply(value) === '[object Function]';
    },
    isObject: function(value) {
        return !!value && !value.tagName && objectPrototype.toString.call(value) === '[object Object]';
    },
    packing: function(elems)
    {
        if (elems instanceof $)
        {
            return elems;
        }
        else if ($.isArray(elems) || elems.nodeType)
        {
            return $(elems);
        }
        else if ($.isString(elems))
        {
            if (elems.indexOf('#') >= 0 || elems.indexOf('.') >= 0)
            {
                return $(elems);
            }
            else
            {
                return $('#' + elems);
            }
        }
        else
        {
            return $([]);
        }
    },
    nameSpace: function()
    {
        var a = arguments,o = null,globalObj,i = 1,j,d,arg;
        if (window[arguments[0]])
        {
            globalObj = window[arguments[0]];
        }
        else
        {
            window[arguments[0]] = {};
        }
        for (; i < a.length; i++)
        {
            o = window[arguments[0]];
            arg = arguments[i];
            if (arg.indexOf('.'))
            {
                d = arg.split('.');
                for (j = 0; j < d.length; j++)
                {
                    o[d[j]] = o[d[j]] || {};
                    o = o[d[j]];
                }
            }
            else
            {
                o[arg] = o[arg] || {};
            }
        }
        return;
    },
    setSessionCache: function() {
        var arglen = arguments.length;
        if (arglen == 2)
        {
            sessionStorage.setItem(arguments[0], arguments[1]);
        }
        else if (arglen == 1)
        {
            if ($.isObject(arguments[0]))
            {
                for (var i in arguments[0])
                {
                    sessionStorage.setItem(i, arguments[0][i]);
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    },
    setLocalCache: function() {
        var arglen = arguments.length;
        if (arglen == 2)
        {
            localStorage.setItem(arguments[0], arguments[1]);
        }
        else if (arglen == 1)
        {
            if ($.isObject(arguments[0]))
            {
                for (var i in arguments[0])
                {
                    localStorage.setItem(i, arguments[0][i]);
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    },
    getSessionCache: function(key) {
        if ($.isString(key)) {
            return sessionStorage.getItem(key);
        }
    },
    getLocalCache: function(key) {
        if ($.isString(key)) {
            return localStorage.getItem(key);
        }
    },
    deleteSessionCache: function(key) {
        if ($.isString(key)) {
            $(key.split(',')).each(function() {
                sessionStorage.removeItem(this + '');
            });
        }
    },
    deleteLocalCache: function(key) {
        if ($.isString(key)) {
            $(key.split(',')).each(function() {
                localStorage.removeItem(this + '');
            });
        }
    },
    isIdCard: function(arrIdCard) {
        var tag = false;
        var sigma = 0;
        var a = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        var w = new Array("1", "0", "X", "9", "8", "7", "6", "5", "4", "3", "2");
        for (var i = 0; i < 17; i++) {
            var ai = parseInt(arrIdCard.substring(i, i + 1));
            var wi = a[i];
            sigma += ai * wi;
        }
        var number = sigma % 11;
        var check_number = w[number];
        if (arrIdCard.substring(17) != check_number) {
            tag = false;
        } else {
            tag = true;
        }
        return tag;
    },
    clearSessionCache: function() {
        sessionStorage.clear();
    },
    doObjFun: function()
    {
        var pathname = 'Page' + window.location.pathname.split('/').pop().split('.')[0].replace(/\b\w+\b/g, function(word) {
            return word.substring(0, 1).toUpperCase() + word.substring(1);
        });
        for (var i in window[pathname])
        {
            if ($.isFunction(window[pathname][i]) && i.indexOf('_') != '0')
            {
                window[pathname][i]();
            }
        }
    },
    goToShopCart:function()
    {
        $('footer .lisActive li a').eq(3).click(function(){
            if(!$.getSessionCache('ISLOGIN') || $.getSessionCache('ISLOGIN') == 'false')
            {
                window.location.href = 'login.html';
                return false;
            }
        });
        $('footer .lisActive li a').eq(4).click(function(){
            if(!$.getSessionCache('ISLOGIN') || $.getSessionCache('ISLOGIN') == 'false')
            {
                window.location.href = 'login.html';
                return false;
            }
        });
    },
    appendFooter()
    {
        alert(23232);
        var footerHtml = '<footer><ul class="lisActive"><li class="active"><span><a href="homechuxin.html">首页</a></span></li><li><span><a href="brand.html">分类</a></span></li><li><span><a href="shoppingcart.html">购物车</a></span><span class="cartround" id="jscartround"></span></li><li><span><a href="personal.html">我的</a></span></li></ul></footer>';
        $('body').prepend(footerHtml);
    }
});
$.extend($, {
    getApi: function(options, action, callback) {
        var baseurl = BASEURL;
        var requestData = {
            'callback': callback
        };
        baseurl = baseurl + action;
        $.extend(requestData, options);
        baseurl = baseurl + '?data=' + JSON.stringify(requestData);
        $.ajax({
            url: baseurl,
            dataType: 'jsonp',
            beforeSend: function()
            {
                $.loadingStart();
            },
            complete: function()
            {
                $.loadingEnd();
            },
            jsonpCallback: requestData['callback']
        });
    },
    getPagingApi: function(options, action, callback)
    {
        var baseurl = BASEURL;
        var requestData = {
            'callback': callback
        };
        baseurl = baseurl + action;
        $.extend(requestData, options);
        baseurl = baseurl + '?data=' + JSON.stringify(requestData);
        $.ajax({
            url: baseurl,
            dataType: 'jsonp',
            beforeSend: function()
            {
                $('#jsloading').css('display', 'block');
            },
            complete: function()
            {
                //$.loadingEnd();
            },
            jsonpCallback: requestData['callback']
        });
    },
    _getJson: function(options, action)
    {
        var requestData = {}, z = 0;
        var baseurl = action;
        $.extend(requestData, options);
        for (var i in requestData)
        {
            if (i != 'callback')
            {
                if (z == 0)
                {
                    baseurl += '?' + i + '=' + requestData[i];
                }
                else
                {
                    baseurl += '&' + i + '=' + requestData[i];
                }
                z++;
            }
        }
        $.ajax({
            url: baseurl,
            dataType: 'jsonp',
            beforeSend: function()
            {

            },
            complete: function()
            {

            },
            jsonpCallback: requestData['callback']
        });
    },
    appendEmByWidth: function() {
        var screenW = document.body.clientWidth,
                style = document.createElement('style'),
                size = (screenW / 640) * 24,
                styles = 'html{font-size:' + size + 'px !important;}';
        GLOBALSIZE = size;
        (document.getElementsByTagName("head")[0] || document.body).appendChild(style);
        if (style.styleSheet) {
            style.styleSheet.cssText = styles;
        }
        else {
            style.appendChild(document.createTextNode(styles));
        }
    },
    sortNumber: function(a, b)
    {
        return a - b;
    },
    getStrLen: function(str)
    {
        return str.replace(/[^\x00-\xff]/g, "**").length;
    },
    getUrlData: function() {
        var url = window.location.href,
                urlstr = url.split('?')[1];
        if (urlstr) {
            var urlarr = urlstr.split('&'),
                    data = {};
            $(urlarr).each(function() {
                var arr = (this + '').split('=');
                data[arr[0]] = arr[1];
            });
            return data;
        }
        else {
            return {};
        }
    },
    cutStr: function(str, size)
    {
        var realLength = 0, len = str.length, charCode = -1;
        for (var i = 0; i < len; i++) {
            charCode = str.charCodeAt(i);
            if (charCode >= 0 && charCode <= 128)
                realLength += 1;
            else
                realLength += 2;
        }
        if (realLength <= size)
        {
            return str;
        }
        else {
            var _l = 0;
            var returnstr = '';
            $(str.split('')).each(function() {
                charCode = (this + '').charCodeAt(0);
                if (charCode >= 0 && charCode <= 128) {
                    _l += 1;
                }
                else {
                    _l += 2;
                }
                if (_l >= size)
                {
                    return;
                }
                else {
                    returnstr += this;
                }
            });
            return returnstr + '...';
        }
    },
    confirm: function(title, des, callback)
    {
        var html = '<div class="mask" id="confirmmask"style="display:block;"><div class="confirm"><h1 id="confirmtitle">确定取消</h1><p id="confirmmsg">取消后需要重新下单，您确定要取消订单吗？</p>'
                + '<div class="btn"><span class="red" id="confirmcancel">取消</span><span class="blue" id="confirmconfirm">确定</span></div></div></div>';
        if (document.getElementById('confirmmask') == null)
        {
            var body = document.getElementsByTagName('body')[0];
            $(body).append(html);
            $('#confirmtitle').text(title);
            $('#confirmmsg').text(des);
            $('#confirmcancel').click(function() {
                $('#confirmmask').css('display', 'none');

            });
            $('#confirmconfirm').click(function() {
                $('#confirmmask').css('display', 'none');
                if ($.isFunction(callback))
                {
                    callback.call(true);
                }
            });
        }
        else
        {
            $('#confirmtitle').text(title);
            $('#confirmmsg').text(des);
            $('#confirmmask').css('display', 'block');
        }
    },
    //全局AJAX LOADING遮罩页面
    loadingStart: function() {
        if (document.getElementById('jsLoadingStart') == null)
        {
            var odiv = document.createElement('div'),
                    odiv2 = document.createElement('div'),
                    odiv3 = document.createElement('div'),
                    body = document.getElementsByTagName('body')[0],
                    oimg = document.createElement('img');
            oimg.src = 'images/loading9.gif';
            odiv.id = 'jsLoadingStart';
            $(odiv).css({
                'position': 'fixed',
                'left': '0px',
                'top': '0px',
                'right': '0px',
                'bottom': '0px',
                'z-index': '999999',
                'width': '100%',
                'height': '100%'
            });
            $(oimg).css({
                'position': 'fixed',
                'width': '2em',
                'height': '2em',
                'left': '50%',
                'margin-left': '-1em',
                'top': '50%',
                'margin-top': '-1em'
            });
            $(odiv3).css({
                'position': 'absolute',
                'z-index': '2',
                'left': '0px',
                'top': '0px',
                'width': '100%',
                'height': '100%'
            });
            $(odiv2).addClass('jsmask');
            $(body).append(odiv);
            $(odiv).append(odiv2);
            $(odiv).append(odiv3);
            $(odiv3).append(oimg);
        }
        else {
            $('#jsLoadingStart').css('display', 'block');
        }

    },
    //关闭LOADING遮罩
    loadingEnd: function() {
        $('#jsLoadingStart').css('display', 'none');
    },
    getMicroTime: function(time) {
        if (time) {
            return (new Date(time.replace(/-/g, "/"))).getTime();
        }
        else {
            return (new Date()).getTime();
        }
    },
    getNormalTime: function(microtime, timeformat) {
        var defaultformat = 'yyyy-MM-dd hh:mm';
        if (timeformat) {
            defaultformat = timeformat;
        }
        return new Date(microtime).format(defaultformat);
    },
    easyErrorBox: function(str, callback) {
        if (document.getElementById('jserrorbox') == null) {
            var odiv = document.createElement('div'),
                    body = document.getElementsByTagName('body')[0],
                    html = "<div class='errormask'></div>" +
                    "<div class='errorboxbg'></div>";
            $(body).append(odiv);
            odiv.id = 'jserrorbox';
            $(odiv).addClass('jsmsgbox');
            $(odiv).append(html);
            $('#jserrorbox').css(
                    {
                        'position': 'fixed',
                        'left': '0px',
                        'top': '0px',
                        'bottom': '0px',
                        'width': '100%'
                    });

        }
        $('#jserrorbox').css('display', 'block');
        $('#jserrorbox').find('div.errorboxbg').empty();
        $('#jserrorbox').find('div.errorboxbg').append(str);
        window.setTimeout(function() {
            $('#jserrorbox').css('display', 'none');
            if ($.isFunction(callback)) {
                callback();
            }
        }, 500);
    },
    payImg: function(str, callback) {
        if (document.getElementById('jserrorbox3') == null) {
            var odiv = document.createElement('div'),
                body = document.getElementsByTagName('body')[0],
                html = "<div class='errormask'></div>" +
                    "<div class='errorboxbg23' style='position:fixed;width:80%;left:10%;top:20%;z-index:9999999999;'><div class='imgbg'></div><div class='errorboxbg' style='margin-top:1em;'></div></div>";
            $(body).append(odiv);
            odiv.id = 'jserrorbox3';
            $(odiv).addClass('jsmsgbox');
            $(odiv).append(html);
            $('#jserrorbox2').css(
                {
                    'position': 'fixed',
                    'left': '0px',
                    'top': '0px',
                    'bottom': '0px',
                    'width': '100%'
            });

        }
        $('#jserrorbox3').css('display', 'block');
        $('#jserrorbox3').find('div.imgbg').empty();
        $('#jserrorbox3').find('div.imgbg').append(str);
        $('#jserrorbox3').find('div.errorboxbg').text('请长按或扫描二维码完成支付');
        $('#jserrorbox3 div.errormask').click(function(){
            $('#jserrorbox3').css('display','none');
        });
        if ($.isFunction(callback)) {
            callback();
        }
    },
    isNumeric: function( obj ) {
        return !isNaN( parseFloat(obj) ) && isFinite( obj );
    },
    toDecimal : function (x) {
        var f = parseFloat(x);
        if (isNaN(f)) {
            return;
        }
        f = Math.round(x*100)/100;
        return f;
    },
    _easyErrorBox: function(str, callback) {
        if (document.getElementById('jserrorbox') == null) {
            var odiv = document.createElement('div'),
                    body = document.getElementsByTagName('body')[0],
                    html = "<div class='errormask'></div>" +
                    "<div class='errorboxbg'></div>";
            $(body).append(odiv);
            odiv.id = 'jserrorbox2';
            $(odiv).addClass('jsmsgbox');
            $(odiv).append(html);
            $('#jserrorbox2').css(
                    {
                        'position': 'fixed',
                        'left': '0px',
                        'top': '0px',
                        'bottom': '0px',
                        'width': '100%'
                    });

        }
        $('#jserrorbox2').css('display', 'block');
        $('#jserrorbox2').find('div.errorboxbg').append(str);
        if ($.isFunction(callback)) {
            callback();
        }
    },
    checkStatus: function(json)
    {
        if (json.returnValue.code == '0001')
        {

            return true;
        }
        else {
            if (json.returnValue.code == '10001')
            {
                $.easyErrorBox('未授权', function() {
                    window.location.href = 'login.html';
                });
            }
            else {
                $.easyErrorBox(json.returnValue.des);
            }
            return false;
        }
    },
    //添加全局图片路径
    appendBasePath:function(url)
    {
        url = url+'';
        if(url.indexOf('http:')>=0)
        {
            return url;
        }
        else{
            return UPLOADIMG+url;
        }
    },
    checkClient:function(check)
    {

    },
    payOrder:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if( json.info.code && json.info.code != '0001' )
            {
                $.easyErrorBox(json.info.msg);
                return;
            }
            //二维码支付
            if(json.info.isOpenId+'' == 'false' )
            {
                console.log(json);
                $.payImg('<img style="width:100%;height:auto;" src="'+ $.appendBasePath(json.info.data.png)+'" />');
            }
            //标准支付
            else{
                var appid =json.info.data.appId;// 'wxe8c95b5d441ce729';
                var  time = json.info.data.timeStamp+'';//parseInt((new Date()).getTime()/1000);
                var nonceStr =json.info.data.nonceStr ; // 'sdfsdfsdfsdf';
                var package = json.info.data.package;
                var paySign = json.info.data.paySign;
                function onBridgeReady(){
                    WeixinJSBridge.invoke('getBrandWCPayRequest',{
                            "appId":appid,  //"wx2421b1c4370ec43b",     //公众号名称，由商户传入
                            "timeStamp":time, //" 1395712654",         //时间戳，自1970年以来的秒数
                            "nonceStr":nonceStr, //"e61463f8efa94090b1f366cccfbbb444", //随机串
                            "package":package, //"prepay_id=u802345jgfjsdfgsdg888",
                            "signType":"MD5",         //微信签名方式：
                            "paySign":paySign //"70EA570631E4BB79628FBCA90534C63FF7FADD89" //微信签名
                        },
                        function(res){

                            if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                                $.easyErrorBox('支付成功',function(){
                                    window.location.href = 'personal.html';
                                })
                            }     // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。
                        }
                    );
                }
                if (typeof WeixinJSBridge == "undefined"){
                    if( document.addEventListener ){
                        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                    }else if (document.attachEvent){
                        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                    }
                }else{
                    onBridgeReady();
                }
            }


        }
    }
});



$.extend($.fn, {
    hiddenSearch: function()
    {
        this.each(function() {
            $(this).click(function() {
                if ($(this).siblings('.search_box').css('visibility') == 'visible')
                {
                    $(this).siblings('.search_box').css('visibility', 'hidden');
                }
                else {
                    $(this).siblings('.search_box').css('visibility', 'visible');
                }
            });
        });
    }
});
$.extend($.fn, {
    Countdown:function(no)
    {
        this.each(function(){
            var start = no- 1,_this = this;
            var interval = window.setInterval(function(){
                if(start == 0)
                {
                    $(_this).removeClass('jssendcode2');
                    $(_this).text('发送验证码');
                    clearInterval(interval);
                }
                else{
                    $(_this).text(start+'秒后重新获取');
                }

                start--;
            },1000);
        });
    }
});
var GlobalCallBack = {
    'appendShoppingCartCount': function(json) {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {

            if (json.info == '0')
            {
                $('#jscartround').css('display', 'none');
            }
            else {
                $('#jscartround').css('display', 'block');
            }
            $('#jscartround').text(json.info);
        }
    },
    'checkLogin':function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.setSessionCache('ISLOGIN',json.isLogin);
            if(json.isLogin+''  == 'true')
            {
                $.getApi({
                }, 'front/shoppingcart/getcount', 'GlobalCallBack.appendShoppingCartCount');
            }

        }
    }
};
$(document).ready(function() {
    $.appendEmByWidth();
    $.doObjFun();
    $.getApi({
    }, 'front/proa/checklogin', 'GlobalCallBack.checkLogin');
    $.goToShopCart();
    alert(423424);
    $.appendFooter();
/*    if(!$.getSessionCache('ISLOGIN'))
    {
        $.getApi({
        }, 'front/proa/checklogin', 'GlobalCallBack.checkLogin');
        if (!$.getLocalCache('shopCart') || JSON.parse($.getLocalCache('shopCart')).length == 0)
        {
            $('#jscartround').css('display', 'none');
        }
        else {

            $('#jscartround').css('display', 'block');
            $('#jscartround').text(JSON.parse($.getLocalCache('shopCart')).length);
        }

    }
    else{
        if($.getSessionCache('ISLOGIN') == 'true')
        {
            $.getApi({
            }, 'front/shoppingcart/getcount', 'GlobalCallBack.appendShoppingCartCount');
        }
        else if($.getSessionCache('ISLOGIN') == 'false'){
            if (!$.getLocalCache('shopCart') || JSON.parse($.getLocalCache('shopCart')).length == 0)
            {
                $('#jscartround').css('display', 'none');
            }
            else {

                $('#jscartround').css('display', 'block');
                $('#jscartround').text(JSON.parse($.getLocalCache('shopCart')).length);
            }
        }

    }*/
    //获取购物车 数量

});
var _hmt = _hmt || [];
(function() {
    var hm = document.createElement("script");
    hm.src = "//hm.baidu.com/hm.js?13a53197b8f0a34396a4863fb42c923f";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hm, s);
})();

