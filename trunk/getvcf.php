<?
require('applevip-conf.php');

$connection = @mysql_connect($host, $user, $pass);
mysql_select_db($db, $connection);

unset($host, $user, $pass, $db);

$info = mysql_fetch_assoc(mysql_query("SELECT * FROM Contacts WHERE ID=". $_REQUEST['id']));

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=".$info['chrFirst'].'_'.$info['chrLast'].".vcf");
header("Pragma: no-cache");
header("Expires: 0");
?>
BEGIN:VCARD
VERSION:3.0
N:<?=$info['chrLast']?>;<?=$info['chrFirst']?>;;;
FN:<?=$info['chrFirst']?> <?=$info['chrLast']?>

ORG:<?=$info['chrCompany']?>

EMAIL;type=INTERNET;type=WORK;type=pref:<?=$info['chrEmail']?>

TEL;type=WORK;type=pref:<?=$info['chrPhoneOffice']?>

TEL;type=CELL:<?=$info['chrPhoneMobile']?>

item1.ADR;type=WORK;type=pref:;;<?=$info['chrAddress1']?>\,;<?=$info['chrCity']?>;<?=$info['chrState']?>;<?=$info['chrPostalCode']?>;<?=$info['chrCountry']?>

item1.X-ABADR:us
END:VCARD
