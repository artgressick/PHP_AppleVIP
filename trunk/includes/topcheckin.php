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
		<td class="fill"><? if($_SESSION['idRight'] != 4) { ?><a href='<?=$BF?>profile.php'><?=$_SESSION['chrFirst']?> <?=$_SESSION['chrLast']?></a> | <? } ?><a href='?logout=1'>Logout</a></td>
		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>
<div class='content'>
