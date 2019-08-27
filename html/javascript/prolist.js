var CallBack = {
    appendData: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            _productList(json, 'jslist');
            Paging.pageNo = json.products.pageNo;
            Paging.perPage = json.products.perPage;
            Paging.totalNo = json.products.totalNo;
            if(Paging.totalNo <= 6)
            {
                $('#jsloading').css('display','none');
            }
            Paging.paging();
        }
    },
    appendPaging: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            _productList(json, 'jslist');
            Paging.AJAXSCROLL = false;
        }
    },
    constractPaging:function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $('#jslist').empty();
            _productList(json, 'jslist');
            Paging.AJAXSCROLL = false;
            Paging.pageNo = json.products.pageNo;
            Paging.perPage = json.products.perPage;
            Paging.totalNo = json.products.totalNo;
            if(Paging.totalNo <= 6)
            {
                $('#jsloading').css('display','none');
            }
        }
    },
    appendSearch: function(json)
    {
        if ($.checkStatus(json) === false)
        {
            return;
        }
        if (json.returnValue.code == '0001')
        {
            $('#jslist').empty();
            _productList(json, 'jslist');
            Paging.AJAXSCROLL = false;
            Paging.pageNo = json.products.pageNo;
            Paging.perPage = json.products.perPage;
            Paging.totalNo = json.products.totalNo;
            if(Paging.totalNo <= 6)
            {
                $('#jsloading').css('display','none');
            }
        }
    }
}

var PageProlist = {
    _writePageNo:function(obj)
    {
        var pageno = 0;
        $('div#jslist div.item').each(function(i){
            if(this == obj)
            {
                 var index = i+1;
                 pageno = Math.ceil(index/Paging.perPage);
            }
        });
        return pageno
    },
    defaultEvent: function()
    {
        var _this = this;
        $('#jslist').on('click', 'div.item', function() {
        /*    var pageno = _this._writePageNo(this);
            window.location.href = 'prodetail.html?id=' + $(this).attr('proid')+'&pageno='+pageno;
            return;*/
            window.location.href = 'prodetail.html?id=' + $(this).attr('proid');


        });
        $("#lisAcitveOri").children().click(function (){
            $(this).siblings().removeClass("top bottom");
            if($(this).hasClass("top")){
                $(this).addClass("bottom").removeClass("top");
            }
            else if($(this).hasClass("bottom")){
                $(this).addClass("top").removeClass("bottom");
            }
            else{
                $(this).addClass("top");
            }
            var indextype='',indexorder = '';
            $("#lisAcitveOri").children().each(function(){
                if($(this).hasClass('top'))
                {
                    var searchtype = $(this).text();
                    if(searchtype.indexOf('销量')>=0)
                    {
                        indextype = '销量';
                    }
                    if(searchtype.indexOf('价格')>=0)
                    {
                        indextype = '价格';
                    }
                    if(searchtype.indexOf('评价')>=0)
                    {
                        indextype = '评价';
                    }
                    indexorder = 'asc';
                }
                else if($(this).hasClass('bottom'))
                {
                    var searchtype = $(this).text();
                    if(searchtype.indexOf('销量')>=0)
                    {
                        indextype = '销量';
                    }
                    if(searchtype.indexOf('价格')>=0)
                    {
                        indextype = '价格';
                    }
                    if(searchtype.indexOf('评价')>=0)
                    {
                        indextype = '评价';
                    }
                    indexorder = 'desc';
                }
            });
            Paging.indexorder = indexorder;
            Paging.indextype = indextype;
            $.getPagingApi({
                'pageNo': 0,
                'catalog': $.getUrlData().id,
                'search': $('#jssearchtext').val(),
                'indextype':indextype,
                'indexorder':indexorder
            }, 'front/proa/listdata', 'CallBack.constractPaging');

        });
        $("#shift").click(function(){
            $(this).toggleClass("active");
            $("#jslist").toggleClass("active");
        });
    },
    construct: function()
    {
        $('#jstitle').text(decodeURI($.getUrlData().title));
        $.getApi({
            'catalog': $.getUrlData().id
        }, 'front/proa/listdata', 'CallBack.appendData');
    },
    search: function()
    {
        $('#jssearch').click(function() {
            var jssearchtext = $('#jssearchtext').val();
            if (jssearchtext == '')
            {
                $.easyErrorBox('请输入查询的字符串');
                return;
            }
            else {
                $.getApi({
                    'catalog': $.getUrlData().id,
                    'search': jssearchtext
                }, 'front/proa/listdata', 'CallBack.appendSearch');
            }

        });
    }
};
var Paging = {
    totalNo: '',
    perPage: '',
    pageNo: '',
    indextype:'',
    indexorder:'',
    constructed:false,
    AJAXSCROLL : false,
    paging: function(search)
    {
        var fun = function()
        {
            this.AJAXSCROLL = true;
            if (Paging.pageNo >= Math.ceil(Paging.totalNo / Paging.perPage) - 1)
            {
                $('#jsloadingtext').text('已经没有更多产品了');
                window.setTimeout(function() {
                    $('#jsloading').css('display', 'none');
                }, 400);
            }
            else
            {
                var pageNo = ++Paging.pageNo;
                $.getPagingApi({
                    'pageNo': pageNo,
                    'catalog': $.getUrlData().id,
                    'search': $('#jssearchtext').val(),
                    'indexorder':Paging.indexorder,
                    'indextype':Paging.indextype
                }, 'front/proa/listdata', 'CallBack.appendPaging');
            }
        };
        if(this.constructed == false)
        {
            var _this = this;
            $(window).scroll(function(){
                var scrollTop = $(this).scrollTop(),
                    scrollHeight = $(document).height(),
                    windowHeight = $(this).height();
                if(scrollTop + windowHeight >= scrollHeight-20){
                    if(_this.AJAXSCROLL == false)
                    {
                        if($.isFunction(fun))
                        {
                            fun();
                        }
                    }
                }
            });
        }
    }
}