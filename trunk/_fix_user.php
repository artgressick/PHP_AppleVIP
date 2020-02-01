<?php
	require('applevip-conf.php');
	
	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	$oldUser = 442;
	$newUser = 447;
	
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
	
	
	$q = "SELECT * FROM Contacts WHERE idUser=".$oldUser;

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
		$test = mysql_query("SELECT ID FROM Contacts WHERE REPLACE(chrFirst, ' ', '') = '".str_replace(' ','',$row['chrFirst'])."' AND REPLACE(chrLast, ' ', '') = '".str_replace(' ','',$row['chrLast'])."' AND idUser=".$newUser);
		
		if(mysql_num_rows($test) == 0) { 
			mysql_query("
				INSERT INTO Contacts SET 
				vip_id = ". $row['vip_id'] .",
				idUser = ". $newUser .",
				chrFirst = '". encode($row['chrFirst']) ."',
				chrLast = '". encode($row['chrLast']) ."',
				chrCompany = '". encode($row['chrCompany']) ."',
				bType = '". $row['bType'] ."',
				chrEmail = '". encode($row['chrEmail']) ."',
				chrAddress1 = '". encode($row['chrAddress1']) ."',
				chrAddress2 = '". encode($row['chrAddress2']) ."',
				chrCity = '". encode($row['chrCity']) ."',
				chrState = '". encode($row['chrState']) ."',
				chrPostalCode = '". encode($row['chrPostalCode']) ."',
				chrCountry = '". encode($row['chrCountry']) ."',
				chrPhone = '". encode($row['chrPhone']) ."',
				chrMobile = '". encode($row['chrMobile']) ."',
				chrFax = '". encode($row['chrFax']) ."',
				chrAltPhone = '". encode($row['chrAltPhone']) ."',
				chrURL = '". encode($row['chrURL']) ."',
				chrAlias = '". encode($row['chrAlias']) ."'
			");
			echo encode($row['chrFirst'].' '.$row['chrLast']). ' Inserted Successfully.<br />';
		}
	}
	
?>