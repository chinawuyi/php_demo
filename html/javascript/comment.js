var CallBack = {
    appendData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            if (json.comment.totalNum == 0)
            {
                $('#jsnocoomment').removeClass('pub_hidden');
            }
            $('#totalNum').append(json.comment.totalNum);
            $('#goodNum').append(json.comment.goodNum);
            $('#normalNum').append(json.comment.normalNum);
            $('#badNum').append(json.comment.badNum);
            $('#photoNum').append(json.comment.photoNum);
            $(json.comment.list).each(function() {
                var answer = '';
                var images = this.images.length;
                var imghtml = '';
                $(this.reply).each(function() {
                    if (this.name == '客服') {
                        answer += '<p style="color:#009edb;"><span style="color:#009edb;">' + this.name + '：</span>' + this.content + '</p>';
                    }
                    else {
                        answer += '<p><span>' + this.name + '：</span>' + this.content + '</p>';
                    }
                });
                $(this.images).each(function(i) {
                    if (i > 2)
                    {
                        return;
                    }
                });
                var html = '<div class="item" images="' + images + '" star="' + this.star + '"><div class="top"><img src="./images/head.jpg" alt="" class="headImg"> <span class="name">' + this.name 
                        + '</span><div class="click"><span class="star' + this.star + '"></span></div></div><div class="middle" style="overflow:hidden"><p>' + this.content + '</p>' + imghtml 
                        + '</div><div class="bottom"><div class="control"><div class="time"><img src="./images/time.png" alt=""> <span>' + this.comTime 
                        + '</span></div><div class="reply"><span>回复</span> <img src="./images/com.png" alt=""></div><div class="comBtn">评价（' + this.replyNum 
                        + '）</div></div><div class="comItem pub_hidden"><div class="comitemin">' + answer + '</div></div><div class="replyItem pub_hidden" commentid="' + this.Id 
                        + '"><input type="text" placeholder="请填写您的评价"> <span class="sub">发&nbsp;&nbsp;表</span></div></div></div>'
                $('#jscomlist').append(html);
            });

            $('.bottom').each(function() {
                var _this = this;
                $(this).find('.comBtn').eq(0).click(function() {
                    $(this).toggleClass('active');
                    $(_this).find('.comItem').eq(0).toggleClass('pub_hidden');
                });
                $(this).find('.reply').eq(0).click(function() {
                    $(this).toggleClass('active');
                    $(_this).find('.replyItem').eq(0).toggleClass('pub_hidden');
                });
            });

            $('.replyItem').each(function() {
                var input = $(this).find('input'),
                        commentid = $(this).attr('commentid');
                $(this).find('span.sub').click(function() {
                    var comment = input.val();
                    if (comment == '')
                    {
                        $.easyErrorBox('请填写您的评价');
                        return;
                    }
                    else {
                        $.getApi({
                            'commentId': commentid,
                            'content': comment
                        }, 'front/comment/addreply', 'CallBack.appendReply');
                    }
                });
            });

        }

    },
    appendReply: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $.easyErrorBox('回复成功', function() {
                window.location.reload();
            });
        }
    }
}
var PageComment = {
    _filter: function(i)
    {
        var items = $('#jscomlist > div.item');
        if (i == 0)
        {
            items.each(function() {
                $(this).removeClass('pub_hidden');
            });
        }
        else if (i == 1)
        {
            items.each(function() {
                if ($(this).attr('star') == '4' || $(this).attr('star') == '5')
                {
                    $(this).removeClass('pub_hidden');
                }
                else {
                    $(this).addClass('pub_hidden');
                }
            });
        }
        else if (i == 2)
        {
            items.each(function() {
                if ($(this).attr('star') == '3' || $(this).attr('star') == '2')
                {
                    $(this).removeClass('pub_hidden');
                }
                else {
                    $(this).addClass('pub_hidden');
                }
            });
        }
        else if (i == 3)
        {
            items.each(function() {
                if ($(this).attr('star') == '1' || $(this).attr('star') == '0')
                {
                    $(this).removeClass('pub_hidden');
                }
                else {
                    $(this).addClass('pub_hidden');
                }
            });
        }
        else if (i == 4)
        {
            items.each(function() {
                if ($(this).attr('images') != '0')
                {
                    $(this).removeClass('pub_hidden');
                }
                else {
                    $(this).addClass('pub_hidden');
                }
            });
        }
        var allhidden = true;
        items.each(function() {
            if (!$(this).hasClass('pub_hidden'))
            {
                allhidden = false;
            }
        });
        if (allhidden == true)
        {
            $('#jsnocoomment').removeClass('pub_hidden');
        }
        else {
            $('#jsnocoomment').addClass('pub_hidden');
        }
    },
    construct: function()
    {
        $.getApi({
            'productId': $.getUrlData().id
        }, 'front/comment/listdata', 'CallBack.appendData');

    },
    defaultEvent: function()
    {
        var _this = this;
        $('#jscomType li').each(function(i) {
            $(this).click(function() {
                $('#jscomType li').each(function(j) {
                    if (i == j)
                    {
                        $(this).addClass('active');
                    }
                    else {
                        $(this).removeClass('active');
                    }
                });
                _this._filter(i);
            });
        });
    }
}