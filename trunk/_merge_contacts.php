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
	
	
	$q = "SELECT VP.viprivate_id AS ID,V.vip_id,S.name_id as idUser,V.first_name AS chrFirst, V.last_name as chrLast, V.company as chrCompany,
			(VP.vip_type_id -1) AS bType, VP.email as chrEmail, address as chrAddress1, address2 as chrAddress2, city as chrCity, 
			state as chrState, zip as chrPostalCode, country as chrCountry, work as chrPhone, cell as chrMobile, fax as chrFax, 
			home as chrAltPhone, url as chrURL, alias as chrAlias
			FROM in_sponsors AS S
			JOIN in_viprivates AS VP ON S.sponsor_id = VP.sponsor_id
			JOIN in_vips AS V ON V.vip_id = VP.vip_id
			ORDER BY ID
			";

	$results = mysql_query($q);

	while($row = mysql_fetch_assoc($results)) {
		mysql_query("
			INSERT INTO Contacts SET 
			ID = ". $row['ID'] .",
			vip_id = ". $row['vip_id'] .",
			idUser = ". $row['idUser'] .",
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
	
?>