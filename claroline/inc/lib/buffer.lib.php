<?php

$tbl_name = claro_sql_get_main_tbl();

$tbl_module = $tbl_name['module'];
$tbl_dock = $tbl_name['dock'];

class Buffer
{
   var $buffer;
       
   function Buffer()
   {
       $this->buffer = '';
   }
       
   function init()
   {
       $this->buffer = '';
   }
       
   function clear()
   {
       $this->buffer = '';
   }
       
   function append( $str )
   {
       $this->buffer .= $str;
   }
       
   function getContent()
   {
       return $this->buffer;
   }
   
   function flush()
   {
       $buffer = $this->buffer;
       $this->clear();
       return $buffer;
   }
}


function getAppletList($dock)
{
    global $tbl_module;
    global $tbl_dock;
    global $includePath;          
                        
    $sql = "SELECT * 
              FROM `".$tbl_module."` AS M, `".$tbl_dock."` AS D
             WHERE D.`name` = '".$dock->name."'
               AND D.`module_id` = M.`id`
               AND M.`activation` = 'activated' 
              ";
    $module_list = claro_sql_query_fetch_all($sql);
    
    $appletList = array();
                          
    //include each entry point of plugins supposed to display output in this buffer
          
    foreach ($module_list as $module)
    {
        if (file_exists($includePath.'/../module/'.$module['label'].'/entry.php')) 
        {                        
            $applet = array();
            $applet['path']  = $includePath.'/../module/'.$module['label'].'/entry.php';
            $applet['label'] = $module['label'];
            $appletList[]    = $applet;               
        }
    }
        
    return $appletList;                               
} 
   
/* Dock class to contain the display buffered by the modules */

class Dock
{
    var $name;
    var $appletList;
    var $kernelOutput;
    var $kernelOutputAtEnd;
     
    function Dock($name, $appletList = array())
    {
       $this->name = $name;
       $this->appletList = $appletList;
    }
    
    function setAppletList($appletList = array())
    {
       $this->appletList = $appletList;
    }
    
    function addElement($applet)
    {
       $this->appletList[] = $applet;
    }
    
    function addOutput($string, $atEnd = false)
    {
        if ($atEnd)
        {
           $this->kernelOutputAtEnd .= $string; 
        }
        else
        {
           $this->kernelOutput .= $string;
        }   
    }
         
    function render()
    {     
       $claro_buffer = new Buffer();
       $claro_buffer->append('<div id="' . $this->name.'" class="dock">' . "\n");
       
       $claro_buffer->append($this->kernelOutput);
             
       foreach ( $this->appletList as $applet )
       {
          # entry appends appletoutput to $buffer           
          include $applet['path'];
       }
       
       $claro_buffer->append($this->kernelOutputAtEnd);
       
       $claro_buffer->append('</div>' . "\n");
       
       return $claro_buffer->getContent();
    }
}
?>
