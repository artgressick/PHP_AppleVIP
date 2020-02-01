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
	
	$q = "
			SELECT DISTINCT idUser,idShow, (SELECT COUNT(ID) FROM Invites AS I WHERE I.idUser=Invites.idUser and I.idShow=Invites.idShow) + 5 as intInvites
			FROM Invites
			";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
		
		mysql_query("
			INSERT INTO SponsorsbyShow SET 
			idUser = ". $row['idUser'] .",
			idShow = '". $row['idShow'] ."',
			intInvites = '". $row['intInvites'] ."',
			idReviewStatus = 1
		");
		echo $row['idUser'].' Inserted Successfully.<br />';
	}
	
?>