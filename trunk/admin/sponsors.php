<?	
	$BF = '../';
	require($BF. '_lib.php');
	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	$title = 'Sponsors';
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
	
	$q = "SELECT DISTINCT SponsorsbyShow.ID, chrFirst, chrLast, SponsorsbyShow.intInvites
	      FROM SponsorsbyShow
		  JOIN Users ON SponsorsbyShow.idUser=Users.ID
		  WHERE !Users.bDeleted AND SponsorsByShow.idShow='" . $_REQUEST['id'] . "' AND !SponsorsbyShow.bDeleted
		   AND 
			((lower(Users.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
			OR lower(Users.chrLast) LIKE '%" . strtolower($chrSearch) . "%'
			OR lower(concat(Users.chrFirst,' ',Users.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'))
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		  
	$result = database_query($q,"Getting all sponser types");
 	}
 	
	$active = 'admin';
	$subactive = 'sponsors';
	
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
		<td class="title">Sponsor by Show 
		<select name='id' id='id' onchange='location.href="sponsors.php?chrSearch=<?=urlencode($_REQUEST['chrSearch'])?>&id="+this.value'>
				<option value=''>-Select Show-</option>
<?
	while($row = mysqli_fetch_assoc($Shows)) { ?>
							<option <?=(isset($_REQUEST['id'])) ? ($_REQUEST['id'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
<?
	}
?>
			</select></td>
		<td class="title_right">
		</td>
<?	if($_REQUEST['id'] != "") { ?>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>
		<td class="title_right"><a href="addsponsor.php?id=<?=$_REQUEST["id"]?>"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
<?	} ?>
		<td class="right"></form></td>
	</tr>
</table>


<div class='instructions'>Listed below are the sponsor that have access to the show in the drop down on the right. To add a sponsor to this show please click on the add button.</div>

<div class='innerbody'>
	<?=messages()?>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Sponsor', 'chrLast', "", "id=" . $_REQUEST['id']."&chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Invites', 'intInvites', "", "id=" . $_REQUEST['id']."&chrSearch=".$_REQUEST['chrSearch']); ?>			
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>

<?
	$count=0;
	if (!$_REQUEST['id'] == "") {
	
	$numInvites=0; // Used to count the total number invites from all sponsors
	
		while ($row = mysqli_fetch_assoc($result))  {
			 $numInvites += $row['intInvites']; 
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editsponsor.php?id=<?=$row['ID']?>";'><?=$row['chrLast']?>, <?=$row['chrFirst']?></td>
				<td style='cursor: pointer;' onclick='location.href="editsponsor.php?id=<?=$row['ID']?>";'><?=$row['intInvites']?></td>
				<td class='options'><?=deleteButton($row['ID'],$row['chrFirst']." ".$row['chrLast'])?></td>			
			</tr>
<?
		} 
		if ($numInvites > '0') { //we don't want to show any records for no records
?>			
			<tr class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>'>
				<td align='right'>Total number of Invites for all Sponsors:</td>
				<td align='left' colspan='2'><?=$numInvites?></td>
			</tr>
<?
		}
	} //end if

	if($count == 0) { ?>
			<tr>
				<td align="center" colspan='3' height="20">No Sponsor in this show</td>
			</tr>
<?
	}

?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
