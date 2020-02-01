<?php
	require('applevip-conf.php');
	
	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	function encode($val,$extra="") {
		if($val == 'N' || $val == '\N') {
			$val = "";
		}
		$val = str_replace("'",'&#39;',stripslashes($val));
		$val = str_replace('"',"&quot;",$val);
		if($extra == "tags") { 
			$val = str_replace("<",'&lt;',stripslashes($val));
			$val = str_replace('>',"&gt;",$val);
		}
		if($extra == "amp") { 
			$val = str_replace("&",'&amp;',stripslashes($val));
		}
		return $val;
	}
	
	
	$q = "SELECT P.vip_id, C.category_id
FROM in_viprivate_category_map AS C
JOIN in_viprivates AS P ON C.viprivate_id=P.viprivate_id
			";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
	
		if(mysql_query("
			UPDATE CONTACTS SET 
			idCategory = '". $row['category_id'] ."'
			WHERE vip_id = '".$row['vip_id']."'
	
		")) {
		echo $row['vip_id'].' Updated Successfully.<br />'; } else { echo 'Error Updating: '.$row['vip_id'].'<br />'; } 
	}
	
?>