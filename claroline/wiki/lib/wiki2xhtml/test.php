<?php
/*require dirname(__FILE__).'/class.wiki2xhtml.php';
header('Content-Type: text/plain');
$test = 'test toto|pwet\|coucou';
$data = wiki2xhtml::__splitTagsAttr($test);
var_dump($data);
exit;*/
//header('Content-Type: text/plain');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"  />
<title>test</title>
</head>

<body>
<?php
error_reporting(E_ALL);
require dirname(__FILE__).'/class.wiki2xhtml.php';

function wikiword($str)
{
	$tag = 'a';
	$attr = ' href="'.$str.'"';
	return '<a href="/'.$str.'">'.$str.'</a>';
}

$text = implode('',file(dirname(__FILE__).'/texte-monkey.txt'));

$W = new wiki2xhtml();
$W->setOpt('active_wikiwords',1);

$W->registerFunction('wikiword','wikiword');

echo $W->transform($text);

echo '<hr />';

echo $W->help();

?>
</body>
</html>
