<?php
	require('applevip-conf.php');
	
	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);


		$replacements = array(
		'psn'=>'ps
		',
		'n-'=>'
		-',
		'ingn'=>'ing
		',
		'edn'=>'ed
		',
		'tionn'=>'tion
		',
		'tinon'=>'tino
		',
		'lonen'=>'lone
		',
		'headn'=>'head
		',
		'stan'=>'sta
		',
		'ehann'=>'ehan
		',
		'llenn'=>'llen
		',
		'yern'=>'yer
		',
		'nnyn'=>'nny
		',
		'tonn'=>'ton
		',
		'hamn'=>'ham
		',
		' n '=>' 
		',
		'etsn'=>'ets
		',
		'tinn'=>'tin
		'
		);
	
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
		

	$notes = mysql_query("SELECT ID, txtNote FROM Notes");
		
	while($row = mysql_fetch_assoc($notes)) {

		foreach($replacements AS $check => $replace) {
			$row['txtNote'] = str_replace($check,$replace,$row['txtNote']);
		}
		
		$q = "UPDATE Notes SET 
			txtNote = '".encode($row['txtNote'])."'
			WHERE ID='".$row['ID']."'";
		if(mysql_query($q)) {
		echo $row['txtNote'].': Updated Successfully.<br />';
		} else { echo $q.': ERROR.<br />'; }
	}
	
?>
