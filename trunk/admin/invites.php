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
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast"; }
		
	if (!isset($_REQUEST['id']) && !isset($_SESSION['idShow'])) { $_REQUEST['id'] = ""; }
	 else if (isset($_SESSION['idShow']) && !isset($_REQUEST['id'])) { $_REQUEST['id'] = $_SESSION['idShow']; }
	if (isset($_REQUEST['chrSearch'])) {
		$chrSearch = encode($_REQUEST['chrSearch']);
	} else {
		$_REQUEST['chrSearch'] = '';
		$chrSearch = "";
	}	
	if ($_REQUEST['id'] != "") {
	
	$q = "SELECT SponsorsbyShow.ID, chrFirst, chrLast, SponsorsbyShow.intInvites, chrStatus,
			(SELECT COUNT(Invites.ID) 
			FROM Invites
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idUser=Users.ID AND Invites.idShow=".$_REQUEST['id']." AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted) AS intInvitesUsed,
			(SELECT SUM(Invites.intGuests) 
			FROM Invites 
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idUser=Users.ID AND Invites.idShow=".$_REQUEST['id']." AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted) AS intGuestsinvited
		  FROM SponsorsbyShow
		  JOIN Users ON SponsorsbyShow.idUser= Users.ID
		  JOIN ReviewStatus ON SponsorsbyShow.idReviewStatus=ReviewStatus.ID
		  WHERE !Users.bDeleted AND SponsorsByShow.idShow='" . $_REQUEST['id'] . "' AND !SponsorsbyShow.bDeleted
		   AND 
			((lower(Users.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
			OR lower(Users.chrLast) LIKE '%" . strtolower($chrSearch) . "%'
			OR lower(concat(Users.chrFirst,' ',Users.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'))
		  GROUP BY Users.ID
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		  
		  
	$result = database_query($q,"Getting all sponser types");
 	}
 	
	$active = 'admin';
	$subactive = 'invites';
	
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
		<td class="title">Manage Invite Requests By Show 
					<select name='id' id='id' onchange='location.href="invites.php?chrSearch=<?=urlencode($_REQUEST['chrSearch'])?>&id="+this.value'>
				<option value=''>-Select Show-</option>
<?
	while($row = mysqli_fetch_assoc($Shows)) { ?>
							<option <?=(isset($_REQUEST['id'])) ? ($_REQUEST['id'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
<?
	}
?>
			</select>
		</td>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>
		<td class="title_right">
		</td>
		<td class="right"></form></td>
	</tr>
</table>


<div class='instructions'>Listed below are the invites that have been submitted by each Sponser.</div>

<div class='innerbody'>
	<?=messages()?>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Sponsor', 'chrLast', "", "id=" . $_REQUEST['id']."&chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Invites Allowed', 'intInvites', "", "id=" . $_REQUEST['id']."&chrSearch=".$_REQUEST['chrSearch']); ?>			
			<? sortList('Invites Used', 'intInvitesUsed', "", "id=" . $_REQUEST['id']."&chrSearch=".$_REQUEST['chrSearch']); ?>		
			<? sortList('Status', 'chrStatus', "", "id=" . $_REQUEST['id']."&chrSearch=".$_REQUEST['chrSearch']); ?>									
		</tr>

<?
	$count=0;
	if (!$_REQUEST['id'] == "") {
	
	$numInvites=0; $numInvitesUsed=0; // Used to count the total number invites from all sponsors
	
		while ($row = mysqli_fetch_assoc($result))  {
			 $numInvites += $row['intInvites']; 
			 $numInvitesUsed += ($row['intInvitesUsed']+$row['intGuestsinvited']); 			 
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="reviewinvites.php?id=<?=$row['ID']?>";'><?=$row['chrLast']?>, <?=$row['chrFirst']?></td>
				<td style='cursor: pointer;' onclick='location.href="reviewinvites.php?id=<?=$row['ID']?>";'><?=$row['intInvites']?></td>
				<td style='cursor: pointer;' onclick='location.href="reviewinvites.php?id=<?=$row['ID']?>";'><?=$row['intInvitesUsed']+$row['intGuestsinvited']?></td>				
				<td style='cursor: pointer;' onclick='location.href="reviewinvites.php?id=<?=$row['ID']?>";'><?=$row['chrStatus']?></td>		
				</div></td>			
			</tr>
<?
		} 
		if ($numInvites > '0') { //we don't want to show any records for no records
?>			
			<tr class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>'>
				<td align='right'>Total:</td>
				<td align='left' colspan='1'><?=$numInvites?></td>
				<td align='left' colspan='2'><?=$numInvitesUsed?></td>
			</tr>
<?
		}
	} //end if

	if($count == 0) { ?>
			<tr>
				<td align="center" colspan='4' height="20">No Sponsors in this show</td>
			</tr>
<?
	}

?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
