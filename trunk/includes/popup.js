function repaintmini(tbl) {
	var menuitems = window.opener.document.getElementById(tbl).getElementsByTagName("tr");
	var j = 0;
	for (var i=0; i<menuitems.length; i++) {
		if(menuitems[i].style.display != "none") {
			((j%2) == 0 ? menuitems[i].style.background = "#fff" : menuitems[i].style.background = "#eee");
			j += 1;
		}		
	}
}

var http_request = false;
function postInfo(url, parameters) {

	http_request = false;
	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
		// set type accordingly to anticipated content type
		//http_request.overrideMimeType('text/xml');
			http_request.overrideMimeType('text/html');
		}
	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	if (!http_request) {
		alert('Cannot create XMLHTTP instance');
		return false;
	}
  
	//http_request.onreadystatechange = alertContents;
	http_request.open('POST', url, true);
	http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http_request.setRequestHeader("Content-length", parameters.length);
	http_request.setRequestHeader("Connection", "close");
	http_request.send(parameters);
	
	http_request.onreadystatechange = function() { 
		if (http_request.readyState == 4 && http_request.status == 200) {
			//alert(http_request.responseText);
			//document.getElementById('showinfo').innerHTML = http_request.responseText;
		}
	}
}

