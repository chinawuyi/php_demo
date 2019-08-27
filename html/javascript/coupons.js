var CallBack = {
    addData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.cuppon.length == 0)
            {
                $('#jsnocoomment').removeClass('pub_hidden');

            }
            $(json.cuppon).each(function() {
                var endtime = $.getMicroTime(this.endDate), now = (new Date()).getTime(), over = '';
                if (now > endtime) {
                    over = 'gray'
                }
                var html = '<div class="item ' + over + '"><div class="left">￥<span>' + this.amount + '</span></div><div class="center"><h1>'
                        + this.name + '</h1><p class="deta">' + this.des + '</p><p class="time">' + this.startDate + '至' + this.endDate + '</p></div></div>';
                $('#jscoupons').append(html);
            });

        }
    }
};
var PageCoupons = {
    construct: function() {
        $.getApi({
        }, 'front/cuppon/listdata', 'CallBack.addData');
    }
};