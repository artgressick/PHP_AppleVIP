<?php
// INSERT PROJECT NAME HERE
	session_name('applevip');
	session_start();
	
	require_once("Spreadsheet/Excel/Writer.php");
	
	include('applevip-conf.php');

	parse_str(base64_decode($_REQUEST['d']),$info);
	
	if ( $info['key'] != $_SESSION['idUser'] ) { die(); }
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
	$q = "SELECT Invites.ID as idInvite, Invites.intGuests, Contacts.chrFirst, contacts.chrLast, Contacts.chrCompany, Categories.chrCategory, chrStatus, Contacts.chrEmail, Contacts.chrAltEmail
		FROM Contacts 
		LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
		JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $_REQUEST['idShow'] ."'
		JOIN iStatus ON Invites.idStatus=iStatus.ID
		WHERE !Invites.bDeleted AND !Contacts.bDeleted AND Contacts.idUser='".$info['to']."'
		ORDER BY iStatus.dInviteOrder,chrLast,chrFirst";

	$results = mysqli_query($mysqli_connection, $q);
	
	$Show = mysqli_fetch_assoc(mysqli_query($mysqli_connection,"SELECT chrName FROM Shows where !bDeleted AND ID='".$_REQUEST['idShow']."'"));
	$User = mysqli_fetch_assoc(mysqli_query($mysqli_connection,"SELECT chrFirst, chrLast FROM Users WHERE ID='".$info['to']."'"));
	
	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$filename  = str_replace(" ", "_", decode($Show['chrName'].'-'.$User['chrFirst'].'_'.$User['chrLast']));
	
	$workbook->send($filename .'_Invites('. $time .').xls');
	
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
	$worksheet =& $workbook->addWorksheet('Invites List');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

		$column_num = 0;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'First Name', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'Last Name', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'Company', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'Category', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'Guests', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'Status', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, 'E-mail', $format_column_header);
		$column_num++;
		$worksheet->setColumn($column_num, $column_num, 20);
		$worksheet->write($row_num, $column_num, "Assistant's Email Address", $format_column_header);
		$column_num++;
		$row_num++;
	
	while($row = mysqli_fetch_assoc($results)) {
		$column_num = 0;
		$worksheet->write($row_num, $column_num, decode($row['chrFirst']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrLast']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrCompany']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrCategory']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['intGuests']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrStatus']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrEmail']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrAltEmail']), $format_data);
		$column_num++;
		$row_num++;
	}

$workbook->close();
?>