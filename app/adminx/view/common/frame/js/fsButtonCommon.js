/**
 * @Description: 按钮拓展配置
 * @Copyright: 2017 www.fallsea.com Inc. All rights reserved.
 * @author: fallsea
 * @version 1.8.0
 * @License：MIT
 */
layui.define(['fsConfig'], function (exports) {

	var fsConfig = layui.fsConfig,
	statusName = $.result(fsConfig,"global.result.statusName","errorNo"),
	msgName = $.result(fsConfig,"global.result.msgName","errorInfo"),
	dataName = $.result(fsConfig,"global.result.dataName","results.data"),
	successNo = $.result(fsConfig,"global.result.successNo","0"),
	FsButtonCommon = function (){

	};

	FsButtonCommon.prototype.move = function(elem,data,datagrid){
		if (data.length==0){
			fsCommon.warnMsg("请选择需要操作的数据！");
			return;
		};
		var _this = elem;
		var _url = _this.attr("url");					
		var _title = _this.attr("topTitle");
		var _width = _this.attr("topWidth");
		var _height = _this.attr("topHeight");
		var isMaximize = _this.attr("isMaximize");
		var inputs = _this.attr("inputs");	
		var _tableId = _this.attr("tableId");				

		if($.isEmpty(_url)) {
			fsCommon.warnMsg("url地址为空！");
			return false;
		}
		var pram = '';
		$.each(data, function(i, value) {
			if (pram==''){
				pram += value.goodsid;
			}else{
				pram += "-"+value.goodsid;
			}
		});
		_url += "&"+inputs+"="+pram;	

		fsCommon.open(_title, _width, _height, _url, function() {
			datagrid.refresh();
		}, isMaximize);

		//alert("测试自定义按钮"+datagrid+JSON.stringify(data));
	}

	FsButtonCommon.prototype.articleMove = function(elem,data,datagrid){
		if (data.length==0){
			fsCommon.warnMsg("请选择需要操作的数据！");
			return;
		};
		var _this = elem;
		var _url = _this.attr("url");					
		var _title = _this.attr("topTitle");
		var _width = _this.attr("topWidth");
		var _height = _this.attr("topHeight");
		var isMaximize = _this.attr("isMaximize");
		var inputs = _this.attr("inputs");	
		var _tableId = _this.attr("tableId");				

		if($.isEmpty(_url)) {
			fsCommon.warnMsg("url地址为空！");
			return false;
		}
		var pram = '';
		$.each(data, function(i, value) {
			if (pram==''){
				pram += value.id;
			}else{
				pram += "-"+value.id;
			}
		});
		if(_url.indexOf('?') == -1) {
			_url += "?"+inputs+"="+pram;
		} else {
			_url += "&"+inputs+"="+pram;
		}

		fsCommon.open(_title, _width, _height, _url, function() {
			datagrid.refresh();
		}, isMaximize);
	}

	FsButtonCommon.prototype.test = function(elem,data,datagrid){
		alert("测试自定义按钮"+datagrid+JSON.stringify(data));
	}


	var fsButtonCommon = new FsButtonCommon();
	exports('fsButtonCommon', fsButtonCommon);
});
