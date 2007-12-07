<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
/**
 * CLAROLINE
 *
 * This lib provide functions to manage a tree
 *
 * actualy it's property of cours categories
 * table structure attempt for thes trees
 *
 *  TREE
 *
 *  * id        integer
 *
 *  * label     string
 *  * ...
 *
 *  NODE
 *  * id        integer
 *  * tree      integer
 *
 *  * id_P      integer rel to parent node
 *  * posLo   integer
 *  * posHi   integer
 *  * level     integer rel to
 *
 *  A node is child of one and only one other node.
 *  A node is node of one and only one tree.
 *  A special node of each tree, called ROOT and identified internally by id NULL
 *
 *  An item is link to a Node.
 *
 *  posLo and posHi
 *
 *  these values are set with the folliwing process.
 *
 *  A counter value, is set to posLo/high field.
 *  The value is incremented by 1 before each set
 *  The posLo is set BEFORE check child
 *  The posHi is set AFTER check child
 *
 *  usage :
 *  * in a set of children an order by posLo (or posHi) give order of children
 *  * to found all descendance of a node.
 *      WHERE posLo > myposLo and posHi < myposHi
 *  * to found all ascendance of a node.
 *      WHERE posLo < myposLo and posHi > myposHi
 *  * to count node descendance
 *      ((myposHi - myposLo)-1)/2
 *
 * @author claro team <cvs@claroline.net>
 * @package CLTREE
 * @todo replace select *
 */


/**
 * Count descendance
 * @param integer $idTree id of the tree
 * @return integer qty of child node.
 * @author Christophe Gesché <moosh@claroline.net>
 */

function claro_count_children($idTree, $idNode=null)
{
    global $tbl_node;
    $nbChild=0;

    if ($tbl_node='')
    {
        die('claro_count_children() need a $tbl_node set with table name of nodes.');
    }

    if (is_null($idNode))
    {
        $sqlGetNodes = "
        SELECT * from `" . $tbl_node . "` `node`
            WHERE id_P is null
                 AND tree=' . $idTree . '";
    }
    else
    {
        $sqlGetNodes = "
        SELECT * from `" . $tbl_node . "` `node`
            WHERE id_P = '" . $idNode . "'
                AND tree='" . $idTree . "'
                ";
    }
    $resGetNodes = claro_sql_query($sqlGetNodes);
    while ($node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC))
    {
        $nbChild++;
        $nbChild += claro_count_children($tree,$node['code']);
    };
    return $nbChild;
}


/**
 * this is  the  original function, work only for course cat
 * @param  $codeCat string code of category.
 * @return integer nb cat child
 * @author Christophe Gesché <moosh@claroline.net>
 */

function nbChildHardCount($codeCat=null)
{
    global $tbl_cat;
    $nbChild=0;

    if (is_null($codeCat))
    {
        $sqlGetNodes = "select * from `" . $tbl_cat . "` `node`
        WHERE code_P is null";
    }
    else
    {
        $sqlGetNodes = "select * from `" . $tbl_cat . "` `node`
        WHERE code_P = '".$codeCat."'";
    }
    $resGetNodes = claro_sql_query($sqlGetNodes);
    while ($node = mysql_fetch_array($resGetNodes, MYSQL_ASSOC ) )
    {
        $nbChild++;
        $nbChild += nbChildHardCount( $node['code'] );
    };
    return $nbChild;

}


/**
 * this is  the  original function, work only for course cat
 * recompute tree index. and reset
 * treePos and nbChild values.
 * @param  $codeCat string code of category.
 * @param  $treePos integer starting treePos value.
 * @return true wether success
 * @author Christophe Gesché <moosh@claroline.net>
  */


function resfreshTreePosNode($codeCat,$treePos)
{
    global $tbl_cat;
    if ($idCat=="NULL")
    {
        $sqlGetNodes = "
        SELECT * from `" . $tbl_cat . "` `node`
                 WHERE code_P is null
                    AND tree='" . $idTree . "'
                ORDER by treePos
        ";
    }
    else
    {
        $sqlGetNodes = "
        SELECT * from `".$tbl_cat."` `node`
            WHERE code_P = '".$codeCat."'
                AND tree = '".$idTree."'
            ORDER by treePos
        ";
    }
    $resGetNodes = claro_sql_query($sqlGetNodes);
    while ($node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC))
    {
        $treePos++;
        $sqlUpdateTreePos ="UPDATE `".$tbl_cat."`
        SET treePos = '".$treePos."'
        WHERE code = '".$node["code"]."'";
        claro_sql_query($sqlUpdateTreePos);
        $treePos = resfreshTreePosNode($node["code"],$treePos);
    };
    return true;
}

/**
 * recompute tree index. and reset
 * treePos and nbChild values.
 * new indexing tree system
 * with a counter,
 * place value of counter on $posleft before
 * to scan child, and in posright after scan child
 * @param  $codeCat string code of category.
 * @param  $treePos integer starting treePos value.
 * @return true wether success
 * @author Christophe Gesché <moosh@claroline.net>
 */


function claro_reindex_tree($idTree, $idNode, $pos)
{

    global $tbl_node;
    if ($idNode=="NULL")
    {
        $sqlGetNodes = "
        SELECT * from `".$tbl_node."` `node`
            WHERE id_P is null
                AND tree='".$idTree."'
            ORDER by posLeft
        ";
    }
    else
    {
        $sqlGetNodes = "
        SELECT * from `".$tbl_node."` `node`
            WHERE id_P = '".$idNode."'
                AND tree='".$idTree."'
            ORDER by posLeft
        ";
    }
    $resGetNodes = claro_sql_query($sqlGetNodes);
    while ($node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC))
    {
        // before check child, update left
        $pos++;
        $sqlUpdateTreePos ="
        UPDATE `".$tbl_node."`
            SET posLeft = '".$pos."'
            WHERE id = '".$node["id"]."'";
        claro_sql_query($sqlUpdateTreePos);
        // check child
        $pos = resfreshTreePosNode($idTree, $node["code"], $pos);
        // after check child, update right
        $pos++;
        $sqlUpdateTreePos ="
        UPDATE `".$tbl_node."`
            SET posRight = '".$pos."'
            WHERE id = '".$node["id"]."'";
        claro_sql_query($sqlUpdateTreePos);

    };
    return $pos;
}

//original function
function getNodesListChild($codeParent)
{
    Global $tbl_cat;

    $sqlGetNodeParent = "select `node`.* from `".$tbl_cat."` `node`
        WHERE ";
        if ($codeParent == "NULL")
            $sqlGetNodeParent .= "`node`.`code_P` IS NULL";
        else
            $sqlGetNodeParent .= "`node`.`code_P`='".$codeParent."'";
    $sqlGetNodeParent .= " ORDER BY `node`.`treePos`";

    $resGetNodeParent = claro_sql_query($sqlGetNodeParent);
    while ($child =    mysql_fetch_array($resGetNodeParent,MYSQL_ASSOC))
    {
        $nodesListChild[] = $child;
    }

    return  $nodesListChild;

}

function claro_getNodesListChild($idParent)
{
    GLOBAL $tbl_node;

    $sqlGetNodeParent = "
    SELECT `node`.*
    FROM `".$tbl_cat."` `node`
        WHERE
            tree='".$idTree."'
        ";
    if ($codeParent == "NULL")
        $sqlGetNodeParent .= "
            `node`.`code_P` IS NULL";
    else
        $sqlGetNodeParent .= "
            `node`.`id_P`='".$idParent."'";
    $sqlGetNodeParent .= "
    ORDER BY `node`.`posLeft`";

    $resGetNodeParent = claro_sql_query($sqlGetNodeParent);
    while ($child =    mysql_fetch_array($resGetNodeParent,MYSQL_ASSOC))
    {
        $nodesListChild[] = $child;
    }

    return  $nodesListChild;

}

// original function
function refreshNbChildInBase($idCat)
{
    global $tbl_cat;
    $sqlUpdateNbChilds =
    "UPDATE `".$tbl_cat."` SET `nb_childs` = ".nbChildHardCount($idCat)."
    WHERE code = '".$idCat."'";
    $resUpdateNodes = claro_sql_query($sqlUpdateNbChilds);
    return ;
}



/**
 * recompute tree nbChilds and reset.
 * @param  $idNode integer, id of node
 * @return true wether success
 * @author Christophe Gesché <moosh@claroline.net>
  */

function claro_recount_NbChild($idNode)
{
    global $tbl_node;
    $sqlUpdateNbChilds ="
    UPDATE `".$tbl_cat."`
        SET `nb_childs` = ".nbChildHardCount($idCat)."
        WHERE id = '".$idNode."'";
    $resUpdateNodes = claro_sql_query($sqlUpdateNbChilds);
    return ;
}


//roginal
function refreshAllNbChildInBase()
{
    global $tbl_cat;

    $output="";

    $sqlGetChilds =
    "SELECT * FROM `".$tbl_cat."` `node` ";
    $resGetChilds = claro_sql_query($sqlGetChilds);
    while ($node = mysql_fetch_array($resGetChilds))
    {
        $output.= "<br>".$node["code"];
        $sqlUpdateNbChilds =
        "UPDATE `".$tbl_cat."` SET `nb_childs` = ".nbChildHardCount($node["code"])."
        WHERE code = '".$node["code"]."'";

        $resUpdateNodes = claro_sql_query($sqlUpdateNbChilds);
    }
    return $output;
}


function claro_recount_NbChild_for_all_nodes()
{
    global $tbl_node;

    $sqlGetChilds = "
    SELECT id
    FROM `".$tbl_node."` `node` ";
    $resGetChilds = claro_sql_query($sqlGetChilds);
    while ($node = mysql_fetch_array($resGetChilds))
    {
        $sqlUpdateNbChilds = "
        UPDATE `".$tbl_cat."`
            SET `nb_childs` = ".nbChildHardCount($node["id"])."
            WHERE id = '".$node["id"]."'";

        $resUpdateNodes = claro_sql_query($sqlUpdateNbChilds);
    }
    return true;
}





class node
{

    var  $id_node;
    var  $id_parent;
    var  $id_low;
    var  $id_hi;

    function node($id_node)
    {
        $sql = "select id_node, id_parent, id_low, id_hi
                FROM `".$tbl_cat."`
                WHERE id_node = '" . $node['id_node'] . "'";
        $res = claro_sql_query_fetch_all($sql);
        $this->id_node = $res[0]['id_node'];
        $this->id_parent = $res[0]['id_parent'];
        $this->id_low = $res[0]['id_low'];
        $this->id_hi = $res[0]['id_hi'];
    }

    function get_node_indexes($id_node)
    {
        $sql = "select id_node, id_parent, id_low, id_hi
                FROM `".$tbl_cat."`
                WHERE id_node = '" . $node['id_node'] . "'";
        $res = claro_sql_query_fetch_all($sql);
        return $res[0];
    }

    function count_childs_node($id_node=null)
    {
        if (is_null($id_node))
        {
            $id_low = $this->$id_low;
            $id_hi = $this->$id_hi;
        }
        else
        {
            $idx = get_node_indexes($id_node);
            $id_hi = $idx['id_hi'];
            $id_low = $idx['id_low'];
        }
        return ($id_hi - $id_low -1 ) / 2;
    }

    function get_childs_node($id_node=null)
    {
        if (is_null($id_node))
        {
            $id_low = $this->$id_low;
            $id_hi = $this->$id_hi;
        }
        else
        {
            $idx = get_node_indexes($id_node);
            $id_hi = $idx['id_hi'];
            $id_low = $idx['id_low'];
        }
        return range($id_node+1, $id_node + ($id_hi - $id_low -1 ) / 2);
    }



     /**
      *
      * @param integer $node_a
      * @param integer $node_b
      * @return
      * @author Christophe Gesché <moosh@claroline.net>
      *
      */
     function swap( $id_node_a, $id_node_b=null)
     {
     	$node_a = new node($id_node_a);
     	if (is_null($id_node_b))
     	{
     	    $node_b = $this;
     	}
     	else
     	{
     	    $node_b = new node($id_node_b);
     	}

     	if ($node_a->id_low < $node_b->id_low)
     	{
     	     $node_Up_idParent  = $node_b->id_parent;
     	     $node_Down_idParent  = $node_a->id_parent;
     	}
        else
     	{
     	     $node_toUp  = $node_b;
     	     $node_toDown  = $node_a;
     	}



     	return $success;
     }


    function get_childs_element($id_node)
    {
        if (is_null($id_node))
        {
            $id_low = $this->$id_low;
            $id_hi = $this->$id_hi;
        }
        else
        {
            $idx = get_node_indexes($id_node);
            $id_hi = $idx['id_hi'];
            $id_low = $idx['id_low'];
        }

        $sql = "select node." . $field_id_node  . " id_node, element.*
                FROM `" . $tbl_element . "` element
                JOIN `" . $tbl_cat . "` node
                ON element." . $field_element_fk_node . " = node." . $field_id_node  . "
                WHERE
                 node." . $field_id_low  . "  > '" . $id_low . "'
                AND
                 node." . $field_id_hi  . "  < '" . $id_hi . "'";

        return claro_sql_query($sql);
    }

    function count_childs_element($id_node)
    {
        if (is_null($id_node))
        {
            $id_low = $this->$id_low;
            $id_hi = $this->$id_hi;
        }
        else
        {
            $idx = get_node_indexes($id_node);
            $id_hi = $idx['id_hi'];
            $id_low = $idx['id_low'];
        }

        $sql = "select count(element.*) qty
                FROM `" . $tbl_element . "` element
                JOIN `" . $tbl_cat . "` node
                ON element." . $field_element_fk_node . " = node." . $field_id_node  . "
                WHERE
                 node." . $field_id_low  . "  > '" . $id_low . "'
                AND
                 node." . $field_id_hi  . "  < '" . $id_hi . "'";

        return claro_sql_query_get_single_value($sql);
    }

}
?>