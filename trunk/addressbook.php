<?	
	$BF = '';
	$title = 'Index Page';
	$active = "addressbook";
	require($BF. '_lib.php');
	if($_SESSION['idRight'] == 4) {
		header('Location: '. $BF .'checkin.php');
		die();
	}
	
	include($BF. 'includes/meta.php');
	
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast,chrFirst"; }
	
	if (!isset($_REQUEST['idUser']) && $_SESSION['idRight'] != 3) { $_REQUEST['idUser'] = $_SESSION['idUser']; }
		else if(!isset($_REQUEST['idUser'])) {
			$tmp = fetch_database_query("
				SELECT Users.ID AS idUser
				FROM Users 
				LEFT JOIN SponsorsByUser ON SponsorsByUser.idSponsor=Users.ID 
				WHERE !Users.bDeleted AND SponsorsByUser.idUser='". $_SESSION['idUser'] ."'
				ORDER BY chrLast, chrFirst
				LIMIT 1
			","Getting First user has access to");
			$_REQUEST['idUser'] = $tmp['idUser'];
		} 
if($_REQUEST['idUser'] != '') {	
	if (isset($_REQUEST['d'])) {
		parse_str(base64_decode($_REQUEST['d']),$info);
		if ( $info['key'] != $_SESSION['idUser'] ) { die(); }
		$_REQUEST['idUser'] = $info['to'];
	}
	
	if (isset($_REQUEST['chrSearch'])) {
		$chrSearch = encode($_REQUEST['chrSearch']);
	} else {
		$_REQUEST['chrSearch'] = '';
		$chrSearch = "";
	}

	$possiblebadge = ltrim(substr($chrSearch, 5, -3), '0');
	
	$q = "SELECT C.ID, C.chrFirst,C.chrLast,C.chrEmail,C.chrCompany, C.bType, Cat.chrCategory
		FROM Contacts AS C
		LEFT JOIN Categories AS Cat ON C.idCategory=Cat.ID
		WHERE !C.bDeleted AND C.idUser='". $_REQUEST['idUser'] ."' ";
	if($chrSearch != '') {
		$q .= "AND 
		((lower(C.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
		OR lower(C.chrLast) LIKE '%" . strtolower($chrSearch) . "%' 
		OR lower(C.chrCompany) LIKE '%" . strtolower($chrSearch) . "%'
		OR lower(concat(C.chrFirst,' ',C.chrLast)) LIKE '%" . strtolower($chrSearch) . "%')
		OR (C.ID = '" . $possiblebadge ."')) ";
	}
	$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all contacts");
}
	if ($_SESSION['idRight'] == 1) {
		$q = "SELECT Users.ID, Users.chrFirst, Users.chrLast 
			FROM Users 
			JOIN SponsorsByShow ON SponsorsByShow.idUser=Users.ID 
			WHERE !Users.bDeleted 
			GROUP BY Users.ID
			ORDER BY chrFirst, chrLast
			";	
	} else if($_SESSION['idRight'] == 2 ) { 
		$q = "SELECT Users.ID, Users.chrFirst, Users.chrLast 
			FROM Users 
			LEFT JOIN SponsorsByUser ON SponsorsByUser.idSponsor=Users.ID 
			LEFT JOIN SponsorsByShow ON SponsorsByShow.idUser=Users.ID 
			WHERE !Users.bDeleted AND (SponsorsByShow.idUser='". $_SESSION['idUser'] ."' || SponsorsByUser.idUser='". $_SESSION['idUser'] ."')
			ORDER BY chrFirst, chrLast";	
	} else {
		$q = "SELECT Users.ID, Users.chrFirst, Users.chrLast 
			FROM Users 
			LEFT JOIN SponsorsByUser ON SponsorsByUser.idSponsor=Users.ID 
			WHERE !Users.bDeleted AND SponsorsByUser.idUser='". $_SESSION['idUser'] ."'
			ORDER BY chrFirst, chrLast";	
	}
	
	
	$books = database_query($q,"Getting all Books");
	$num_results = mysqli_num_rows($books);
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?

	include($BF. 'includes/top.php');
	
	//This is the include file for the overlay
	$TableName = "Contacts";
	include($BF. 'includes/overlay.php');
?>
<form id="idForm" name="idForm" method="GET">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Manage Address Book for 
		<!--Drop Down for List of Sponsers this user has access to-->
						<select id='idUser' name='idUser' onchange='document.getElementById("idForm").submit()'>
							<?=(mysqli_num_rows($books) > 1 ? "<option value=''>-Select User-</option>" : '')?>
<?	while($row = mysqli_fetch_assoc($books)) { ?>
							<option<?=($_REQUEST['idUser'] == $row["ID"] ? ' selected="selected" ' : '')?> value='<?=$row["ID"]?>'><?=$row['chrFirst']?> <?=$row['chrLast']?></option>
<?	}
	if($num_results == 0) {
?>
							<option	value=''>N/A</option>	
<?
	}						
?>
						</select>
		
		</td>
<?
	if($num_results > 0) {
?>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>
		<td class="title_right"><a href="addcontact.php?d=<?=base64_encode("to=".$_REQUEST['idUser']."&key=".$_SESSION['idUser'])?>"><img src="<?=$BF?>images/plus_add.gif" border="0" /></a></td>
<?
	}
?>
		<td class="right"></td>
	</tr>
</table>

	<div class='instructions'>Listed below is the address book containing the contacts. You can download the contact via the icon on the left hand side next to the contact name. </div>

	<div class='innerbody'>
	<?=messages()?>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width:12px;'></th>
			<th class='headImg' style='width:12px;'></th>
			<? sortList('First Name', 'chrFirst','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Last Name', 'chrLast','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Category', 'chrCategory','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Company', 'chrCompany','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;
if (isset($result)) {
	while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td><img src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></a></td>	
				<td style='cursor: pointer;' onclick='location.href="editcontact.php?id=<?=$row['ID']?>&d=<?=base64_encode("to=".$_REQUEST['idUser']."&key=".$_SESSION['idUser'])?>";'><?=$row['chrFirst']?></td>
				<td style='cursor: pointer;' onclick='location.href="editcontact.php?id=<?=$row['ID']?>&d=<?=base64_encode("to=".$_REQUEST['idUser']."&key=".$_SESSION['idUser'])?>";'><?=$row['chrLast']?></td>
				<td style='cursor: pointer;' onclick='location.href="editcontact.php?id=<?=$row['ID']?>&d=<?=base64_encode("to=".$_REQUEST['idUser']."&key=".$_SESSION['idUser'])?>";'><?=$row['chrCategory']?></td>
				<td style='cursor: pointer;' onclick='location.href="editcontact.php?id=<?=$row['ID']?>&d=<?=base64_encode("to=".$_REQUEST['idUser']."&key=".$_SESSION['idUser'])?>";'><?=$row['chrCompany']?></td>
				<td class='options'><?=deleteButton($row['ID'],$row['chrFirst']." ".$row['chrLast'])?></td>			
			</tr>
<?	} 
}
?>
<? if($count == 0) { ?>
			<tr><td colspan="7" style="padding:3px; text-align:center; background-color:#FFFFFF; " height="20">No contacts to display</td></tr>
<?	} ?>
		</table>
</form>

	</div>
		<table cellpadding='0' cellspacing='0' style='padding-top:10px;'>
		<tr>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_gold.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Guest or VIP</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_red.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Special Guest</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/plus_add2.gif" border="0" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Add Contact to Address Book</td>
		</tr>
	</table>							
	
<?
	include($BF. 'includes/bottom.php');
?>
