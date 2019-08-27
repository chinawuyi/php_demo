var CallBack = {
    appendData:function(json)
    {
        if($.checkStatus(json) === false)
        {
            return;
        }
        if(json.returnValue.code == '0001')
        {
            $(json.category).each(function(){
                var html = '<div class="item" proid="'+this.id+'"><a ><img src="'+ $.appendBasePath(this.imgurl)+'" alt=""></a><p class="pub_hidden">'+this.name+'</p></div>';
                $('#jslist').append(html);
            });
            $('#jslist div.item').each(function(){
                $(this).click(function(){
                    var proid = $(this).attr('proid'),
                        title = $(this).find('p').text();
                    window.location.href = 'prolist.html?id='+proid+'&title='+title;
                });
            });
        }
    }
}
var PageProductclassification = {
    construct:function()
    {
        $.getApi({
        },'front/productclassification/listdata','CallBack.appendData');
    },
    search:function()
    {
        $('#jssearch').click(function(){
            var jssearchtext = $('#jssearchtext').val();
            if(jssearchtext == '')
            {
                $.easyErrorBox('请输入查询的字符串');
                return;
            }
            else{
                window.location.href = 'prolist2.html?search='+jssearchtext;
            }
        });
    }
}