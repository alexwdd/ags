/**
 * @Description: 主页面
 * @Copyright: 2017 www.fallsea.com Inc. All rights reserved.
 * @author: fallsea
 * @version 1.8.0
 * @License：MIT
 */
layui.use(['fsMenu','layer','fsTab','fsCommon','fsConfig'], function(){
	var fsTab = layui.fsTab,
	fsConfig = layui.fsConfig,
	fsCommon = layui.fsCommon,
	fsMenu = layui.fsMenu;
	layer = layui.layer;
	fsMenu.render();

	//初始化显示菜单
	showMenu($("#fsTopMenu li.layui-this").attr("dataPid"));

	if (window.attachEvent) {
	  window.attachEvent("hashchange", hashChanged);
	} else if (window.addEventListener) {
		window.addEventListener("hashchange", hashChanged, false);
	}

	hashChanged();

	function hashChanged(){
		//获取路由信息
		var hash = window.location.hash;
		if(!$.isEmpty(hash) && hash.length>1){
			var menuId = hash.substring(1);
			//获取layId
			var dom = $('#fsLeftMenu a[menuId="'+ menuId +'"]').parent();
			if(dom.length>0){
				fsTab.add(dom);
				fsTab.menuSelectCss(dom.attr("lay-id"));

			}
		}
	}


	$("#fsTopMenu").on("click","li",function(){
		var dataPid = $(this).attr("dataPid");
		showMenu(dataPid);
	});


	//显示菜单
	function showMenu(dataPid){
		if(!$.isEmpty(dataPid)){
			$('#fsLeftMenu>li').hide();
			$('#fsLeftMenu>li[dataPid="'+ dataPid +'"]').show();
		}
	}

	//渲染tab
	fsTab.render();

	//新增tab
	function addTab(elem){
		fsTab.add(elem);
	}

	//手机设备的简单适配
	var treeMobile = $('.site-tree-mobile'),
		shadeMobile = $('.site-mobile-shade')

	treeMobile.on('click', function(){
		$('body').addClass('site-mobile');
	});

	shadeMobile.on('click', function(){
		$('body').removeClass('site-mobile');
	});


	//菜单绑定

	$(".fsSwitchMenu").on("click",function(){
		if($(this).find("i.fa-outdent").length>0){
			$(this).find("i").removeClass("fa-outdent").addClass("fa-indent");
		}else{
			$(this).find("i").removeClass("fa-indent").addClass("fa-outdent");
		}
	 	$(".layui-layout-admin").toggleClass("showMenu");
	});


	/**
	 * 右边菜单
	 */
	$.contextMenu({
    selector: '.layui-tab-title li',
    callback: function(key, options) {
    	var layId = $(this).attr("lay-id");
    	switch (key) {
				case "close":
					fsTab.del(layId);
					break;
				case "closeOther":

					$(this).parent().children("li").each(function(i,e){

						if($(this).find(".layui-tab-close").is(":visible")){

							var newLayId = $(this).attr("lay-id");
							if(layId != newLayId ){
								fsTab.del(newLayId);
							}
						}
					});
					break;
				case "closeAll":

					$(this).parent().children("li").each(function(i,e){
						if($(this).find(".layui-tab-close").is(":visible")){
							var newLayId = $(this).attr("lay-id");
							fsTab.del(newLayId);
						}
					});
					break;
				default:
					break;
    	}
    },
    items: {
      "close": {name: "关闭标签",icon:"fa-close",disabled: function(){
      	if($(this).find(".layui-tab-close").is(":visible")){
      		return false;
      	}
      	return true;
      }},
      "closeOther": {name: "关闭其他",icon:"fa-ban"},
      "closeAll": {name: "关闭全部",icon:"fa-window-close"}
    }
	});

	$("#clearCache").on("click",function(){
        layer.msg('确定清楚缓存吗？', {
            time:0,
            btn: ['确定', '取消'],
            yes: function(index){
                layer.close(index);
                layer.load(0, {shade:[0.5,'#000'],time:0});
                $.ajax({
                    url:"/index.php?s=Adminx/Index/clearcache",
                    success:function(data){
                        layer.closeAll();
                        if(data.code==1){
                            layer.msg(data.msg, {icon:1});
                        }else{
                            layer.alert(data.msg, {icon:0});
                        }
                    }
                });
            }
        });
	});

	$("#changePwd").on("click",function(){
		var url = $(this).attr("url");
		layer.open({
			type: 2,
			title: '修改密码',
			shadeClose: true,
			shade: 0.8,
			area: ['500px','350px'],
			content: url //iframe的url
		});
	});

	//退出
	$("#signout").on("click",function(){		
		layui.define(["fsCommon"], function(exports){
			url = "/index.php/Adminx/Login/signout";
			statusName = $.result(fsConfig,"global.result.statusName","status");
			successNo = $.result(fsConfig,"global.result.successNo",1);
			msgName = $.result(fsConfig,"global.result.msgName","info");
			fsCommon.invoke(url,{},function(data){
	  			if(data[statusName] == successNo){
	  				fsCommon.successMsg(data[msgName],function(){
	  					window.location.reload();
	  				});
	  			}else{
	  				fsCommon.errorMsg(data[msgName]);
	  			}
	  		});
		});
	});
});
