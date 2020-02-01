<?	
	$BF = '../';
	require($BF. '_lib.php');

	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	
	$title = 'Shows';
	include($BF. 'includes/meta.php');	

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	
	
	$q = "SELECT Shows.ID, chrName, chrStatus
		  FROM Shows
		  JOIN ShowStatus ON Shows.idStatus = ShowStatus.ID
		  WHERE !Shows.bDeleted
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all shows");

	$active = 'admin';
	$subactive = 'shows';

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top.php');
	
	//This is the include file for the overlay
	$TableName = "Shows";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Shows</td>
		<td class="title_right"><a href="addshow.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Select a show from the list above.</div>

<div class='innerbody'>
	<?=messages()?>
	<form id="idForm" name="idform" method="post">
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<? sortList('Show Name', 'chrName'); ?>
			<? sortList('Show Status', 'chrStatus'); ?>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editshow.php?id=<?=$row['ID']?>";'><?=$row['chrName']?></td>
				<td style='cursor: pointer;' onclick='location.href="editshow.php?id=<?=$row['ID']?>";'><?=$row['chrStatus']?></td>
				<td class='options'><?=deleteButton($row['ID'],$row['chrName'])?></td>			
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='3' height="20">No shows to display</td>
			</tr>
<?	} ?>
	</table>
	</div>
</form>	
<?
	include($BF. 'includes/bottom.php');
?>
