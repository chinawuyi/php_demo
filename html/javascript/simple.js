var simple={
	selectSimu: function (){
		var iTrue=false,
			num=0;
	    $(".select").click(function(e){
	    	if(!iTrue){
	    		$(this).addClass("active");
	    		iTrue=true;
	    	}else{
	    		$(this).removeClass("active");
	    		iTrue=false;
	    	}
	    	$(this).parent().css({"position":"relative","z-index":++num});
			e.stopPropagation();
		});
		$(".select ul li").click(function(e){
			$(this).parents(".select").children("a").html($(this).html());
			$(this).parents(".select").removeClass("active");
			e.stopPropagation();
			iTrue=false;
		});
		$(document).click(function(){
			$(".select").removeClass("active");
			iTrue=false;
		});
	},
	radioSimu: function (){
		$(".radio").click(function(){
			if($(this).children('input').attr("name")){
				var name=$(this).children('input').attr("name");
				$(document).find("input[name='"+name+"']").parent(".radio").removeClass("active");
				$(document).find("input[name='"+name+"']").attr("checked",false);
				$(this).addClass("active");
				$(this).children('input').attr("checked",true);
			}
			else{
				if($(this).hasClass("active")){
					$(this).removeClass("active");
					$(this).children('input').attr("checked",false);
				}
				else{
					$(this).addClass("active");
					$(this).children('input').attr("checked",true);
				}
			}
		});
	},
	checkboxSimu: function (){
		$(".checkbox").click(function(){
			if($(this).hasClass("active")){
				$(this).removeClass("active");
			}
			else{
				$(this).addClass("active");
			}
		});
	},
	tab: function (){
		$(".tab>.tabHeader>ul>li").each(function (){
			$(this).attr("num" , $(this).index());
			if($(this).index() == 0){
				$(this).addClass("active");
			}
			else{
				$(this).removeClass("active");
			}
		});
		$(".tab>.tabBody>.list-item").each(function (){
			$(this).attr("num" , $(this).index());
			if($(this).index() == 0){
				$(this).css("display" , "block");
			}
			else{
				$(this).css("display" , "none");
			}
		});
		$(".tab>.tabHeader>ul>li").click(function (){
			var index=$(this).attr("num");
			$(this).parents(".tabHeader").find("li").removeClass("active");
			$(this).addClass("active");
			$(this).parents(".tab").find(".tabBody>.list-item").each(function (){
				var nowIndex=$(this).attr("num");
				if(index == nowIndex){
					$(this).css("display" , "block");
				}
				else{
					$(this).css("display" , "none");
				}
			});
		});
	},
	lisAcitve: function (){
		$(".lisAcitve").children().hover(function (){
			$(this).addClass("active");
		},function(){
			$(this).removeClass("active");
		});
	},
	textdis:function(){//文本框获取焦点时,默认val消失
		$(".textdis").each(function (){
			// console.log($(this).attr("value"));
			$(this).attr("val" , $(this).val());
		});
		$(".textdis").focus(function(){
			$(this).addClass("text");
			if($(this).val()==$(this).attr("val")){
				$(this).val("");
			}
		});
		$(".textdis").blur(function(){
			if($(this).val()==""){
				$(this).val($(this).attr("val"));
				$(this).removeClass("text");
			}
		});
		
	},
	init: function (){
		this.selectSimu();
		this.radioSimu();
		this.checkboxSimu();
		this.tab();
		this.textdis();
		this.lisAcitve();
	}
};
$(function(){
	simple.init();
});