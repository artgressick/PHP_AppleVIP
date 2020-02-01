</head>
<body <?=(isset($bodyParams) ? $bodyParams : '')?>>

<!-- The top of the screen -->

<div class='mainBody'>

<div width="900" class='topBanner'>
	<img src="<?=$BF?>images/title_banner.jpg" width="900" height="100" />
</div>

<table border="0" cellspacing="0" cellpadding="0" class='navMenu'>
	<tr>
		<td><img src="<?=$BF?>images/cap-left.gif" width="10" height="21" /></td>
		<td class="menu<?=($active == 'home' ? '_active' : '')?>"><a href="<?=$BF?>index.php">Home</a></td>
		<td class="menu<?=($active == 'addressbook' ? '_active' : '')?>"><a href="<?=$BF?>addressbook.php">Address Book</a> </td>
		<td class="menu<?=($active == 'invites' ? '_active' : '')?>"><a href="<?=$BF?>invites.php">Invites</a> </td>
		
		
		
		<?
		//Check to see if the user is an admin. If so display the admin link
		if($_SESSION['idRight'] == 1){ ?>
		<td class="menu<?=($active == 'admin' ? '_active' : '')?>"><a href="<?=$BF?>admin/index.php">Administration</a></td>
		<? } ?>
<?	if($_SESSION['bMasq']) { ?>
		<td class="fill"><a href='<?=$BF?>profile.php'>(<?=$_SESSION['chrOldFirst']?> <?=$_SESSION['chrOldLast']?> masquerading as) <?=$_SESSION['chrFirst']?> <?=$_SESSION['chrLast']?></a> | <a href='?logoutmasq=1'>Logout</a> </td>
<?	} else { ?>
		<td class="fill"><a href='<?=$BF?>profile.php'><?=$_SESSION['chrFirst']?> <?=$_SESSION['chrLast']?></a> | <a href='?logout=1'>Logout</a> </td>
<?	} ?>

		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>

<?
	if($active == 'admin') {	
?>

<table border="0" cellspacing="0" cellpadding="0" class='navSubMenu'>
	<tr>
		<td><img src="<?=$BF?>images/cap-left.gif" width="10" height="21" /></td>
		<td class="menu<?=($subactive == 'shows' ? '_active' : '')?>"><a href="index.php">Shows</a></td>
		<td class="menu<?=($subactive == 'sponsors' ? '_active' : '')?>"><a href="sponsors.php">Sponsors by Show</a></td>
		<td class="menu<?=($subactive == 'invites' ? '_active' : '')?>"><a href="invites.php">Manage Invite Requests</a></td>
		<td class="menu<?=($subactive == 'showinvites' ? '_active' : '')?>"><a href="showinvites.php">Show Approved Invites</a></td>					
		<td class="menu<?=($subactive == 'assign' ? '_active' : '')?>"><a href="assign.php">User Assignments</a></td>
		<td class="menu<?=($subactive == 'categories' ? '_active' : '')?>"><a href="categories.php">Categories</a></td>
		<td class="menu<?=($subactive == 'accounts' ? '_active' : '')?>"><a href="accounts.php">Accounts</a> </td>
		<td class="menu<?=($subactive == 'checkinreport' ? '_active' : '')?>"><a href="checkinreport.php">Check-In Report</a> </td>
		<td class="menu<?=($subactive == 'checkin' ? '_active' : '')?>"><a href="<?=$BF?>checkin.php" target="_blank">Check-In</a> </td>
		<td class="fill"></td>
		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>

<? 
	}
?>

<div class='content'>
