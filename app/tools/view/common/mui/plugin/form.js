/*
表单元素初始及表单验证插件
*/
function muiFormCheck(selector){
	var formIn  = $(selector);
	if(formIn.length != 1){return true;}
	var inputs = formIn.find('input'), hiddens = formIn.find('hidden'), selects = formIn.find('select'), textareas = formIn.find('textarea'); res = true;
	for(var i = 0; i < inputs.length; i++){res = muiFormCheckBase(inputs[i]); if(!res){break;}}
	if(res){for(var i = 0; i < selects.length; i++){res = muiFormCheckBase(selects[i]); if(!res){break;}}}
	if(res){for(var i = 0; i < textareas.length; i++){res = muiFormCheckBase(textareas[i]); if(!res){break;}}}
	if(res){for(var i = 0; i < hiddens.length; i++){res = muiFormCheckBase(hiddens[i]); if(!res){break;}}}	
	if(typeof(huiFormCheckAttach) != 'undefined' && res){if(!huiFormCheckAttach()){return false;}}
	return res;
}
function muiFormCheckBase(obj){
	var checkType  = obj.getAttribute('checkType');
	if(!checkType){return true;}
	var checkData  = obj.getAttribute('checkData');
	var checkMsg   = obj.getAttribute('checkMsg');
	if(!checkMsg){return true;}
	var checkVal   = obj.value;
	switch(checkType){
		case 'required' : 
			checkVal = checkVal.trim();
			if(checkVal==''){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'string' : //字符串长度检查
			checkVal = checkVal.trim();
			var reg  = new RegExp('^.{'+checkData+'}$');
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'int' :
			var reg  = new RegExp('^\-?[0-9]{'+checkData+'}$');
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
			var reg2 = new RegExp('^\-?0+[0-9]+$');
			if(reg2.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'between' : 
			if(!muiFormCheckNumber(checkVal, checkData, checkMsg)){return false;}
		break;
		case 'betweenD' : 
			var reg  = new RegExp('^\-?[0-9]+$');
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
			if(!muiFormCheckNumber(checkVal, checkData, checkMsg)){return false;}
		break;
		case 'betweenF' : 
			var reg  = new RegExp('^\-?[0-9]+\.[0-9]+$');
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
			if(!muiFormCheckNumber(checkVal, checkData, checkMsg)){return false;}
		break;
		case 'same' : 
			if(checkVal != checkData){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'sameWithId' : 
			if(checkVal != $('#'+checkData).val()){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'notSame' : 
			if(checkVal == checkData){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'notSameWithId' :
			if(checkVal == $(checkData).val()){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'email' : 
			var reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'phone' :
			var reg = /^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/;
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'url'  :
			var reg = /^(\w+:\/\/)?\w+(\.\w+)+.*$/;
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'sn'  :
			if (checkVal!=''){
				var reg = /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}[0-9Xx]$)/;
				if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
			}			
		break;
		case 'chinese'  :
			var reg = /^[\u4E00-\u9FA5]{1,5}$/;
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'zipcode'  :
			var reg = /^[0-9]{6}$/;
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'reg'  : 
			var reg = new RegExp(checkData);
			if(!reg.test(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
		break;
		case 'fun'  : 
			eval('var res = '+checkData+'("'+checkVal+'");');
			if(!res){return muiFormCheckShowErrMsg(checkMsg);}
		break;
	}
	return true;
}
function muiFormCheckNumber(checkVal, checkData, checkMsg){
	checkVal = Number(checkVal); if(isNaN(checkVal)){return muiFormCheckShowErrMsg(checkMsg);}
	checkDataArray = checkData.split(',');
	if(checkDataArray[0] == ''){
		if(checkVal > Number(checkDataArray[1])){return muiFormCheckShowErrMsg(checkMsg);}
	}else if(checkDataArray[1] == ''){
		if(checkVal < Number(checkDataArray[0])){return muiFormCheckShowErrMsg(checkMsg);}
	}else{
		if(checkVal < Number(checkDataArray[0]) || checkVal > Number(checkDataArray[1])){return muiFormCheckShowErrMsg(checkMsg);}
	}
	return true;
}

function muiFormCheckShowErrMsg(checkMsg){mui.toast(checkMsg); return false;}

mui.getFormData = function(formId, reType){
	mui.formTpmValForChecked = {};
	if(formId.substr(0, 1) == '#'){formId = formId.substr(1, formId.length - 1);}
	if(reType == undefined){reType = 'obj';}
	var elements = mui.formGetElements(formId);
	var queryComponents = new Array();
	var returnObj = {};
	for (var i = 0; i < elements.length; i++) {
		var queryComponent = mui.serializeElement(elements[i]);
		if(queryComponent){queryComponents.push(queryComponent);}
	}
	var restr = queryComponents.join('&');
	if(reType != 'obj'){return restr;}
	var arrExplode = restr.split('&');
	for(var i = 0; i < arrExplode.length; i++){
		var cArr = arrExplode[i].split('=');
		eval('returnObj.'+cArr[0]+' = decodeURIComponent(cArr[1]);');
	}
	return returnObj;
}
mui.serializeElement = function(elementObj){
	var method = elementObj.tagName.toLowerCase();
	var parameter = mui.getInputs(elementObj, method);
	if(parameter){
		if(parameter[0].substr(-2) == '[]'){
			var subName = parameter[0].substr(0, parameter[0].length - 2);
			if(mui.formTpmValForChecked.subName != undefined){
				mui.formTpmValForChecked.subName += ','+parameter[1];
			}else{
				mui.formTpmValForChecked.subName = parameter[1];
			}
			var key = subName;
			return key + '=' + encodeURIComponent(mui.formTpmValForChecked.subName);
		}
		var key = encodeURIComponent(parameter[0]);
		return key + '=' + encodeURIComponent(parameter[1]);
	}else{
		return false;
	}
}

mui.getInputs = function(elementObj, method) {
	if(elementObj.name == ''){return false;}
	if(method == 'textarea' || method == 'select'){return [elementObj.name, elementObj.value];}
	switch (elementObj.type.toLowerCase()){  
		case 'submit':
		case 'hidden':
	   	case 'password':
		case 'text':
		case 'number':
		case 'email':
		case 'tel':
		case 'url':
		return [elementObj.name, elementObj.value];  
		case 'checkbox':
		case 'radio':
		return mui.inputSelector(elementObj);  
	}
	return false;
}

mui.inputSelector = function(elementObj){
	if(elementObj.checked){return [elementObj.name, elementObj.value];}
	return false;
}

mui.formGetElements = function (formId) {  
	var form = document.getElementById(formId);
	var elements = new Array();
	var tagElements = form.getElementsByTagName('input');
	for (var j = 0; j < tagElements.length; j++){elements.push(tagElements[j]);}
	tagElements = form.getElementsByTagName('textarea');
	for (var j = 0; j < tagElements.length; j++){elements.push(tagElements[j]);}
	tagElements = form.getElementsByTagName('select');
	for (var j = 0; j < tagElements.length; j++){elements.push(tagElements[j]);}
	return elements;
}