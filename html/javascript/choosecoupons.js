var PageChoosecoupons = {
    _coupons: [],
    construct: function()
    {
        var shopCarInfo = JSON.parse($.getLocalCache('shopCarInfo')), _this = this,
                shopCarInfoCouponsArr = [],
                coupons = shopCarInfo.coupons;
        if ($.getLocalCache('shopCarInfoCoupons'))
        {
            shopCarInfoCouponsArr = $.getLocalCache('shopCarInfoCoupons').split(',');
            this._coupons = shopCarInfoCouponsArr;
        }
        $(coupons).each(function() {
            var active = '';
            if ($.inArray(this.Id, shopCarInfoCouponsArr) >= 0)
            {
                active = 'active';
            }
            var html = '<div class="item" couponid = "' + this.Id + '"><div class="left">￥<span>' + this.amount + '</span></div><div class="center"><h1>' + this.name + '</h1><p class="deta">' + this.des + '</p><p class="time">' + this.startDate.split(' ')[0] + '至' + this.endDate.split(' ')[0] + '</p></div><div couponid="' + this.Id + '" class="checkbox ' + active + '" style="margin-top:1.7em;"></div></div>';
            $('#jscoupons').append(html);
        });
        $('#jscoupons .checkbox').each(function() {
            $(this).click(function() {
                if ($(this).hasClass('active'))
                {
                    $(this).removeClass('active');
                }
                else {
                    $(this).addClass('active');
                }
                _this._coupons = [];
                $('#jscoupons .checkbox').each(function() {
                    if ($(this).hasClass('active'))
                    {
                        var couponid = $(this).attr('couponid');
                        _this._coupons.push(couponid);
                    }
                });
            });
        });
    },
    defaultEvent: function()
    {
        var _this = this;
        $('#finish').click(function() {
            if (_this._coupons.length == 0)
            {
                $.easyErrorBox('请选择可用的代金卷');
                return;
            }
            else {
                $.setLocalCache('shopCarInfoCoupons', _this._coupons.join(','));
                window.location.href = 'ordercreate.html';
            }
        });
    }
}