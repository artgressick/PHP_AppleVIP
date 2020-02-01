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
	
	
	$q = "SELECT I.invite_id AS ID, S.name_id AS idUser, I.viprivate_id AS idContact, I.event_id AS idShow, I.guests as intGuests, I.status_id as idStatus, (I.vip_type_id -1) AS bType, I.create_dt
			FROM in_invites AS I
			JOIN in_sponsors as S ON I.sponsor_id=S.sponsor_id
			ORDER BY ID
			";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
	
		if(mysql_query("
			INSERT INTO Invites SET 
			ID = ". $row['ID'] .",
			bType = ". $row['bType'] .",
			idUser = ". $row['idUser'] .",
			idContact = '". $row['idContact'] ."',
			idShow = '". $row['idShow'] ."',
			intGuests = '". $row['intGuests'] ."',
			idStatus = ". $row['idStatus'] .",
			dtStamp = '". $row['create_dt'] ."'
		")) {
		echo $row['ID'].' Inserted Successfully.<br />'; } else { echo 'Error inserting: '.$row['ID'].'<br />'; } 
	}
	
?>