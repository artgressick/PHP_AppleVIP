function displayInfo(obj,id) {
	// Example of use: 	<div id='show_person_photo141513247' onclick='person_photo(this, "141513247", "<?=$BF?>")'><img src='<?=$BF?>images/person-photo.png' /></div>

	var pos = findPos(obj);
	
	if(document.getElementById("displayInfo"+id).innerHTML.length < 40) {
		document.getElementById("displayInfo"+id).innerHTML = '<iframe style="border: 1px solid blue; position: absolute; left:"+ pos[0] +"px; top: "+ (pos[1] + 16) +"px" id="displayInformation" name="displayInformation" src="userinfo.php?id='+ id +'"></iframe>';
		document.getElementById("displayInfo"+id).style.display = "";
		
	} else {
		document.getElementById("displayInfo"+id).innerHTML = "";
	}
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}