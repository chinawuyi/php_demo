var CallBack = {
    constructEvent: function()
    {
        $("#allItem li").on('touchstart', function(e) {
            e = e.event || window.event;
            this.startMove = true;
            this.startX = e.touches[0].clientX;
        });
        $("#allItem li").on('touchmove', function(e) {
            e = e.event || window.event;

            if (this.startMove) {
                this.move = e.touches[0].clientX - this.startX;
            }
            if (this.move < -20) {
                $(this).stop(true, true).animate({'left': '-5.3rem'}, 1000);
                event.stopPropagation();//组织冒泡
                event.preventDefault();//阻止浏览器默认事件
            }
            else if (this.move > 20) {
                $(this).stop(true, true).animate({'left': '0'}, 1000);
                event.stopPropagation();//组织冒泡
                event.preventDefault();//阻止浏览器默认事件
            }

        });
        $("#allItem li").on('touchend', function() {
            this.startMove = false;
            this.startX = 0;
        });
        $("#allItem li").on('click', function(e) {
            var proid = $(this).attr('proid');
            window.location.href = 'prodetail.html?id=' + proid;
        });
        //取消收藏
        $('#allItem li').each(function() {
            var proid = $(this).attr('proid');
            $(this).find('.ope').click(function(e) {
                e.stopPropagation();
                $.getApi({
                    'prodId': proid
                }, 'front/collect/deldata', 'CallBack.deldata');
            });
        });
    },
    deldata: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            window.location.reload();
        }
    },
    appendData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.cart.length == 0)
            {
                $('#jsnocoomment').removeClass('pub_hidden');
            }
            $(json.cart).each(function() {
                $('#allItem').append(_dispListCollect(this));
            });
            this.constructEvent();
        }
    }
};
var PageCollection = {
    construct: function()
    {
        $.getApi({
        }, 'front/collect/listdata', 'CallBack.appendData');
    }
};