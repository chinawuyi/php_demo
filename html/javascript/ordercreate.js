var CallBack = {
    createOrder: function(json)
    {
        $.payOrder(json);
    }
}
var PageOrdercreate = {
    _addressid: 0,
    _total: 0,
    _cansub: false,
    construct: function()
    {
        var shopCarInfo = JSON.parse($.getLocalCache('shopCarInfo'));
        var _this = this;
        if ($.getLocalCache('chooseAddress'))
        {
            var chooseAddress = JSON.parse($.getLocalCache('chooseAddress'));
            $('#jsaddressname').text(chooseAddress.user);
            $('#jsaddressphone').text(chooseAddress.mobile);
            $('#jsaddressaddress').text(chooseAddress.address);
            $('#jsid').attr('value',chooseAddress.CERTNO?chooseAddress.CERTNO:'');
            PageOrdercreate._addressid = chooseAddress.addressid;
            $.deleteLocalCache('chooseAddress');
        }
        else {
            if (shopCarInfo.address.length > 0)
            {
                console.log(shopCarInfo);
                $('#jsaddressname').text(shopCarInfo.address[0].userName);
                $('#jsaddressphone').text(shopCarInfo.address[0].Mobile);
                $('#jsaddressaddress').text(shopCarInfo.address[0].address);
                $('#jsid').val(shopCarInfo.address[0].CERTNO == null ? '':shopCarInfo.address[0].CERTNO);

                PageOrdercreate._addressid = shopCarInfo.address[0].Id;
            }
            else {
                $('#jsdefaultaddress').addClass('pub_hidden');
            }
        }
        $(shopCarInfo.products).each(function() {
            _this._total += this.orderAmount;
            $('#products').append('<div class="proDetail"><img src="' +  $.appendBasePath(this.listimage) + '"alt=""class="pic"><div class="right"><h1>' + this.name
                    + '</h1><p>单价：<span class="small">￥</span><span class="price">' + this.orderPrice + '</span><span class="num">X' + this.orderNum + '</span></p></div></div>');
        });
        $('#jspreference').append('可抵用：' + shopCarInfo.preference + '元');
        $('#choosecoupon').click(function() {
            if (shopCarInfo.preference == '0')
            {
                return;
            }
            else {
                window.location.href = 'choosecoupons.html';
            }
        });

        $('#freight').text(shopCarInfo.freight);
        _this._total += shopCarInfo.freight;
        $('#tax').text(shopCarInfo.tax);
        _this._total += shopCarInfo.tax;
        _this._total = $.toDecimal(_this._total)
        $('#jstotal').append(_this._total);
        var couponprice = 0;
        if ($.getLocalCache('shopCarInfoCoupons'))
        {
            $($.getLocalCache('shopCarInfoCoupons').split(',')).each(function() {
                var couponid = this + '';
                $(shopCarInfo.coupons).each(function() {
                    if (this.Id == couponid)
                    {
                        couponprice += new Number(this.amount);
                    }
                });
            });
            if (shopCarInfo.preference <= couponprice)
            {
                couponprice = shopCarInfo.preference;
            }
            $(shopCarInfo.coupons).each(function() {
                if ($.inArray(this.Id, $.getLocalCache('shopCarInfoCoupons').split(',')) >= 0)
                {
                    var html = '<div class="item" couponid = "' + this.Id + '"><div class="left">￥<span>' + this.amount + '</span></div><div class="center"><h1>' 
                            + this.name + '</h1><p class="deta">' + this.des + '</p><p class="time">' + this.startDate.split(' ')[0] + '至' + this.endDate.split(' ')[0] + '</p></div></div>';
                    $('#jscoupons').append(html);
                }

            });
        }
        _this._total -= couponprice;
        $('#jspaytotal').text(_this._total);
    },
    buy: function()
    {

        $('#jspay').click(function() {
            //front/order/createorder?data={"callback":"bb","cartId":["1"],"addressId":"1","buyerMsg":"test","postFee":"100","cuppons":[]}
            var idcard = $('#jsid').val();
            if ($.isIdCard(idcard) == false)
            {
                $.easyErrorBox('请正确填写您的身份证号码');
                return;
            }
            if (PageOrdercreate._addressid == 0)
            {
                $.easyErrorBox('请选择您的收货地址');
                return;
            }
            var shopCarInfo = JSON.parse($.getLocalCache('shopCarInfo'));
            var idcard = $('#jsid').val();
            var shopCarInfoCoupons = [];
            if ($.getLocalCache('shopCarInfoCoupons'))
            {
                shopCarInfoCoupons = $.getLocalCache('shopCarInfoCoupons').split(',');
            }

            $.getApi({
                'cartId': shopCarInfo.cartId,
                'address': PageOrdercreate._addressid,
                'buyerMsg': $('#jsmsg').val(),
                'certNo': idcard,
                'cuppons': shopCarInfoCoupons
            }, 'front/order/createorder', 'CallBack.createOrder');
            return;

        });
    },
    defaultEvent: function()
    {
        $('#jschooseaddress').click(function() {
            window.location.href = 'address.html?back=ordercreate';
        });
        $('.close').click(function() {
            $('#jsmask').addClass('pub_hidden');
        });
        $('#jsreadconfirm').click(function() {
            $('#jsmask').addClass('pub_hidden');

        });
    }
}