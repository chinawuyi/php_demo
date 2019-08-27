var CallBack = {
    addressid: '',
    appendData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.address.length == 0)
            {
                $('#jsnocoomment').removeClass('pub_hidden');
            }
            $(json.address).each(function() {
                var active = '';
                if (this.default == '1')
                {
                    CallBack.addressid = this.id;
                    active = 'active';
                }
                $('#allItem').append('<li><div class="main"><div class="checkbox ' + active + ' "  addressid="' + this.id + '"></div>'
                        + '<div class="address"><p class="line1"><span class="person">收货人：' + this.name + '</span> <span class="phone">电话：' + this.mobile
                        + '</span></p><p class="line2">' + this.address + '</p></div></div><div class="ope"><span class="red  jsdelete" addressid="' + this.id
                        + '">删除</span> <span class="jsmodify blue"  addressid="' + this.id + '" >修改</span></div></li>');
            });
            this.constructEvent();
        }
    },
    constructEvent: function()
    {
        $("#allItem").find(".checkbox").click(function() {
            $("#allItem").find(".checkbox").removeClass("active");
            CallBack.addressid = $(this).attr('addressid');
            $(this).addClass("active");
        });
        //checkbox-end

        //左右滑动出操作按钮-start
    /*    $("#allItem li").on('touchstart', function(e) {
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
            }
            else if (this.move > 20) {
                $(this).stop(true, true).animate({'left': '0'}, 1000);
            }
        });
        $("#allItem li").on('touchend', function() {
            this.startMove = false;
            this.startX = 0;
        });
*/
        $("#allItem li span.jsdelete").click(function() {
            var addressid = $(this).attr('addressid');
            //front/address/adddata
            $.confirm('确认', '您确认要删除吗？', function() {
                $.getApi({
                    'id': addressid
                }, 'front/address/deldata', 'CallBack.deleteAddress');
            });

        });
        $("#allItem li span.jsmodify").click(function() {
            var addressid = $(this).attr('addressid');
            window.location.href = 'modifyaddress.html?addressid=' + addressid;

        });
    },
    //删除寄货地址
    deleteAddress: function(json)
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
    defaultData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('设置成功', function() {
                if ($.getUrlData().back)
                {
                    window.location.href = $.getUrlData().back + '.html';
                }
            });
        }
    }
};
var PageMyaddress = {
    construct: function()
    {
        $.getApi({
            'productId': $.getUrlData().id
        }, 'front/address/listdata', 'CallBack.appendData');
    },
    defaultEvent: function()
    {
        $('#jsaddnew').click(function() {

            window.location.href = 'addaddress.html';
        });

        $('#finish').click(function() {
            if (CallBack.addressid == '')
            {
                $.easyErrorBox('请选择一个收货地址作为您的默认地址');
            }
            else
            {
                $.getApi({
                    'id': CallBack.addressid
                }, 'front/address/defaultdata', 'CallBack.defaultData');
            }
        });
    }
};