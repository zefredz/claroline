<?php

class sso
{
    private $driversList;
    private $driversDir;
    private $drivers;
    
    public function __construct()
    {
        $this->driversDir = claro_get_conf_repository() . '/sso/';
        $this->drivers = array();
        $this->loadDrivers();        
        
    }
    
    private function loadDriversList()
    {
        $dir = new DirectoryIterator( $this->driversDir );
        
        $drivers = array();
        
        foreach( $dir as $fileInfo )
        {
            if( ! ( $fileInfo->isDot()|| $fileInfo->isDir() ) && substr($fileInfo->getFilename(), -4) == '.php' )
            {
                $drivers[] = $fileInfo->getFilename();
            }            
        }
        
        $this->driversList = $drivers;
        
        return true;
    }
    
    public function loadDrivers()
    {
        $this->loadDriversList();
        
        foreach( $this->driversList as $driver )
        {
            include_once( $this->driversDir . '/' . $driver );
        }
        
        if( isset( $driverConfig  ) )
        {
            
            $this->drivers = array_merge( $this->drivers, $driverConfig);
        }
    }
    
    public function getDriversList()
    {
        if( !is_array( $this->driversList ) )
        {
            $this->loadDriversList();
        }
        return $this->driversList;
    }
    
    public function getDrivers()
    {
        return $this->drivers;
    }
}

?>