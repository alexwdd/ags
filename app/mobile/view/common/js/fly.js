$(document).on('click','.canBuy .set', function (){
    var action = $(this).attr("data-action");	    
    var spec_id = $(this).parent().attr("spec-id");
    var goodsID = $(this).parent().attr("data-id");
    var typeID = $(this).parent().attr("data-type");
    var number = $("#number"+spec_id);
    var cart = $('#barNumber');
    var imgtodrag = $(this).parent().parent('.item').find("img").eq(0);
    if (action=='inc'){
    	number.html(parseInt(number.html())+1);
    	var number = $("#number"+spec_id).html();
	    $.get("/www/cart/addcart?goodsID="+goodsID+"&typeID="+typeID+"&spec_id="+spec_id+"&number=1&act=inc&temp="+new Date().getTime(),function(res){
		        if (res.code==1){
		            $("#barNumber").show().html(res.msg);
		        }
		    },'json'); 

	    var imgclone = imgtodrag.clone()
            .offset({
            top: imgtodrag.offset().top,
            left: imgtodrag.offset().left
        })
            .css({
            'opacity': '0.5',
            'position': 'absolute',
            'height': '150px',
            'width': '150px',  
            'z-index': '1000'
        })
            .appendTo($('body'))
            .animate({
            'top': cart.offset().top + 10,
            'left': cart.offset().left + 0,
            'width': 75,
            'height': 75
        }, 500);
        
        imgclone.animate({
            'width': 0,
                'height': 0
        }, function () {
        	//回调
            $(this).detach(); 
        });
    }else{
    	if (parseInt(number.html())==0){
    		return false;
    	}else{
    		number.html(parseInt(number.html())-1);
    		$.get("/www/cart/addcart?goodsID="+goodsID+"&typeID="+typeID+"&spec_id="+spec_id+"&number=1&act=dec&temp="+new Date().getTime(),function(res){	    			
		        if (res.code==1){
		            $("#barNumber").show().html(res.msg);
		        }
		    },'json'); 
    	}	    	
    }
}); 