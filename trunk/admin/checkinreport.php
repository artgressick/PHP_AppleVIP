<?	
	$BF = '../';
	require($BF. '_lib.php');
	
	$title = 'Checkin Report';
	include($BF. 'includes/meta.php');
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dtStamp, chrUser, chrContact"; }
		
	if ((!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) && !isset($_SESSION['idShow'])) { $_REQUEST['id'] = ""; }
	 else if (isset($_SESSION['idShow']) && !isset($_REQUEST['id'])) { $_REQUEST['id'] = $_SESSION['idShow']; }
	 
		 
	if ($_REQUEST['id'] != "") {
	
		$q = "SELECT COUNT(C.ID) AS intCount, SUM(C.intGuests) AS intGuests 
				FROM Checkin AS C
				JOIN Invites AS I ON C.idInvite=I.ID
				JOIN Contacts AS CO ON I.idContact=CO.ID AND !CO.bDeleted
				JOIN Users AS U ON I.idUser=U.ID AND !U.bDeleted
				WHERE !I.bDeleted AND C.idShow='".$_REQUEST['id']."'";
		$totalcheckedin = fetch_database_query($q,"Getting Total Checked In");
		$totalcheckedin['intTotal'] = $totalcheckedin['intCount'] + $totalcheckedin['intGuests'];	
		
		$q = "SELECT COUNT(I.ID) AS intInvites, SUM(I.intGuests) AS intGuests 
				FROM Invites AS I 
				JOIN Contacts AS CO ON I.idContact=CO.ID AND !CO.bDeleted
				JOIN Users AS U ON I.idUser=U.ID AND !U.bDeleted
				WHERE !I.bDeleted AND I.idShow='".$_REQUEST['id']."' AND I.idStatus IN (2,5,6,8,9)";
		$totalinvited = fetch_database_query($q,"Getting Total Invites");
		$totalinvited['intTotal'] = $totalinvited['intInvites'] + $totalinvited['intGuests'];		
		
			
		$q = "SELECT I.ID, CONCAT(CO.chrFirst,' ',CO.chrLast) AS chrContact, CONCAT(U.chrFirst,' ',U.chrLast) AS chrUser, I.intGuests, if(C.intGuests >= 0,C.intGuests,'N/A') AS intGuestsCheckedin, if(C.dtStamp != '',C.dtStamp,'N/A') as dtStamp
			FROM Invites AS I
			LEFT JOIN Checkin AS C ON C.idInvite=I.ID
			JOIN Contacts AS CO ON I.idContact=CO.ID
			JOIN Users AS U ON I.idUser=U.ID
			WHERE !I.bDeleted AND I.idShow='" . $_REQUEST['id'] . "' AND I.idStatus IN (2,5,6,8,9)
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
			  
		$result = database_query($q,"Getting all sponser types");
 	}
 	
	$active = 'admin';
	$subactive = 'checkinreport';
	
	//Load drop down menus for the page
	$Shows = database_query("SELECT ID,chrName FROM Shows where !bDeleted ORDER BY Shows.dBegin DESC, Shows.chrName", "getting shows");
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top.php');
?>
<form id="idForm" name="idForm" method="GET">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Check-In Report 
		<select name='id' id='id' onchange='location.href="checkinreport.php?id="+this.value'>
				<option value=''>-Select Show-</option>
<?
	while($row = mysqli_fetch_assoc($Shows)) { ?>
							<option <?=(isset($_REQUEST['id'])) ? ($_REQUEST['id'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
<?
	}
?>
			</select></td>
		<td class="title_right"><? if($_REQUEST['id'] != '') { ?><input type="button" id="excel" name="excel" onclick="window.open('<?=$BF?>admin/_excel_checkin.php?id=<?=$_REQUEST['id']?>>&sortCol=<?=$_REQUEST['sortCol']?>&ordCol=<?=$_REQUEST['ordCol']?>')" value="Export to Excel" /><? } ?>
		</td>
		<td class="right"></form></td>
	</tr>
</table>


<div class='instructions'>Select a show from above to view the report on who was checked in for that show.</div>

<div class='innerbody'>
	<?=messages()?>
	<div style='padding:10px 0;'>Total Invites: <?=$totalinvited['intTotal']?> -- Total Checked In: <?=$totalcheckedin['intTotal']?></div>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Contact', 'chrContact', "", "id=" . $_REQUEST['id']); ?>
			<? sortList('Sponsor', 'chrUser', "", "id=" . $_REQUEST['id']); ?>
			<? sortList('Guests Invited', 'intGuests', "", "id=" . $_REQUEST['id']); ?>
			<? sortList('Guests Checked In', 'intGuestsCheckedin', "", "id=" . $_REQUEST['id']); ?>
			<? sortList('Checked In', 'dtStamp', "", "id=" . $_REQUEST['id']); ?>			
		</tr>

<?
	$count=0;
	if (!$_REQUEST['id'] == "") {
	
	$numInvites=0; // Used to count the total number invites from all sponsors
	
		while ($row = mysqli_fetch_assoc($result))  {
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style=''><?=$row['chrContact']?></td>
				<td style=''><?=$row['chrUser']?></td>
				<td style=''><?=$row['intGuests']?></td>
				<td style=''><?=$row['intGuestsCheckedin']?></td>
				<td style=''><?=($row['dtStamp']!='N/A'?date('n/j/Y g:i a',strtotime($row['dtStamp'])):$row['dtStamp'])?></td>
			</tr>
<?
		} 
	} //end if

	if($count == 0) { ?>
			<tr>
				<td align="center" colspan='5' height="20">No Checkin information found</td>
			</tr>
<?
	}

?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
