utils = {
    setParam : function (name,value){
        localStorage.setItem(name,value)
    },
    getParam : function(name){
        return localStorage.getItem(name)
    }
}

product={
    id:0,
    goodsID:"",
    itemID:"",
    server:"",
    name:"",
    short:"",
    picname:"",
    number:0,
    goodsNumber:0,
    price:0.00,
    typeID:0,    
    exts:'',
    flag:0,
};
cart = {
    //向购物车中添加商品
    addproduct:function(product){
        var ShoppingCart = utils.getParam("ShoppingCart");
        if(ShoppingCart==null||ShoppingCart==""){
			//第一次加入商品
            var jsonstr = {"productlist":[{"goodsID":product.goodsID,"itemID":product.itemID,"server":product.server,"name":product.name,"short":product.short,"number":product.number,"goodsNumber":product.goodsNumber,"price":product.price,"typeID":product.typeID,"picname":product.picname,"exts":product.exts,"flag":product.flag}],"totalNumber":product.number,"totalAmount":(product.price*product.number)};
            utils.setParam("ShoppingCart","'"+JSON.stringify(jsonstr));
        }else{
            var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
            var productlist = jsonstr.productlist;
            var result=false;
			//查找购物车中是否有该商品
            for(var i in productlist){
                if(productlist[i].itemID==product.itemID && productlist[i].typeID==product.typeID){
                    productlist[i].number=parseInt(productlist[i].number)+parseInt(product.number);
                    productlist[i].goodsNumber=parseInt(productlist[i].goodsNumber)+parseInt(product.goodsNumber);
                    result = true;
                }
            }
            if(!result){
				//没有该商品就直接加进去
                productlist.push({"goodsID":product.goodsID,"itemID":product.itemID,"server":product.server,"name":product.name,"short":product.short,"number":product.number,"goodsNumber":product.goodsNumber,"price":product.price,"typeID":product.typeID,"picname":product.picname,"exts":product.exts,"flag":product.flag});
            }
			//重新计算总价
            jsonstr.totalNumber=parseInt(jsonstr.totalNumber)+parseInt(product.number);
            jsonstr.totalAmount=parseFloat(jsonstr.totalAmount)+(parseInt(product.number)*parseFloat(product.price));
            //保存购物车
            utils.setParam("ShoppingCart","'"+JSON.stringify(jsonstr));
        }
    },

    //修改给买商品数量
    updateproductnumber:function(id,number){
        var ShoppingCart = utils.getParam("ShoppingCart");
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        
        for(var i in productlist){
            if(productlist[i].itemID==itemID){
                jsonstr.totalNumber=parseInt(jsonstr.totalNumber)+(parseInt(number)-parseInt(productlist[i].number));
                jsonstr.totalAmount=parseFloat(jsonstr.totalAmount)+((parseInt(number)*parseFloat(productlist[i].price))-parseInt(productlist[i].number)*parseFloat(productlist[i].price));
                productlist[i].number=parseInt(number);  
                utils.setParam("ShoppingCart","'"+JSON.stringify(jsonstr));
                return;
            }
        }
    },

    //获取购物车中的所有商品
    getproductlist:function(){
        var ShoppingCart = utils.getParam("ShoppingCart");
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        return productlist;
    },

    //清空
    clear:function(){
        var ShoppingCart = utils.getParam("ShoppingCart");
        if(ShoppingCart!=null&&ShoppingCart!=""){
            var ShoppingCart = utils.getParam("ShoppingCart");
            var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
            var productlist = jsonstr.productlist;
            $.each(productlist,function(index,data){        
                $(".checkNumber").each(function(){   
                    if ($(this).attr('item-id')==data.id){
                        n = parseInt($(this).html());
                        $(this).html(n+parseInt(data.number));
                    }
                });  
            })
            var list=[];
            jsonstr.productlist = list;
            utils.setParam("ShoppingCart","'"+JSON.stringify(jsonstr));
        }
    },

    //渲染
    render:function(){
        var flag = 0;
        var ShoppingCart = utils.getParam("ShoppingCart");
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        _html = '';
        $.each(productlist,function(index,data){
            if (data.flag==1){
                flag = 1;
            }
            _html += '<tr id="peronCart'+data.itemID+'">';
            _html += '<td><img src="'+data.picname+'" style="height:50px"/></td>';
            _html += '<td style="text-align:left">'+data.name+data.exts+'</td>';
            _html += '<td>'+data.number+'</td>';
            _html += '</tr>';    
        })
        $('#goods').html(_html);
        if (flag==0){
            $("#signBox").hide();
        }else{
            $("#signBox").show();
        }        
    },

    //判断购物车中是否存在商品
    existproduct:function(product){
        var ShoppingCart = utils.getParam("ShoppingCart");
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        var result=false;
        for(var i in productlist){
            if(productlist[i].itemID==product.itemID){
                result = true;
            }
        }
        return result;
    },

    //删除购物车中商品
    deleteproduct:function(product){
        var ShoppingCart = utils.getParam("ShoppingCart");
        var jsonstr = JSON.parse(ShoppingCart.substr(1,ShoppingCart.length));
        var productlist = jsonstr.productlist;
        var list=[];
        for(var i in productlist){
            if(productlist[i].itemID==product.itemID){
                productlist[i].number = parseInt(productlist[i].number)-product.number;
                productlist[i].goodsNumber = parseInt(productlist[i].goodsNumber)-product.goodsNumber;
                if (productlist[i].number > 0){
                    list.push(productlist[i]);
                }
            }else{
                list.push(productlist[i]);
            }
        }
        jsonstr.productlist = list;
        utils.setParam("ShoppingCart","'"+JSON.stringify(jsonstr));
    }
};
