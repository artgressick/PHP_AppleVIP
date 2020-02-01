<?	
	$BF = '../';
	require($BF. '_lib.php');
	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	$title = 'Invites';
	include($BF. 'includes/meta.php');
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "idInviteStatus,chrLast,chrFirst"; }
	
	if (!isset($_REQUEST['idSearchWho'])) { $_REQUEST['idSearchWho'] = 2; }
	
	$_SESSION['idSearchWho'] = $_REQUEST['idSearchWho'];
	if (isset($_REQUEST['chrSearch'])) {
		$chrSearch = encode($_REQUEST['chrSearch']);
	} else {
		$_REQUEST['chrSearch'] = '';
		$chrSearch = "";
	}

	$tmpStatus = database_query("SELECT ID, chrStatus FROM iStatus WHERE !bDeleted ORDER BY dOrder","Getting Status");
	$iStatus = array();
	while($row = mysqli_fetch_assoc($tmpStatus)) {
		$iStatus[$row['ID']] = $row['chrStatus'];
	}
	
	if ((!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) && !isset($_SESSION['idShow'])) { $_REQUEST['id'] = ""; }
	 else if (isset($_SESSION['idShow']) && !isset($_REQUEST['id'])) { $_REQUEST['id'] = $_SESSION['idShow']; }
	
	if ($_REQUEST['id'] != "") {
	$q = "SELECT SponsorsbyShow.ID, Users.ID as idUser, chrFirst, chrLast, SponsorsbyShow.intInvites, chrStatus,
			(SELECT COUNT(Invites.ID) 
			FROM Invites 
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idUser=Users.ID AND Invites.idShow='".$_REQUEST['id']."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted) AS intInvitesUsed,
			(SELECT SUM(Invites.intGuests) 
			FROM Invites 
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idUser=Users.ID AND Invites.idShow='".$_REQUEST['id']."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted) AS intGuestsinvited
		  FROM SponsorsbyShow
		  JOIN Users ON SponsorsbyShow.idUser= Users.ID
		  JOIN ReviewStatus ON SponsorsbyShow.idReviewStatus=ReviewStatus.ID
		  WHERE !Users.bDeleted AND SponsorsByShow.idShow='" . $_REQUEST['id'] . "' AND !SponsorsbyShow.bDeleted AND SponsorsByShow.idReviewStatus=3";
		  
		  
	if ($_REQUEST['idSearchWho'] == 1) {
		(isset($_REQUEST['chrSearch']) ? $chrSearch = encode($_REQUEST['chrSearch']) : $chrSearch = "" );
		$q .= " AND 
			((lower(Users.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
			OR lower(Users.chrLast) LIKE '%" . strtolower($chrSearch) . "%'
			OR lower(concat(Users.chrFirst,' ',Users.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'))";
	}		  
	$q .=  " GROUP BY Users.ID
		  ORDER BY chrLast,chrFirst";
		  
	$_SESSION['SponsorQuery'] = $q;

	$result = database_query($q,"Getting all sponsor types");
 	}
 	
	$active = 'admin';
	$subactive = 'showinvites';
	
	(!isset($_REQUEST['idStatus']) ? $_REQUEST['idStatus'] = "%" : "" );
	
	//Load drop down menus for the page
	$Shows = database_query("SELECT ID,chrName FROM Shows where !bDeleted ORDER BY Shows.dBegin DESC, Shows.chrName", "getting shows");
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top.php');
	
	//This is the include file for the overlay
	$TableName = "SponsorsbyShow";
	include($BF. 'includes/overlay.php');
?>

<form id="idForm" name="idForm" method="GET">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Show All Approved Invites for   
					<select name='id' id='id' onchange='location.href="showinvites.php?sortCol=<?=$_REQUEST['sortCol']?>&ordCol=<?=$_REQUEST['ordCol']?>&idSearchWho=<?=urlencode($_REQUEST['idSearchWho'])?>&chrSearch=<?=urlencode($_REQUEST['chrSearch'])?>&idStatus=<?=$_REQUEST['idStatus']?>&id="+this.value' style="width:150px;">
				<option value=''>-Select Show-</option>
<?
	while($row = mysqli_fetch_assoc($Shows)) { ?>
							<option <?=(isset($_REQUEST['id'])) ? ($_REQUEST['id'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
<?
	}
?>
			</select>
		with Status: <select name='idStatus' id='idStatus' onchange='location.href="showinvites.php?sortCol=<?=$_REQUEST['sortCol']?>&ordCol=<?=$_REQUEST['ordCol']?>&idSearchWho=<?=urlencode($_REQUEST['idSearchWho'])?>&chrSearch=<?=urlencode($_REQUEST['chrSearch'])?>&id=<?=$_REQUEST['id']?>&idStatus="+this.value'>
				<option value='%'>-Show All-</option>
<?
	foreach ($iStatus AS $k => $v) { ?>
							<option <?=(isset($_REQUEST['idStatus'])) ? ($_REQUEST['idStatus'] == $k ? ' selected ' : '') : '' ?> value='<?=$k?>'><?=$v?></option>
<?
	}
?>
			</select>			
		</td>
		<td class="title_right" style="text-align:left;">

			
		</td>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=@$_REQUEST['chrSearch']?>" /><span style="font-size:9px;"> <input type="radio" id="idSearchWho" name="idSearchWho" value="1" <?=(@$_REQUEST['idSearchWho'] == 1 ? "checked='checked'" : "")?> />Sponsers <input type="radio" id="idSearchWho" name="idSearchWho" value="2" <?=(@$_REQUEST['idSearchWho'] == 2 ? "checked='checked'" : "")?> />Invites </span><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>
		<td class="right"></form></td>
	</tr>
</table>


<div class='innerbody'>
<?=messages()?>
<div class='instructions'>Listed below are the invites that have been approved listed by Sponsor.</div>
<div style="text-align:right;"><input type="button" id="excel" name="excel" onclick="window.open('<?=$BF?>admin/_excel_invites.php?id=<?=$_REQUEST['id']?>&idStatus=<?=$_REQUEST['idStatus']?>&sortCol=<?=$_REQUEST['sortCol']?>&ordCol=<?=$_REQUEST['ordCol']?>')" value="Export to Excel" /></div>
<? 
	$count=0;
	if(isset($result)) {
	while ($rowsponsor = mysqli_fetch_assoc($result))  {
	?><br /><?
	$count++;
	?>
		
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<th>Sponsor</th>
				<th style="width:75px;">Invites Allowed</th>
				<th style="width:75px;">Invites Used</th>
			</tr>
			<tr style="background-color:#FFFFFF;">
				<td style="padding:5px;"><?=$rowsponsor['chrLast']?>, <?=$rowsponsor['chrFirst']?></td>
				<td style="text-align:center;"><?=$rowsponsor['intInvites']?></td>
				<td style="text-align:center;"><?=$rowsponsor['intInvitesUsed']+$rowsponsor['intGuestsinvited']?></td>				
			</tr>
		</table>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<? sortList('Last Name', 'chrLast', "width:150px;", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>
				<? sortList('First Name', 'chrFirst', "width:150px;", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>			
				<? sortList('Company', 'chrCompany', "", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>
				<? sortList('Title', 'chrTitle', "", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>
				<? sortList('Email', 'chrEmail', "", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>
				<? sortList('Alt Email', 'chrAltEmail', "", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>
				<? sortList('Category', 'chrCategory', "width:150px;", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>
				<? sortList('Guests', 'intGuests', "width:40px;", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>			
				<? sortList('Status', 'idStatus', "width:100px;", "id=" . $_REQUEST['id']. "&idStatus=". $_REQUEST['idStatus']."&chrSearch=". $_REQUEST['chrSearch']."&idSearchWho=". $_REQUEST['idSearchWho']); ?>									
			</tr>
	<? 
	
			$q = "SELECT Invites.ID as idInvite, Contacts.ID, Contacts.chrFirst, Contacts.chrLast, idStatus, Contacts.chrCompany, Categories.chrCategory, Invites.intGuests, Contacts.chrEmail, Contacts.chrTitle, Contacts.chrAltEmail
				FROM Contacts 
				JOIN Invites ON Invites.idContact=Contacts.ID AND idShow=". $_REQUEST['id'] ."
				LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
				WHERE !Invites.bDeleted AND !Contacts.bDeleted AND Contacts.idUser=".$rowsponsor['idUser']." AND idStatus LIKE '".$_REQUEST['idStatus']."'";
			if ($_REQUEST['idSearchWho'] == 2) {
				(isset($_REQUEST['chrSearch']) ? $chrSearch = encode($_REQUEST['chrSearch']) : $chrSearch = "" );
				$possiblebadge = ltrim(substr($chrSearch, 5, -3), '0');
				$q .= " AND 
					((lower(Contacts.chrFirst) LIKE '%" . $chrSearch . "%' 
					OR lower(Contacts.chrLast) LIKE '%" . $chrSearch . "%' 
					OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
					OR (Contacts.ID = '" . $possiblebadge ."'))";
			}
			$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol']; 
			 
			 			 
			$Invites = database_query($q,"Getting all Invites");
			$rowcount=0;
			while ($row = mysqli_fetch_assoc($Invites))  {
			?>
			<tr id='tr<?=$row['ID']?>' class='<?=($rowcount++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td><?=$row['chrLast']?></td>
					<td><?=$row['chrFirst']?></td>
					<td><?=$row['chrCompany']?></td>
					<td><?=$row['chrTitle']?></td>
					<td><?=$row['chrEmail']?></td>
					<td><?=$row['chrAltEmail']?></td>
					<td><?=$row['chrCategory']?></td>	
					<td><?=$row['intGuests']?></td>			
					<td><?=$iStatus[$row['idStatus']]?></td>		
					</div></td>			
				</tr>
			<?
			}
			?>
			</table>
		<?
		}
	?>

	</table>
 <?
	}
	if($count == 0) { ?>
		<div style="text-align:center;">No Sponsors with Approved Invite Lists to show</div>
<?
	}

?>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
