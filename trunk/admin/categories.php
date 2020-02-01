<?	
	$BF = '../';
	require($BF. '_lib.php');
	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	$title = 'Categories';
	include($BF. 'includes/meta.php');

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrCategory"; }
	
	$q = "SELECT ID, chrCategory
		  FROM Categories
		  WHERE !bDeleted
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all categories");

	$active = 'admin';
	$subactive = 'categories';
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/mini_popup.js"></script>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top.php');
	
	//This is the include file for the overlay
	$TableName = "Categories";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Categories</td>
		<td class="title_right"><a href="addcategory.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>This is a list of categories to be used in the address book/contact area.</div>

<div class='innerbody'>
	<?=messages()?>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<? sortList('Category', 'chrCategory'); ?>	
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editcategory.php?id=<?=$row['ID']?>";'><?=$row['chrCategory']?></td>		
				<td class='options'><?=deleteButton($row['ID'],$row['chrCategory'])?></td>			
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='5' height="20">No Categories to display</td>
			</tr>
<?	} ?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
