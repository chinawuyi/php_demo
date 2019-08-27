var CallBack = {
    cartarr: [],
    appendData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            console.log(json);
            if (json.cart.length == 0)
            {
                $('#jsempty').removeClass('pub_hidden');
                $('.perchase').addClass('pub_hidden');
                $('.cartnone').click(function() {
                    window.location.href = '../home.html';
                });
            }
            $(json.cart).each(function() {
                if(this.OVERSEAFLAG == '1')
                {
                    $('#allItem').append(_dispListShopcart(this));
                }
                else{
                    $('#allItem2').append(_dispListShopcart(this));
                }

            });
            this.constructEvent();

        }
    },
    countAll: function()
    {
        var total = 0;
        CallBack.cartarr = [];
        $('.allitems li').each(function() {
            var checkbox = $(this).find('.checkbox');
            if (checkbox.hasClass('active'))
            {
                var price = new Number($(this).find('span.priceitem').text()),
                        no = new Number($(this).find('span.buyno').text());
                total += price * no;
                var rowid = $(this).attr('rowid');
                if ($.inArray(rowid, CallBack.cartarr) < 0)
                {
                    CallBack.cartarr.push(rowid);
                }
            }
        });
        $('#jstotal').text(total);
    },
    constructEvent: function()
    {
        var _this = this;
        $("#chooseAll").attr({"num": '0', "allnum": $(".allitems").find("li").length});
        $("#chooseAll").click(function() {
            if ($(this).hasClass("active")) {
                // $(this).removeClass("active");
                $(".allitems").find(".checkbox").addClass("active");
                $("#chooseAll").attr("num", "0");
            }
            else {
                //  $(this).addClass("active");
                $(".allitems").find(".checkbox").removeClass("active");
                $("#chooseAll").attr("num", $(this).attr("allnum"));
            }
            _this.countAll();

        });
        $(".allitems .checkbox").click(function() {
            var num = parseInt($("#chooseAll").attr("num"));
            if ($(this).hasClass("active")) {
                $(this).removeClass("active");
                num--;
                $("#chooseAll").removeClass("active");
            }
            else {
                $(this).addClass("active");
                num++;
                if (num == $("#chooseAll").attr("allnum")) {
                    $("#chooseAll").addClass("active");
                }
            }
            $("#chooseAll").attr("num", num);
            _this.countAll();
        });
        //checkbox-end

        //左右滑动出操作按钮-start
        $(".allitems li").on('touchstart', function(e) {
            e = e.event || window.event;
            this.startMove = true;
            this.startX = e.touches[0].clientX;
        });
        $(".allitems li").on('touchmove', function(e) {
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
        $(".allitems li").on('touchend', function() {
            this.startMove = false;
            this.startX = 0;
        });
        //左右滑动出操作按钮-start

        //数量加减
        $(".adjust .reduce").click(function() {
            var obj = $(this).siblings('.num').eq(0).find('span');
            var num = parseInt(obj.html());
            num--;
            if (num < 1) {
                num = 1;
            }
            obj.html(num);
        });
        $(".adjust .add").click(function() {
            var obj = $(this).siblings('.num').eq(0).find('span');
            var num = parseInt(obj.html());
            num++;
            obj.html(num);
        });
        $('.allitems li').each(function() {
            var proid = $(this).attr('proid');
            $(this).find('img.pic').click(function() {
                window.location.href = 'prodetail.html?id=' + proid;
            });
            $(this).find('div.right div.top').click(function() {
                window.location.href = 'prodetail.html?id=' + proid;
            });
        });
        $(".classify").each(function(i) {
            $(this).click(function() {
                if ($(this).hasClass("active")) {
                    $(this).html("编辑").siblings(".right").eq(0).find(".adjust").eq(0).css("display", "none");
                    $(this).siblings(".right").eq(0).find(".top").eq(0).css("display", "block");
                    $(this).removeClass("active");
                    var proid = $(this).attr('proid');
                    $('.allitems li').each(function(j) {
                        if (j == i)
                        {
                            var no = $(this).find('div.num span').text();
                            _this.modifyShopingcart(proid, no);
                        }
                    });

                }
                else {
                    $(this).html("完成").siblings(".right").eq(0).find(".adjust").eq(0).css("display", "block");
                    $(this).siblings(".right").eq(0).find(".top").eq(0).css("display", "none");
                    $(this).addClass("active");
                }

            });
        });
        $('.jsdelete').click(function() {
            var rowid = $(this).attr('rowid');
            //deldata
            $.getApi({
                'cartId': rowid
            }, 'front/shoppingcart/deldata', 'CallBack.deleteData');
        });
    },
    deleteData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('删除成功', function() {
                // var newcartnum = (new Number($.getLocalCache('cartnum')))-1;
                // $.setLocalCache('cartnum',newcartnum);
                window.location.reload();
            });
        }
    },
    modifyShopingcart: function(id, no)
    {
        $.getApi({
            'id': id,
            'nums': no
        }, 'front/shoppingcart/modifynumdata', 'CallBack.modifyCart');
    },
    modifyCart: function(json)
    {
        var _this = this;
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            var num = json.info.num,
                    proid = json.info.proId;
            $('.allitems li').each(function() {
                if ($(this).attr('proid') == proid) {
                    $(this).find('span.buyno').text(num);

                }
            });
        }
    },
    createOrder: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if(json.info && json.info.code && json.info.code !='0001')
            {
                $.easyErrorBox(json.info.msg);
                return;
            }
            $.easyErrorBox('提交成功', function() {
                var shopCarInfo = JSON.stringify(json.info);
                $.deleteLocalCache('shopCarInfoCoupons');
                $.setLocalCache('shopCarInfo', shopCarInfo);
                window.location.href = 'ordercreate.html';
            });
        }
    }
}
var PageShoppingcart = {
    construct: function()
    {
        $.getApi({
        }, 'front/shoppingcart/listdata', 'CallBack.appendData');

    },
    defaultEvent: function()
    {
        $('#jscreateorder').click(function() {
            if (CallBack.cartarr.length == 0)
            {
                $.easyErrorBox('请选择您需要结算的商品');
                return;
            }
            var together = false;
            if($('#allItem .active').length >0 && $('#allItem2 .active').length >0 )
            {
                $.easyErrorBox('跨境和非跨境商品无法同时结算');
                return;
            }
            $.getApi({
                'cartId': CallBack.cartarr
            }, 'front/order/orderselect', 'CallBack.createOrder');
        });
    }

}