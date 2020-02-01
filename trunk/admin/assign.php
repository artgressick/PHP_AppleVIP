<?	
	$BF = '../';
	require($BF. '_lib.php');
	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	$title = 'User Assignment';
	include($BF. 'includes/meta.php');
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast,chrFirst"; }
		
	if (!isset($_REQUEST['idShow']) && !isset($_SESSION['idShow'])) { $_REQUEST['idShow'] = ""; }
	 else if (isset($_SESSION['idShow']) && !isset($_REQUEST['idShow'])) { $_REQUEST['idShow'] = $_SESSION['idShow']; }
	
	if (isset($_REQUEST['chrSearch'])) {
		$chrSearch = encode($_REQUEST['chrSearch']);
	} else {
		$_REQUEST['chrSearch'] = '';
		$chrSearch = "";
	}
	
  
	$q = "SELECT ID,chrFirst,chrLast
		  FROM Users
		  WHERE idRight != 3 AND ID IN (SELECT idSponsor FROM SponsorsByUser WHERE !bDeleted) AND 
			((lower(Users.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
			OR lower(Users.chrLast) LIKE '%" . strtolower($chrSearch) . "%'
			OR lower(concat(Users.chrFirst,' ',Users.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'))
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		  
	$result = database_query($q,"Getting all sponsor types");
 	
	$active = 'admin';
	$subactive = 'assign';
	
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top.php');
	
	//This is the include file for the overlay
	$TableName = "SponsorsByUser";
	include($BF. 'includes/overlay2.php');
?>

<form id="idForm" name="idForm" method="GET">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">User by Sponsor</td>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>
		<td class="title_right"><a href="addassign.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></form></td>
	</tr>
</table>


<div class='instructions'>Listed below are the Sponsors who currently have users assigned to them.</div>

<div class='innerbody'>
	<?=messages()?>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Last Name', 'chrLast','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('First Name', 'chrFirst','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<th>Users Assigned To</th>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>

<?
	$count=0;
	while ($row = mysqli_fetch_assoc($result)) { 
		$rowsponsor = database_query("SELECT Users.chrFirst, Users.chrLast 
		FROM SponsorsByUser 
		JOIN Users ON SponsorsByUser.idUser=Users.ID
		WHERE idSponsor='". $row['ID'] ."'", "getting users per sponsor");
			$UserSponsors = "";
			while ($subrow = mysqli_fetch_assoc($rowsponsor)) { 
				($UserSponsors != "" ? $UserSponsors .=", " : "" );
				$UserSponsors .= $subrow['chrFirst'] . " " . $subrow['chrLast'];
			}
	?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editassign.php?id=<?=$row['ID']?>";'><?=$row['chrLast']?></td>
				<td style='cursor: pointer;' onclick='location.href="editassign.php?id=<?=$row['ID']?>";'><?=$row['chrFirst']?></td>
				<td style='cursor: pointer;' onclick='location.href="editassign.php?id=<?=$row['ID']?>";'><?=$UserSponsors?></td>
				<td class='options'><?=deleteButton($row['ID'],$row['chrFirst']." ".$row['chrLast'])?></td>			
			</tr>
<?
	} //end if
	if($count == 0) { ?>
			<tr>
				<td align="center" colspan='3' height="20">No Sponsors have users</td>
			</tr>
<?
	}

?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
