/**
 * @Description: 字典配置
 * @Copyright: 2017 www.fallsea.com Inc. All rights reserved.
 * @author: fallsea
 * @version 1.8.0
 * @License：MIT
 */
layui.fsDict = {

	//用户状态
	status : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"禁用","style":"color:#F00;"},
			{"value":1,"name":"启用"}
		]
	},

	//会员状态
	disable : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"启用"},
			{"value":1,"name":"禁用","style":"color:#F00;"}
		]
	},

	//会员状态
	print : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"未打印"},
			{"value":1,"name":"已打印","style":"color:blue;"}
		]
	},

	//结算状态
	snStatus : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"已上传"},
			{"value":0,"name":"未上传","style":"color:red;"}
		]
	},

	//颜色
	color : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":"#FF5722","name":"红色","style":"color:#FF5722;"},
			{"value":"#1E9FFF","name":"蓝色","style":"color:#1E9FFF;"},
			{"value":"#009688","name":"绿色","style":"color:#009688;"}
		]
	},

	//商品状态
	goodsComm : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"是","style":"color:blue;"},
			{"value":0,"name":"","style":"color:red;"}
		]
	},

	//商品状态
	goodsShow : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"显示"},
			{"value":0,"name":"隐藏","style":"color:red;"}
		]
	},

	//支付状态
	payStatus : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"待付款"},
			{"value":1,"name":"待审核","style":"color:red;"},
			{"value":2,"name":"待配货","style":"color:gold;"},
			{"value":3,"name":"配货中","style":"color:blue;"},
			{"value":4,"name":"已发货","style":"color:green;"},
			{"value":99,"name":"取消订单","style":"color:red;"}
		]
	},

	//商品运费
	baoyou : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"不包邮"},
			{"value":1,"name":"包邮"}
		]
	},

	//支付方式
	payType : {
		formatType : "server",
		loadUrl : "/index.php?s=Adminx/Admin/getPayType",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//商品模型
	goodsType : {
		formatType : "server",
		loadUrl : "/index.php?s=Adminx/Admin/getGoodsType",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//品牌
	brand : {
		formatType : "server",
		loadUrl : "/index.php?s=Adminx/Admin/getBrand",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//包裹类型
	baoguo : {
		formatType : "server",
		loadUrl : "/index.php?s=Adminx/Admin/getBaoguo",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//用户身份
	memberGroup : {
		formatType : "server",
		loadUrl : "/index.php?s=Adminx/Admin/getMemberGroup",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//用户组
	group : {
		formatType : "server",
		loadUrl : "/index.php?s=Adminx/Admin/getGroup",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//财务类型
	finance : {
		formatType : "server",
		loadUrl : "/index.php/adminx/admin/getfinance.html",
		method : "post",
		labelField : "name",
		valueField : "id"
	}
};
