<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Garbage Collector
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.file
 */

/**
 * Garbage collector : remove old files from a given folder
 */
class ClaroGarbageCollector
{
    private $path, $expire, $maxLifeTime;

    /**
     * Constructor
     * @param string $path folder path
     * @param int $expire expiration time
     */
    public function  __construct( $path, $maxLifeTime = 3600 )
    {
        $this->path = $path;
        $this->maxLifeTime = $maxLifeTime;
        $this->expire = time() - $maxLifeTime;
    }

    /**
     * Run the garbage collector
     */
    public function run()
    {
        if ( is_dir( $this->path ) )
        {
            // control that path is not in a system folder
            
            if ( strpos( $this->path, get_path('coursesRepositorySys') ) !== false )
            {
                Console::warning("GC directory {$this->path} located in platform course folder : ABORT!");
                return;
            }
            
            if ( strpos( $this->path, get_path('clarolineRepositorySys') ) !== false )
            {
                Console::warning("GC directory {$this->path} located in platform main folder : ABORT!");
                return;
            }
            
            if ( strpos( $this->path, get_path('rootSys').'/web' ) !== false )
            {
                Console::warning("GC directory {$this->path} located in platform web folder : ABORT!");
                return;
            }
            
            if ( strpos( $this->path, get_path('rootSys').'/module' ) !== false )
            {
                Console::warning("GC directory {$this->path} located in platform modules dir : ABORT!");
                return;
            }
            
            Console::debug('GC Called in '.$this->path);

            // Delete archive files older than one hour
            $directoryIterator = new RecursiveDirectoryIterator( $this->path );
            //$directoryIterator->setFlags(FilesystemIterator::SKIP_DOTS);
            $tempDirectoryFiles = new RecursiveIteratorIterator( $directoryIterator );

            foreach ( $tempDirectoryFiles as $tempDirectoryFile )
            {
                if ( $tempDirectoryFile->isReadable() 
                    && $tempDirectoryFile->isWritable() )
                {
                    if ( $tempDirectoryFile->getMTime() < $this->expire )
                    {
                        if ( !$tempDirectoryFile->isDir() 
                            /* && !$tempDirectoryFile->isDot() */ )
                        {
                            Console::debug(
                                'Unlink '
                                    . $tempDirectoryFile->getPathName()
                                    . " mtime: ".$tempDirectoryFile->getMTime()
                                    . "; expire: ".$this->expire
                            );

                            @unlink( $tempDirectoryFile->getPathName() );
                        }
                        elseif ( $tempDirectoryFile->isDir() 
                                && $this->isEmpty( $tempDirectoryFile->getPathName() ) )
                        {
                            Console::debug(
                                'Rmdir '
                                    . $tempDirectoryFile->getPathName()
                                    . " mtime: ".$tempDirectoryFile->getMTime()
                                    . "; expire: ".$this->expire
                            );

                            @rmdir( $tempDirectoryFile->getPathName() );
                        }
                    }
                }
            }
        }
        else
        {
            Console::warning("GC directory {$this->path} is not a folder folder : ABORT!");
        }
    }
    
    /**
     * Check if the folder corresponding to the given path is empty
     * @param string $path
     * @return boolean
     */
    protected function isEmpty( $path )
    {
        return ( ( $files = @scandir($path) ) && ( count($files) <= 2 ) );
    }
}
