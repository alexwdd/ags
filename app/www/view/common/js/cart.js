$(function() { 
	$('.canBuy .set').on('click', function () {
	    var action = $(this).attr("data-action");
	    var spec_id = $(this).parent().parent().find(".setNumber").attr("spec-id");
	    var goodsID = $(this).parent().parent().find(".setNumber").attr("data-id");
	    var typeID = $(this).parent().parent().find(".setNumber").attr("data-type");
	    var number = $("#number"+spec_id);
	    var cart = $('#topCart');
	    var imgtodrag = $(this).parent().parent().parent('.product').find("img").eq(0);
	    if (action=='inc'){
	    	number.html(parseInt(number.html())+1);
	    	var number = $("#number"+spec_id).html();
		    $.get("/www/cart/addcart?goodsID="+goodsID+"&typeID="+typeID+"&spec_id="+spec_id+"&number=1&act=inc&temp="+new Date().getTime(),function(res){
			        if (res.code==1){
			            $("#cartNumber").show().html(res.msg);
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
	            'left': cart.offset().left + 50,
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
			            $("#cartNumber").show().html(res.msg);
			        }
			    },'json'); 
	    	}	    	
	    }
	}); 

	$('.addCart').on('click', function () {
	    var cart = $('#topCart');
	    var goodsID = $(this).attr("data-id");
	    var spec_id = $(this).attr("spec-id");
	    var typeID = $(this).attr("data-type");
	    var number = $("#number"+spec_id).html();
	    var imgtodrag = $(this).parent().parent('.product').find("img").eq(0);
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
            'left': cart.offset().left + 50,
            'width': 75,
            'height': 75
        }, 500);
        
        imgclone.animate({
            'width': 0,
                'height': 0
        }, function () {
        	//回调
            $(this).detach();
            $.get("/www/cart/addcart?goodsID="+goodsID+"&typeID="+typeID+"&spec_id="+spec_id+"&number="+number+"&temp="+new Date().getTime(),function(res){
		        if (res.code==1){
		            $("#cartNumber").show().html(res.msg);
		        }
		    },'json');

        });
	}); 
}); 