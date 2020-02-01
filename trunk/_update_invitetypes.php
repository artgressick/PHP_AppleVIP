<?php
	require('applevip-conf.php');
	
	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	$q = "SELECT ID, bType FROM Contacts";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
		$q = "UPDATE Invites SET 
			bType = '". $row['bType'] ."'
			WHERE idContact='".$row['ID']."'
		";
		if(mysql_query($q)) {
		echo $row['ID'].' Update Successfully. '.$q.'<br />';
		}
	}
	
?>