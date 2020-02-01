<?php
// INSERT PROJECT NAME HERE
	session_name('applevip');
	session_start();
	
	require_once("Spreadsheet/Excel/Writer.php");
	
	include('../applevip-conf.php');
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
	$q = "SELECT COUNT(C.ID) AS intCount, SUM(C.intGuests) AS intGuests 
			FROM Checkin AS C
			JOIN Invites AS I ON C.idInvite=I.ID
			JOIN Contacts AS CO ON I.idContact=CO.ID AND !CO.bDeleted
			JOIN Users AS U ON I.idUser=U.ID AND !U.bDeleted
			WHERE !I.bDeleted AND C.idShow='".$_REQUEST['id']."'";
	$totalcheckedin = mysqli_fetch_assoc(mysqli_query($mysqli_connection, $q));
	$totalcheckedin['intTotal'] = $totalcheckedin['intCount'] + $totalcheckedin['intGuests'];	
	
	$q = "SELECT COUNT(I.ID) AS intInvites, SUM(I.intGuests) AS intGuests 
			FROM Invites AS I 
			JOIN Contacts AS CO ON I.idContact=CO.ID AND !CO.bDeleted
			JOIN Users AS U ON I.idUser=U.ID AND !U.bDeleted
			WHERE !I.bDeleted AND I.idShow='".$_REQUEST['id']."' AND I.idStatus IN (2,5,6,8,9)";
	$totalinvited = mysqli_fetch_assoc(mysqli_query($mysqli_connection, $q));
	$totalinvited['intTotal'] = $totalinvited['intInvites'] + $totalinvited['intGuests'];		
	
		
	$q = "SELECT I.ID, CONCAT(CO.chrFirst,' ',CO.chrLast) AS chrContact, CONCAT(U.chrFirst,' ',U.chrLast) AS chrUser, I.intGuests, if(C.intGuests >= 0,C.intGuests,'N/A') AS intGuestsCheckedin, if(C.dtStamp != '',C.dtStamp,'N/A') as dtStamp
			FROM Invites AS I
			LEFT JOIN Checkin AS C ON C.idInvite=I.ID
			JOIN Contacts AS CO ON I.idContact=CO.ID
			JOIN Users AS U ON I.idUser=U.ID
			WHERE !I.bDeleted AND I.idShow='" . $_REQUEST['id'] . "' AND I.idStatus IN (2,5,6,8,9)
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		  
	$result = mysqli_query($mysqli_connection,$q);
	
	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$showinfo = mysqli_fetch_assoc(mysqli_query($mysqli_connection, "SELECT chrName FROM Shows WHERE ID='".$_REQUEST['id']."'"));
	$filename  = str_replace(" ", "_", decode($showinfo['chrName']) );
	
	$workbook->send($filename .'_Checkin_Report('. $time .').xls');	
	
	// create format for column headers
	$format_column_header =& $workbook->addFormat();
	$format_column_header->setBold();
	$format_column_header->setSize(10);
	$format_column_header->setAlign('left');
	
	// create data format
	$format_data =& $workbook->addFormat();
	$format_data->setSize(10);
	$format_data->setAlign('left');
	
	function decode($val) {
		$val = str_replace('&quot;','"',$val);
		$val = str_replace("&apos;","'",$val);
		return $val;
	}
	
	
	// Create worksheet
	$worksheet =& $workbook->addWorksheet($filename.'_Check_In');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

	
	$column_num = 0;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Total Invites:', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, $totalinvited['intTotal'], $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Total Checked In:', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, $totalcheckedin['intTotal'], $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, '', $format_column_header);
	$column_num++;
	
	$row_num++;
	$column_num = 0;
	$worksheet->write($row_num, $column_num, 'Contact', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Sponsor', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Guests Invited', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Guests Checked In', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Checked In', $format_column_header);
	$column_num++;
	
	$row_num++;
		
	while($row = mysqli_fetch_assoc($result)) {

		$column_num = 0;
		$worksheet->write($row_num, $column_num, decode($row['chrContact']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrUser']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['intGuests']), $format_data);
		$column_num++;			
		$worksheet->write($row_num, $column_num, decode($row['intGuestsCheckedin']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, ($row['dtStamp'] != '' ? date('n/j/Y g:i a',strtotime($row['dtStamp'])) : 'N/A'),$format_data);
		$column_num++;	
		$row_num++;
	}

$workbook->close();
	
?>
