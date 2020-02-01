<?php
// INSERT PROJECT NAME HERE
	session_name('applevip');
	session_start();
	
	require_once("Spreadsheet/Excel/Writer.php");
	
	include('../applevip-conf.php');
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
	$sponsors = mysqli_query($mysqli_connection, $_SESSION['SponsorQuery']);
	
	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$showinfo = mysqli_fetch_assoc(mysqli_query($mysqli_connection, "SELECT chrName FROM Shows WHERE ID=".$_REQUEST['id']));
	$filename  = str_replace(" ", "_", decode($showinfo['chrName']) );
	
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
	
	
	$tmpStatus = mysqli_query($mysqli_connection, "SELECT ID, chrStatus FROM iStatus WHERE !bDeleted ORDER BY dOrder");
	$iStatus = array();
	while($row = mysqli_fetch_assoc($tmpStatus)) {
		$iStatus[$row['ID']] = $row['chrStatus'];
	}
	
	function decode($val) {
		$val = str_replace('&quot;','"',$val);
		$val = str_replace("&apos;","'",$val);
		return $val;
	}
	
	
	// Create worksheet
	$worksheet =& $workbook->addWorksheet($filename.'_Invites');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

	
	while($row = mysqli_fetch_assoc($sponsors)) {
	$column_num = 0;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Sponsor Name:', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, $row['chrLast'].' '.$row['chrFirst'], $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Invites Allowed:', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, $row['intInvites'], $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Invites Used:', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, $row['intInvitesUsed'] + $row['intGuestsinvited'], $format_column_header);
	$column_num++;
	
	$row_num++;
	$column_num = 0;
	$worksheet->write($row_num, $column_num, 'First Name', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Last Name', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Company', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Title', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Email', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Alt Email', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Category', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Guests', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, 'Status', $format_column_header);
	$column_num++;
	
	$row_num++;
	
			$q = "SELECT Invites.ID as idInvite, Contacts.ID, Contacts.chrFirst, Contacts.chrLast, idStatus, Contacts.chrCompany, Categories.chrCategory, Invites.intGuests, Contacts.chrEmail, Contacts.chrTitle, Contacts.chrAltEmail
				FROM Contacts 
				JOIN Invites ON Invites.idContact=Contacts.ID AND idShow=". $_REQUEST['id'] ."
				LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
				WHERE !Invites.bDeleted AND !Contacts.bDeleted AND Contacts.idUser=".$row['idUser']." AND idStatus LIKE '".$_REQUEST['idStatus']."'";
				
		if ($_SESSION['idSearchWho'] == 2) {
			(isset($_SESSION['chrSearch']) ? $chrSearch = $_SESSION['chrSearch'] : $chrSearch = "" );
			$possiblebadge = ltrim(substr($chrSearch, 5, -3), '0');
			$q .= " AND 
				((lower(Contacts.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
				OR lower(Contacts.chrLast) LIKE '%" . strtolower($chrSearch) . "%' 
				OR lower(chrCompany) LIKE '%" . $chrSearch . "%'
				OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%')
				OR (Contacts.ID = '" . $possiblebadge ."'))";
		}
		$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol']; 

		$invites = mysqli_query($mysqli_connection, $q);
		
		while($rowinvites = mysqli_fetch_assoc($invites)) {

			$column_num = 0;
		
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrFirst']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrLast']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrCompany']), $format_data);
			$column_num++;
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrTitle']), $format_data);
			$column_num++;
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrEmail']), $format_data);
			$column_num++;
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrAltEmail']), $format_data);
			$column_num++;			
			$worksheet->write($row_num, $column_num, decode($rowinvites['chrCategory']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, number_format($rowinvites['intGuests']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $iStatus[$rowinvites['idStatus']], $format_data);
			$column_num++;	
			$row_num++;
		}
	$row_num++;	
	}

$workbook->close();
	
?>
