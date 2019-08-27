var CallBack = {
    modifydata: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('修改成功', function() {
                window.location.href = 'myaddress.html';
            });
        }
    },
    //默认省市区选择
    provinceselect: function(province, city, zone)
    {
        $('#jsprovince').val(province);
        $('#jsarea').empty();
        $(cityjson).each(function() {
            if (this.id == province)
            {
                $('#jscity').empty();
                $('#jscity').eq(0)[0].child = this.child;
                $('#jscity').append('<option value="0">请选择</option>');
                $(this.child).each(function() {
                    $('#jscity').append('<option value="' + this.id + '">' + this.name + '</option>');
                });
            }
        });
        $('#jscity option').each(function() {
            if ($(this).attr('value') == city)
            {
                $(this).attr('selected', 'selected');
            }
            else {
                $(this).removeAttr('selected');
            }
        });
        $(cityjson).each(function() {
            if (this.id == province)
            {
                $(this.child).each(function() {
                    if (this.id == city)
                    {
                        $(this.child).each(function() {
                            $('#jsarea').append('<option value="' + this.id + '">' + this.name + '</option>');
                        });
                    }
                });
            }
        });
        $('#jsarea option').each(function() {
            if ($(this).attr('value') == zone)
            {
                $(this).attr('selected', 'selected');
            }
            else {
                $(this).removeAttr('selected');
            }
        });

    },
    detailData: function(json)
    {
        console.log(json);
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $('#jsuser').val(json.info.userName);
            $('#jsid').val(json.info.CERTNO);
            $('#jsphone').val(json.info.Mobile);
            $('#jsstreet').val(json.info.street);
            $('#jsaddress').val(json.info.address);
            $('#jszcode').val(json.info.zip);
            var path = json.info.path.split('/');
            this.provinceselect(path[2], path[3], path[4]);
        }
    }
};
var PageModifyaddress = {
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
        this._appendProvince();
        $.getApi({
            'id': $.getUrlData().addressid
        }, 'front/address/detaildata', 'CallBack.detailData');
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
                'id': $.getUrlData().addressid,
                'CERTNO':$('#jsid').val(),
                'name': $('#jsuser').val(),
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
            }, 'front/address/modifydata', 'CallBack.modifydata');
        });
    }
}