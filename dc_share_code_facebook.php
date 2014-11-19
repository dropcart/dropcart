<?
$tegoed	= $_GET['tegoed'];
$code	= $_GET['code'];
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="nl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Kortingscode voor Dropcart.nl</title>
<meta property="og:title" content="Gratis kortingscode voor cartridges en toners" />
<meta property="og:type" content="website" />
<? if($empty != 1) { ?>
<meta property="og:image" content="http://www.dropcart.nl/images/logo_square.png" />
<? } ?>
<meta property="og:site_name" content="Dropcart" />
<meta name="description" content="Ik heb een 1 gratis kortingscode over t.v.w. &euro; <?=$tegoed?>. Gebruik kortingscode <?=$code?> bij het afrekenen.">
<meta http-equiv="refresh" content="0;URL=http://www.dropcart.nl/" />
</head>

<body>
	<img src="http://www.dropcart.nl/images/logo_square.png" />
</body>
</html>