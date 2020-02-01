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
	
	$q = "SELECT in_viprivates.vip_id, in_viprivate_asst.* FROM in_viprivate_asst JOIN in_viprivates ON in_viprivate_asst.viprivate_id = in_viprivates.viprivate_id";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
		if(mysql_query("
			UPDATE Contacts SET
			bAltEmail='".$row['use_for_email']."',
			bAltMail='".$row['use_for_postal']."',
			chrAltFirst='".encode($row['first_name'])."',
			chrAltLast='".encode($row['last_name'])."',
			chrAltTitle='".encode($row['title'])."',
			chrAltAddress1='".encode($row['address'])."',
			chrAltAddress2='".encode($row['address2'])."',
			chrAltCity='".encode($row['city'])."',
			chrAltState='".encode($row['state'])."',
			chrAltPostalCode='".encode($row['zip'])."',
			chrAltCountry='".encode($row['country'])."',
			chrAltPhone='".encode($row['work'])."',
			chrAltMobile='".encode($row['cell'])."',
			chrAltFax='".encode($row['fax'])."',
			chrAltEmail='".encode($row['email'])."'
			WHERE Contacts.vip_id = ".$row['vip_id']."
		")) {
			echo $row['first_name'].' '.$row['last_name'].' added successfully<br />';
		} else {
			echo $row['first_name'].' '.$row['last_name'].' had an error<br />';
		}
	}
?>