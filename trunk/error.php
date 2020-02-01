<?php
	$BF = '';
	$title = 'Error!';
	$active = "";
	require($BF. '_lib.php');

if (count($_POST)) {
			header('Location: ' . $BF . 'index.php');
			die();
}

	// Set the title, and add the doc_top
	include($BF. 'includes/meta.php');
	include($BF. 'includes/top.php');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title"><strong>ERROR!!</strong></td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<div class='innerbody'>
	<form id='idForm' name='idForm' method='post' action=''>
		<div style="padding-top:20px; padding-bottom:20px;"><strong>An Error as occured! This is usually due to missing or incomplete information.  Please try again.</strong></div>
			
		<div><input type="button" id="back" name="back" value="Back" onclick="javascript: history.go(-1)" />&nbsp;&nbsp;&nbsp;<input type="submit" id="submit" name="submit" value="Home" /></div>
	</form>
</div>
<?
	include($BF. 'includes/bottom.php');
?>
