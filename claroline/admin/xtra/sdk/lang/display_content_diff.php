<?php

/*
 * This script displays all the variables 
 * with the same content and a different name.
 */

// include configuration and library file

include ('language.conf.php');
include ('language.lib.php');

// get start time
$starttime = get_time();

// start content
echo "<html>
<head>
 <title>Display different variables with the same content</title>
</head>
<body>";

echo "<h1>Display different variables with the same content</h1>\n";

// start form

echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";

if (isset($_REQUEST['language'])) 
{
    $language = $_REQUEST['language'];
}
else 
{
    $language = DEFAULT_LANGUAGE ;
}

echo "<p>Language: $language</p>";

// display select box with language in the table

echo "<p>Change Language: ";
echo "<select name=\"language\">";
$sql = "SELECT DISTINCT language 
        FROM ". TABLE_TRANSLATION . "
        ORDER BY language ";
$results = mysql_query($sql);

while($result=mysql_fetch_row($results))
{
    if ($result[0] == $language) 
    {
        echo "<option value=$result[0] selected=\"selected\">" . $result[0] . "</option>";
    }
    else
    {
        echo "<option value=$result[0]>" . $result[0] . "</option>";
    }
}
echo "</select></p>";

echo "<p><input type=\"submit\" value=\"OK\" /></p>";
echo "</form>";

// select variables with same content

$sql = " SELECT DISTINCT L1.language , L1.varContent , L1.varName , L1.sourceFile
    FROM ". TABLE_TRANSLATION . " L1,
         ". TABLE_TRANSLATION . " L2,
         ". TABLE_USED_LANG_VAR . " U
    WHERE L1.language = \"" . $language . "\" and
        L1.language = L2.language and
        L1.varContent = L2.varContent and
        L1.varName <> L2.varName and
        L1.varName = U.varName
    ORDER BY L1.varContent, L1.varName
    LIMIT 0, 400";

$results = mysql_query($sql) or die($problemMessage);

// display table header 

echo "<table border=\"1\">
<tr>
<td>N°</td>
<td>language</td>
<td>varName</td>
<td>varContent</td>
<td>sourceFile</td>
</tr>";

$varContent="";
$i = 0;
$color = true;

while($result=mysql_fetch_array($results))
{
     if ($result['varContent'] != $varContent)
     {
        $varContent = $result['varContent'];
        $color = !$color;
     }
     if ($color == true)
     {
        echo "<tr style=\"background-color: #ccc;\">";
     } 
     else
     {
        echo "<tr>";
     }
     echo  "<td>" . ++$i . "</td>
           <td>" . $result['language'] . "</td>
           <td>" . $result['varName'] . "</td>
           <td>" . $result['varContent'] . "</td>
           <td>" . $result['sourceFile'] . "</td>";
     echo "</tr>";
}

echo "</table>";

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>";

// display footer 

echo "</body></html>";

?>
