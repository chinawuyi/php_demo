var CallBack = {
    addAddress: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('创建成功', function() {
                if ($.getUrlData().back )
                {
                    var html = 'address.html?back='+ $.getUrlData().back;
                    if($.getUrlData().id)
                    {
                        html += '&id='+ $.getUrlData().id
                    }
                    if($.getUrlData().num)
                    {
                        html += '&num='+ $.getUrlData().num;
                    }
                    window.location.href = html;
                }
                else {
                    window.location.href = 'myaddress.html';
                }

            });

        }
    }
};
var PageAddaddress = {
    _appendProvince: function()
    {
        $(cityjson).each(function() {
            $('#jsprovince').append('<option value="' + this.id + '">' + this.name + '</option>');
        });
        $('#jsprovince').change(function() {
            var id = $(this).val();
            $('#jsarea').empty();
            $(cityjson).each(function() {
                if (this.id == id)
                {
                    var city = this.child;
                    $('#jscity').empty();
                    $('#jscity').eq(0)[0].child = city;
                    $('#jscity').append('<option value="0">请选择</option>');
                    $(city).each(function() {
                        $('#jscity').append('<option value="' + this.id + '">' + this.name + '</option>');
                    });
                }
            });
        });
        $('#jscity').change(function() {
            var id = $(this).val();
            $(this.child).each(function() {
                if (this.id == id)
                {
                    var child = this.child;
                    $('#jsarea').empty();
                    $('#jsarea').append('<option value="0">请选择</option>');
                    $(child).each(function() {
                        $('#jsarea').append('<option value="' + this.id + '">' + this.name + '</option>');
                    });
                }

            });
        });
    },
    construct: function()
    {
        var back = $.getUrlData().back;
        if (back)
        {
            $('.back a').attr('href', $('.back a').attr('href') + '?back=' + back);
        }
        this._appendProvince();
    },
    defaultEvent: function()
    {
        $('#jssub').click(function() {
            if ($('#jsuser').val() == '')
            {
                $.easyErrorBox('姓名不可为空');
                return;
            }
            if($('#jsid').val() == '')
            {
                $.easyErrorBox('身份证不可为空');
                return;
            }
            if ($('#jsphone').val() == '')
            {
                $.easyErrorBox('手机不可为空');
                return;
            }
            if($.isIdCard($('#jsid').val()) == false)
            {
                $.easyErrorBox('您的身份证不合法');
                return;
            }
            if (!(/^1[3|4|5|8][0-9]\d{4,8}$/.test($('#jsphone').val())))
            {
                $.easyErrorBox('手机号码不正确');
                return;
            }
            if ($('#jsprovince').val() == '0')
            {
                $.easyErrorBox('请选择省');
                return;
            }
            if ($('#jscity').val() == '0')
            {
                $.easyErrorBox('请选择市');
                return;
            }
            if ($('#jsarea').val() == '0')
            {
            //    $.easyErrorBox('请选择区');
            //    return;
            }
            if ($('#jsstreet').val() == '')
            {
                $.easyErrorBox('请填写街道');
                return;
            }
            if ($('#jsaddress').val() == '')
            {
                $.easyErrorBox('请填写详细信息');
                return;
            }
            if ($('#jszcode').val() == '')
            {
                $.easyErrorBox('请填写邮编');
                return;
            }


            $.getApi({
                'name': $('#jsuser').val(),
                'CERTNO':$('#jsid').val(),
                'mobile': $('#jsphone').val(),
                //省
                'province': $("#jsprovince option:selected").text(),
                //市
                'city': $("#jscity option:selected").text(),
                //区
                'district': $("#jsarea option:selected").text(),
                //regionId
                'regionId': $('#jsarea').val(),
                'street': $('#jsstreet').val(),
                'address': $('#jsaddress').val(),
                'zip': $('#jszcode').val()
            }, 'front/address/adddata', 'CallBack.addAddress');
        });
    }
}