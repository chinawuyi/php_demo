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

var PageProlist2 = {
    defaultEvent: function()
    {
        $('#jslist').on('click', 'div.item', function() {

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
            var urlData = $.getUrlData();
            var opt = {
                'pageNo': 0,
                'indextype':indextype,
                'indexorder':indexorder
            };
            if($('#jssearchtext').val() == '')
            {
                if(urlData.catalog)
                {
                    $.extend(opt,{
                        catalog:decodeURI(urlData.catalog)
                    });
                }
                if(urlData.label)
                {
                    $.extend(opt,{
                        label:decodeURI(urlData.label)
                    });
                }
            }
            //产品搜索路线
            else{
                $.extend(opt,{
                    'search': $('#jssearchtext').val()
                });
            }
            $.getPagingApi(opt, 'front/proa/listdata', 'CallBack.constractPaging');
        });
        $("#shift").click(function(){
            $(this).toggleClass("active");
            $("#jslist").toggleClass("active");
        });
    },
    construct: function()
    {
        var urlData = $.getUrlData(),opt = {};
        if(urlData.search)
        {
            $.extend(opt,{
                search:decodeURI(urlData.search)
            });
            $('#jssearchtext').val(decodeURI(urlData.search));
        }
        if(urlData.catalog)
        {
            $.extend(opt,{
                catalog:decodeURI(urlData.catalog)
            });
        }
        if(urlData.label)
        {
            $.extend(opt,{
                label:decodeURI(urlData.label)
            });
        }
        $.getApi(opt, 'front/proa/listdata', 'CallBack.appendData');
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
                var urlData = $.getUrlData();
                var opt = {
                    'pageNo': pageNo,
                    'indexorder':Paging.indexorder,
                    'indextype':Paging.indextype
                };
                //非产品搜索路线，LABEL,CATAGLO路线
                if($('#jssearchtext').val() == '')
                {
                    if(urlData.catalog)
                    {
                        $.extend(opt,{
                            catalog:decodeURI(urlData.catalog)
                        });
                    }
                    if(urlData.label)
                    {
                        $.extend(opt,{
                            label:decodeURI(urlData.label)
                        });
                    }
                    $.getPagingApi(opt, 'front/proa/listdata', 'CallBack.appendPaging');
                }
                //产品搜索路线
                else{
                    $.extend(opt,{
                        'search': $('#jssearchtext').val()
                    });
                    $.getPagingApi(opt, 'front/proa/listdata', 'CallBack.appendPaging');
                }

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