<?php
	$BF = "";
	include($BF.'_lib.php');
	$title = 'Check-In Page';
	$active = '';
	
	include('includes/meta.php');
	include('includes/topcheckin.php');
?>
<!-- This is the main body of the page.-->
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Checkin Success</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>

	<div class='innerbody'>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>

     <td><form name="form1" method="post" action="checklogon.php">
      <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td id=cont><table width="100%"  border="0" cellspacing="0" cellpadding="5">
            <tr>
              <td class="title" style="text-align:center;">Check-in Completed Successfully</td>
            </tr>
            <tr>
              <td><span style="font-size: 12px"></span></td>
            </tr>
            <tr>
              <td align="center" class="title"><a href="checkin.php?idShow=<?=$_REQUEST['idShow']?>"><span style="color:#222;">Return to Check-in</span></a></td>
            </tr>
          </table></td>
        </tr>
      </table>
    </form></td>
  </tr>

</table>
</div>
<!-- This is the bottom of the body -->
<?php
	include('includes/bottom.php');
?>
</body>
</html>
