//dtn:  Set up the Ajax connections
function startAjax() {
	var ajax = false;
	try { 
		ajax = new XMLHttpRequest(); // Firefox, Opera 8.0+, Safari
	} catch (e) {
	    // Internet Explorer
	    try { ajax = new ActiveXObject("Msxml2.XMLHTTP");
	    } catch (e) {
			try { ajax = new ActiveXObject("Microsoft.XMLHTTP");
	        } catch (e) {
	        	alert("Your browser does not support AJAX!");
	        }
	    }
	}
	return ajax;
}

//dtn: This is the revert for the Warning Overlay page... it turns it from the dark background back to the normal view.
function revert() {
	document.getElementById('overlaypage').style.display = "none";
	document.getElementById('warning').style.display = "block";
}

//dtn: This is the warning window.  It sets up the gay overlay background with the window in the middle asking if you are sure you want to deleted whatever.
function warning(id,val1,val2) {
	if (!val2) { val2 = ""; }
	// This specifically finds the height of the entire internal window (the page) that you are currently in.
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}

	// This specifically find the SCROLL height.  Example, you have scrolled down 200 pixels
	if( typeof( window.pageYOffset ) == 'number' ) {
		//Netscape compliant
		scrOfY = window.pageYOffset;
		scrOfX = window.pageXOffset;
	} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		//DOM compliant
		scrOfY = document.body.scrollTop;
		scrOfX = document.body.scrollLeft;
	} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		//IE6 standards compliant mode
		scrOfY = document.documentElement.scrollTop;
		scrOfX = document.documentElement.scrollLeft;
	} else {
		scrOfY = 0;
		scrOfX = 0;
	}

	// document.body.scrollHeight <-- Finds the entire SCROLLable height of the document.
	if (window.innerHeight && window.scrollMaxY) { // Firefox
		document.getElementById('gray').style.height = (window.innerHeight + window.scrollMaxY) + "px";
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		document.getElementById('gray').style.height = yWithScroll = document.body.scrollHeight + "px";
	} else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
		document.getElementById('gray').style.height = document.body.scrollHeight + "px";
  	}

	document.getElementById('gray').style.width = (myWidth + scrOfX) + "px";
	
//	if(scrOfY != 0) {
		document.getElementById('message').style.top = scrOfY+"px";
//	} 
	
	document.getElementById('delName').innerHTML = val1;
	document.getElementById('idDel').value = id;
	document.getElementById('chrTablePrefix').value = val2;
	document.getElementById('overlaypage').style.display = "block";
}

//dtn: This is the basic delete item script.  It uses GET's instead of Posts
function delItem(address) {
	var id = document.getElementById('idDel').value;
	var tblpre = "";
	var tblpre = document.getElementById('chrTablePrefix').value;
	ajax = startAjax();
	
	if(ajax) {
		ajax.open("GET", address + id);
	
		ajax.onreadystatechange = function() { 
			if(ajax.readyState == 4 && ajax.status == 200) { 
				showNotice(id,ajax.responseText,tblpre);
			} 
		} 
		ajax.send(null); 
	}
} 

//dtn: This is used to erase a line from the sort list.
function showNotice(id, type, tblpre) {
	document.getElementById(tblpre + 'tr' + id).style.display = "none";
	repaint(tblpre);
	revert();
}

//dtn: This is the quick delete used on the sort list pages.  It's the little hoverover x on the right side.
function quickdel(address, idEntity, fatherTable, attribute) {
	ajax = startAjax();
	
	if(ajax) {
		ajax.open("GET", address);
	
		ajax.onreadystatechange = function() { 
			if (ajax.readyState == 4 && ajax.status == 200) { 
				//alert(ajax.responseText);
				document.getElementById(fatherTable + 'tr' + idEntity).style.display = "none";
				repaintmini(fatherTable);
			} 
		} 
		ajax.send(null); 
	}
} 

function invite_guests(BF,id,guests) {
	ajax = startAjax();
	if(guests=='') { guests=0; }
	var address = BF + "ajax_delete.php?postType=updateguests&id=" + id + "&intGuests=" + guests;

	if(ajax) {
		ajax.open("GET", address);	
		ajax.send(null); 
	}
}

function invite_status(BF,id,status) { 
	ajax = startAjax();
	var address = BF + "ajax_delete.php?postType=updatestatus&id=" + id + "&idStatus=" + status;

	if(ajax) {
		ajax.open("GET", address);
		ajax.send(null); 
	}
}

function contact_status(BF,id,status) { 
	ajax = startAjax();
	var address = BF + "ajax_delete.php?postType=updatecontactstatus&id=" + id + "&idStatus=" + status;

	if(ajax) {
		ajax.open("GET", address);
		ajax.send(null); 
	}
}

function invite_type(BF,id,bType) {
	ajax = startAjax();
	var address = BF + "ajax_delete.php?postType=updateinvitetype&id=" + id + "&bType=" + bType;

	if(ajax) {
		ajax.open("GET", address);	
		ajax.send(null); 
	}
}

//dtn: Quick associations is an ajax method for entering information into the association table
function quickassoc(address) {
	ajax = startAjax();
		
	if(ajax) {
		ajax.open("GET", address);
		//alert(address);
		ajax.onreadystatechange = function() { 
			if(ajax.readyState == 4 && ajax.status == 200) { 
				//alert(ajax.responseText);

			} 
		} 
		ajax.send(null); 
	}
} 

//dtn: Function added to get rid of the first line in the sort columns if there are no values in the sort table yet.
//		Ex: "There are no People in this table" ... that gets erased and replaced with a real entry
function noRowClear(fatherTable) {
	var val = document.getElementById(fatherTable).getElementsByTagName("tr");
	if(val.length <= 2 && val[1].innerHTML.length < 100) {
		var tmp = val[0].innerHTML
		document.getElementById(fatherTable).innerHTML = "";
		document.getElementById(fatherTable).innerHTML = tmp;
	}
}

//dtn: This is a javascript repainter. It sets the columns colors after you do an insert or delete so the colors alternate.
//		There is a check in there to make sure any hidden variables are NOT counted
function repaintmini(fatherTable) {
	var menuitems = document.getElementById(fatherTable).getElementsByTagName("tr");
	var j = 0;
	for (var i=1; i<menuitems.length; i++) {
		if(menuitems[i].style.display != "none") {
			((j%2) == 0 ? menuitems[i].className = "ListLineOdd" : menuitems[i].className = "ListLineEven");
			j += 1;
		}		
	}
}

Array.prototype.inArray = function (value) {
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] === value) {
			return true;
		}
	}
	return false;
};

function IsNumeric(sText) {
	var ValidChars = "0123456789";
	var IsNumber=true;
	var Char;
	for (i = 0; i < sText.length && IsNumber == true; i++) { 
		Char = sText.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
			IsNumber = false;
		}
	}
	return IsNumber;
}