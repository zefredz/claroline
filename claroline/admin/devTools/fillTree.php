
#Paste  in sql win  of <A target"=_PMA" href=../mysql/ >PMA</A> (open a new windows)<br>
#<br>
#and after  click to theses button

<FORM action="../managing/adminCoursesTree.php" method="post" >
	<input type="submit" name="rebuiltTreePos" value="rebuilt Tree Pos">
	<input type="submit" name="refreshAllNbChildInBase" value="rebuilt nb Childs in db">
</FORM>


<?php


##################################################
#This script output SQL to add a tree of categorie
# Edit this and remove higlight and exit(); =)<
##################################################

$nom = "Catégorie ";
$nomSep =".";
$code = "CAT";
$catSep ="_";

$largeurMin = 2;
$largeurMax = 10;
$deep = 4;
$number = 24; // <- 1+ select max(number) FROM faculte;

echo "INSERT INTO `faculte`
		SET
			`code`		= '".$code."' ,
			`code_P`	= NULL ,
			`name` 		= 'test tree root'	;<br>" ;

createChilds($largeurMin,$largeurMax,$deep,$nom,$nomSep,$code,$catSep);

function createChilds($largeurMin,$largeurMax,$deep,$nom,$nomSep,$code,$catSep)
{
	GLOBAL $number;
	//echo "#DEEP :".$deep."<br>";
	$code_P = $code;
	$nom_P	= $nom;
	if ($deep > 0)
	for ($i=1;$i<=(rand($largeurMin,$largeurMax));$i++)
	{
		$nom = 	$nom_P.$nomSep.$i;
		$code =	$code_P.$catSep.$i;
		//echo "# ".$nom." * ".$code."<br>";
		echo "INSERT INTO `faculte`
		SET
			`code`		= '".$code."' ,
			`code_P`	= '".$code_P."' ,
			`name` 		= '".$nom."'	;<br>" ;
		createChilds(rand(0,$largeurMin),rand($largeurMin,$largeurMax),$deep-rand(1,3),$nom,$nomSep,$code,$catSep);
	}
	return true;
}

?>
