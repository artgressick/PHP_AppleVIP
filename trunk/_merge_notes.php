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
	
	$q = "SELECT create_by, create_dt, note, invite_id FROM in_invite_notes";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
		
		mysql_query("
			INSERT INTO Notes SET 
			idType = 3,
			idUser = ". $row['create_by'] .",
			idRecord = '". $row['invite_id'] ."',
			dtStamp = '". $row['create_dt'] ."',
			txtNote = '".encode($row['note'])."'
		");
		echo $row['note'].' Inserted Successfully.<br />';
	}
	
?>