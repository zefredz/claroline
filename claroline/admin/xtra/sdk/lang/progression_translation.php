<?php

/*
 * This script display progression of all language.
 */

// include configuration and library file
include ('language.conf.php');
include ('language.lib.php');

// table

$tbl_used_lang = '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_USED_LANG_VAR . '`';
$tbl_translation =  '`' . $mainDbName . '`.`' . $mainTblPrefix . TABLE_TRANSLATION . '`';

// get start time
$starttime = get_time();

// start content
echo "<html>
<head>
 <title>Display Progression of Translations</title>
</head>
<body>";

echo "<h1>Display Progression of Translations</h1>\n";

// count different variables in script
$sql = " SELECT count(DISTINCT varName) 
        FROM " . $tbl_used_lang . "";

$results = mysql_query($sql);
$row = mysql_fetch_row($results);
$count_total_diff_var = $row[0];

echo "<p>Total variables in Claroline scripts: <strong>" . $count_total_diff_var . "</strong></p>";

if ( isset($_REQUEST['exCmd']) && $_REQUEST['exCmd'] == 'ToTranslate' )
{

    if ( isset($_REQUEST['language']))
    {
        $language = $_REQUEST['language'];
	}
    else 
    {
        $language = DEFAULT_LANGUAGE ;
    }

    printf("<h2>Missing variables in %s</h2>",$language);
    printf("<p><a href=\"%s\">Back</a></p>",$_SERVER['PHP_SELF']);
    
    // count missing lang var in devel complete file for this language
	$sql = " SELECT DISTINCT u.varName, u.sourceFile 
	         FROM ". $tbl_used_lang . " u 
	         LEFT JOIN " . $tbl_translation . " t ON 
	         (
	            u.varName = t.varName 
	            AND t.language=\"" . $language . "\"
	         ) 
	         WHERE t.varContent is NULL " ;
    $sql .= " ORDER BY u.varName, u.sourceFile ";
    $result_missing_var = mysql_query($sql);
	
    // display table header
    echo "<table border=\"1\">\n";
    echo "<thead>"
	     . "<tr style=\"background-color: #a1e1ff;\">"
         . "<td>VarName</td>"
	     . "<td>SourceFile</td>"
	     . "</tr>"
         . "</thead>"
	     . "<tbody>\n";

    // variables used to switch background color
    $varName = '';
    $color = true;
	
    // browse missing variables
	while ($row_missing_var = mysql_fetch_array($result_missing_var)) 
	{
	    // get values
	    $sourceFile = $row_missing_var['sourceFile'];
        if ($row_missing_var['varName'] != $varName)
        {
            $varName = $row_missing_var['varName'];
            $color = !$color;
        }
        
        // display row
        if ($color)
        {
            echo "<tr style=\"background-color: #ccc;\">\n";
        } 
        else
        {
            echo "<tr>\n";
        }

        echo "<td>". $varName ."</td>\n"
            . "<td>". $sourceFile ."</td>\n"
            . "</tr>\n";
	}

    // display table footer
    echo "</tbody>";
    echo "</table>";
}
else
{

    /*
     * Display a table and display each language variable translated, to translate and complete pourcentage of the translation
     */

	// get all languages
	$sql = " SELECT DISTINCT language 
	         FROM " . $tbl_translation . "";
	$result_language = mysql_query($sql);
	

    // display table header
	echo "<table border=\"1\">\n";
	echo "<thead>
	      <tr style=\"background-color: #a1e1ff\">
	       <td>Language</td>
	       <td>Translated</td>
	       <td>To translate</td>
	       <td>Complete %</td>
	      </tr>
	      </thead>
	      <tbody>\n";
	
	while ($row_language = mysql_fetch_array($result_language)) 
	{
	    // get language
	    $language = $row_language['language'];
	
		// count missing lang var in devel complete file for this language
		$sql = " SELECT count(DISTINCT u.varName) 
		         FROM ". $tbl_used_lang . " u 
		         LEFT JOIN " . $tbl_translation . " t ON 
		         (
		            u.varName = t.varName 
		            AND t.language=\"" . $language . "\"
		         ) 
		         WHERE t.varContent is NOT NULL ";
	    
        // execute query and get result
		$result_missing_var_count = mysql_query($sql) or die("mysql_error " . __LINE__);
		$row_missing_var_count  = mysql_fetch_row($result_missing_var_count);

        // compute field
		$count_var_translated = $row_missing_var_count[0];
	    $count_var_to_translate = $count_total_diff_var - $count_var_translated;
	    $pourcent_progession = (float) round (1000 * $count_var_translated / $count_total_diff_var) / 10;
	
        // display row

        if ( $pourcent_progession > 60 ) echo "<tr style=\"font-weight: bold;\">\n";
        else echo "<tr>\n";

        echo "<td>" . $language . "</td>\n"
	         . "<td style=\"text-align: right\">" . $count_var_translated . "</td>\n"
	         . "<td style=\"text-align: right\">"
	         . "<a href=\"" . $_SERVER['PHP_SELF'] . "?exCmd=ToTranslate&language=" . $language . "\">" . $count_var_to_translate . "</a>"
	         . "</td>\n"
	         . "<td style=\"text-align: right\">" . $pourcent_progession . " %</td>\n"
	         . "</tr>\n";
	}
	
	echo "</tbody>";
	echo "</table>";
}

// get end time
$endtime = get_time();
$totaltime = ($endtime - $starttime);

echo "<p><em>Execution time: $totaltime</em></p>";

// display footer 
echo "</body></html>";

?>
