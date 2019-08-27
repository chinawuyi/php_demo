var CallBack = {
    orderDetail: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $('#jsorderno').append($.getUrlData().orderid);
            $(json.user).each(function() {
                var obj = this.products[0];
                $('#pingjiacon').append(_dispComment(this,obj));
            });
            this._eventConstruct();
            this._subPingjia();
        }
    },
    addData: function()
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('提交成功', function() {
                window.location.href = 'waitpingjia.html';
            });
        }
    },
    _subPingjia: function()
    {
        $('.mainBtn').click(function() {
            $('#pingjiacon > div.productitem').each(function() {
                var prodId = $(this).attr('prodId'),
                        orderNo = $(this).attr('orderNo'),
                        star = $(this).find('.click span').attr('starno'), undefined,
                        textcontent = $(this).find('textarea.textcontent').val();
                if (star == undefined)
                {
                    return;
                }
                else {
                    $.getApi({
                        'productId': prodId,
                        'orderNo': orderNo,
                        'content': textcontent,
                        'star': star,
                        'pics': []
                    }, 'front/comment/adddata', 'CallBack.addData');
                }
            });
        });

    },
    _eventConstruct: function() {
        $('.click').each(function(j) {
            $(this).find('ul li').each(function(i) {
                $(this).click(function() {
                    $(this).parents('.click').find('span').attr('class', 'star' + (parseInt(i) + 1));
                    $(this).parents('.click').find('span').attr('starno', parseInt(i) + 1);
                });
            });
        });
        var addImgs = {
            imgNum: 0,
            addImg: function() {
                var _this = this;
                $('#add').click(function() {
                    var html = '';
                    var src = './images/uploadimg.jpg';
                    _this.imgNum++;
                    html = _this.imgNum == 5 ? '<li style="margin-right:0">' : '<li>';
                    html += '<img src="' + src + '"><span class="delete"></span></li>';
                    $('#imgList').append(html);
                    _this.addIconJudge();
                });
            },
            deleteImg: function() {
                var _this = this;
                $('#imgList').on("click", "span.delete", function() {
                    $(this).parents('li').remove();
                    _this.imgNum--;
                    _this.addIconJudge();
                });
            },
            addIconJudge: function() {
                if (this.imgNum > 4) {
                    $('#add').css('display', 'none');
                }
                else {
                    $('#imgList li').last().css('margin-right', '0.83rem');
                    $('#add').css('display', 'block');
                }
            },
            init: function() {
                this.addImg();
                this.deleteImg();
            }
        };
        addImgs.init();
        //字数变化
        //$("#wordNum").html("200");
    }
};
var PageMyevaluate = {
    construct: function()
    {
        $.getApi({
            'orderNo': $.getUrlData().orderid
        }, 'front/user/commentdata', 'CallBack.orderDetail');
    }
}