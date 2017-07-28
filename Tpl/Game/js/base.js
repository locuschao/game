/**
 * 
 */
// txt文字 ，shadow遮罩，time显示时间
function tip(txt, shadow, time, type) {
	var op = document.getElementsByClassName('showTip');
	if (op.length > 0) {
		for (var i = 0; i < op.length; i++) {
			document.body.removeChild(op[i]);
		}
	}
	var div = document.createElement("div");
	var toast = "";
	if (type == 1) {
		toast = '<span class="mui-spinner"></span>';
	}
	div.innerHTML = '<div id="tip"  style="padding:0px 20px; height:50px; line-height:50px;text-align:center;z-index:9999;background:rgba(0,0,0,0.5);top:20px;color:#fff; left:30px; border-radius:5px;position:fixed;">'
			+ toast + txt + '</div>';
	div.setAttribute("class", "showTip");
	document.body.appendChild(div);
	// 获取DIV为‘box’的盒子
	var oBox = document.getElementById('tip');
	// 获取元素自身的宽度
	var L1 = oBox.offsetWidth;
	// 获取元素自身的高度
	var H1 = oBox.offsetHeight;
	if (shadow == 1) {
		// 显示遮罩
		div.style.zIndex = 99909;
		div.style.width = screen.availWidth + 'px';
		div.style.height = screen.availHeight + 'px';
		div.style.position = 'fixed';
		div.style.left = '0px';
		div.style.top = '0px';
		div.style.backgroundColor = 'rgba(0,0,0,0.5)';
	}
	setTimeout(function() {
		div.style.display = "none";
	}, time * 1000);
	var Left = (screen.availWidth - L1) / 2;
	// 获取实际页面的top值。（页面宽度减去元素自身高度/2）
	var top = (screen.availHeight - H1) / 2;
	oBox.style.left = Left + 'px';
	oBox.style.top = top + 'px';
	document.body.scrollTop = 0;
	// 当浏览器页面发生改变时，DIV随着页面的改变居中。
	window.onresize = function() {
		tip();
	}

}

// 关闭提示框
function xtip() {
	var op = document.getElementsByClassName('showTip');
	if (op.length > 0) {
		for (var i = 0; i < op.length; i++) {
			document.body.removeChild(op[i]);
		}
	}
}

function toDecimal(x) {
	var f = parseFloat(x);
	if (isNaN(f)) {
		return;
	}
	f = Math.round(x * 100) / 100;
	return f;
}

/**
 * 判断是否是空
 * @param value
 */
function isDefine(value) {
  if (value === null || value === "" || value === "undefined" || value === undefined || value === "null" || value === "(null)" || value === 'NULL' || typeof(value) === 'undefined') {
    return false;
  } else {
    value = value + "";
    value = value.replace(/\s/g, "");
    if (value === "") {
      return false;
    }
    return true;
  }
}