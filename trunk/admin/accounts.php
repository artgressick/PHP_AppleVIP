<?	
	$BF = '../';
	require($BF. '_lib.php');
	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	$title = 'Accounts';
	include($BF. 'includes/meta.php');

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast, chrFirst"; }
	
	if (isset($_REQUEST['chrSearch'])) {
		$chrSearch = encode($_REQUEST['chrSearch']);
	} else {
		$_REQUEST['chrSearch'] = '';
		$chrSearch = "";
	}
	
	$q = "SELECT Users.ID, chrFirst, chrLast, chrEmail, chrRights
		  FROM Users
		  JOIN UserRights ON Users.idRight = UserRights.ID
		  WHERE !Users.bDeleted AND 
		((lower(Users.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
		OR lower(Users.chrLast) LIKE '%" . strtolower($chrSearch) . "%'
		OR lower(concat(Users.chrFirst,' ',Users.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'))
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all users");

	$active = 'admin';
	$subactive = 'accounts';
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/mini_popup.js"></script>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top.php');
	
	//This is the include file for the overlay
	$TableName = "Users";
	include($BF. 'includes/overlay.php');
?>
<form id="idForm" name="idForm" method="POST">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Accounts</td>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>
		<td class="title_right"><a href="addaccount.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></form></td>
	</tr>
</table>


<div class='instructions'>This is a list of people who have access to the website. Only people in this area will be able to log in and be either a Sponsor or a User.</div>
<div class='innerbody'>
	<?=messages()?>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<? sortList('First Name', 'chrFirst','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Last Name', 'chrLast','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Email', 'chrEmail','',"chrSearch=".$_REQUEST['chrSearch']); ?>
			<? sortList('Access Rights', 'chrRights','',"chrSearch=".$_REQUEST['chrSearch']); ?>			
			<th style='text-align: center;'><img src="<?=$BF?>images/options.gif"></th>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editaccount.php?id=<?=$row['ID']?>";'><?=$row['chrFirst']?></td>
				<td style='cursor: pointer;' onclick='location.href="editaccount.php?id=<?=$row['ID']?>";'><?=$row['chrLast']?></td>
				<td style='cursor: pointer;' onclick='location.href="editaccount.php?id=<?=$row['ID']?>";'><?=$row['chrEmail']?></td>	
				<td style='cursor: pointer;' onclick='location.href="editaccount.php?id=<?=$row['ID']?>";'><?=$row['chrRights']?></td>			
				<td class='options'><a href='masq.php?id=<?=$row['ID']?>'>Masq</a></td>
				<td class='options'><?=deleteButton($row['ID'],$row['chrFirst']." ".$row['chrLast'])?></td>			
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='5' height="20">No User to display</td>
			</tr>
<?	} ?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom.php');
?>
