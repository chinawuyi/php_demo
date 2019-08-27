var CallBack = {

};
var PageLogisticsinfo = {
    construct:function(){
        var expressesInfo = JSON.parse($.getLocalCache('expressesInfo'))[0];
        $('#companyname').append(expressesInfo.company);
        $('#orderid').append(expressesInfo.orderId);
        $(expressesInfo.items).each(function(i){
            var first = '';
            if(i == 0)
            {
                first = 'first';
            }
            $('#expresses').append('<div class="item '+first+'"><h2>'+this.context+'</h2><p>'+this.t+'</p></div>');
        });
    }
};