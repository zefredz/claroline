<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * user list strategy
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


//load messagestrategy class
require_once dirname(__FILE__) . '/selectorstrategy/selectorstrategy.lib.php';

class UserStrategy implements SelectorStrategy 
{
    const ORDER_BY_NAME = "nom %order%, prenom %order%";
    const ORDER_BY_USERNAME = "username %order%";
    
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    
    protected $nameSearch = "";
    
    protected $fieldOrder = self::ORDER_BY_NAME;
    
    protected $numberOfUserPerPage;
    protected $pageToDisplay = 1;
    
    
    
    public function __construct()
    {
        $this->numberOfUserPerPage = get_conf('userPerPage',25);
    }
    
    public function getNumberOfUserPerPage()
    {
        return $this->numberOfUserPerPage;
    }
    
    
    public function getStrategy()
    {
        if($this->nameSearch == "*")
        {
            $this->nameSearch = '';
        }
        return "WHERE nom like '%".$this->nameSearch."%' OR prenom like '%".$this->nameSearch."%' OR username like '%".$this->nameSearch."%' OR "
        ."CONCAT(nom,' ',prenom) like '%".$this->nameSearch."%' OR CONCAT(prenom,' ',nom) like '%".$this->nameSearch."%'";
    }
    
    public function setPageToDisplay($page)
    {
        $this->pageToDisplay = $page;
    }
    
    public function getLimit()
    {
        if ($this->numberOfUserPerPage <= 0)
        {
            throw new Exception("The number of user per page must be positif and not null");
        }
        
        if ($this->pageToDisplay <= 0)
        {
            throw new Exception("The page to display must be positif and not null");
        }
        
        return " LIMIT " . (int)($this->pageToDisplay - 1)*$this->numberOfUserPerPage . ", " 
            . (int)$this->numberOfUserPerPage."\n";
    }
    
    public function setFieldOrder($fieldOrder)
    {
        if($fieldOrder == self::ORDER_BY_NAME || $fieldOrder == self::ORDER_BY_USERNAME)
        {
            $this->fieldOrder = $fieldOrder;
        }
        else
        {
            throw new Exception('Invalid order field');
        }
    }
    
    /**
     * Set the order of search
     *
     * @param string $order: accpeted value UserStrategy::ORDER_DESC and UserStrategy::ORDER_ASC
     */
    public function setOrder($order)
    {
        if ($order == self::ORDER_ASC
              || $order == self::ORDER_DESC)
        {
            $this->order = $order;
        }
    }
    
    public function getOrder()
    {
        $orderString = $this->fieldOrder;
        $orderString = str_replace('%order%',$this->order,$orderString);
        return " ORDER BY ".$orderString."\n";
    }
    
    public function setSearch($name)
    {
        $this->nameSearch = $name; 
    }
}
