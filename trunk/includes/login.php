<?
	$BF = "";

	$title = "Login Page";
	include('includes/meta.php');
?>
<body>
<form id="form1" name="form1" method="post" action="">
<table width="300" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan='3' height="65">&nbsp;</td>
  </tr>
  <tr>
    <td colspan='3'><img src="images/logon_image.gif" width="300" height="65" /></td>
  </tr>
  <tr>
  
  
  	<td style='width: 5px; background: url(images/shadow-left.gif) repeat-y;'></td>
  
  
  
    <td bgcolor="#ffffff">
      <div style='padding: 10px;'>
<? if(isset($error_messages)) {
		foreach($error_messages as $er) { ?>
			<div class='ErrorMessage'><?=$er?></div>
<?		}
	}
?>
        <p><span class="FormName">Apple Email Address</span> <span class="FormRequired">(Required)</span> <br />
            <input name="auth_form_name" type="text" size="30" maxlength="35" value='<?=(isset($_REQUEST['auth_form_name']) ? $_REQUEST['auth_form_name'] : '')?>' />
		</p>
        <p><span class="FormName">Password</span> <span class="FormRequired">(Required)</span> <br />
            <input name="auth_form_password" type="password" size="30" maxlength="30" />
		</p>
        <p>
			<input type="submit" name="Submit" value="Submit" />
		</p>
        <p class="FormRequired">Problems? Contact: Alison Costa<br />
          <a href="mailto:acosta@apple.com" style="text-decoration:none; color:#333333">Email</a> or 1-408-862-5556
</p>
     </td>
	 
  	<td style='width: 5px; background: url(images/shadow-right.gif) repeat-y;'></td>
  
  
  </tr>
  <tr>
    <td colspan='3'><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td rowspan="2" align="left" valign="bottom" background="images/shadowblue-left.gif"><img src="images/blue-bottomleft.gif" width="15" height="18" /></td>
        <td width="100%" height="20" bgcolor="#C2CED8">&nbsp;</td>
        <td rowspan="2" align="right" valign="bottom" background="images/shadowblue-right.gif"><img src="images/blue-bottomright.gif" width="15" height="18" /></td>
      </tr>
      <tr>
        <td height="9" background="images/shadow-bottom.gif"><img src="images/shadow-bottom.gif" width="4" height="9" /></td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
</body>
</html>