<?php // $Id$

/**
 * CLAROLINE
 *
 * Folder cleaner : recursively remove the contents of a given folder
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Frédéric Minne <zefredz@claroline.net>
 */

class FolderCleaner
{
    private $path;
    
    /**
     * Construct a folder cleaner for the given folder path
     * @param string $path path to the folder to empty
     * @throws Exception if the given path does not exists or not a folder
     */
    public function __construct ( $path )
    {
        if ( !file_exists ( $path ) )
        {
            throw new Exception( get_lang( 'Folder %path does not exists', array( '%path' => $path ) ) );
        }
        
        if ( ! is_dir( $path ) )
        {
            throw new Exception( get_lang('The given path %path is not a valid folder', array( '%path' => $path ) ) );
        }
        
        $this->path = $path;
    }
    
    /**
     * Empty the folder
     * @return array( 'files' => list of path removed, 'folders' => list of folders removed )
     * @throws Exception if the given path does not exists or not a folder
     */
    public function clean()
    {
        if ( !file_exists ( $this->path ) )
        {
            throw new Exception( get_lang( 'Folder %path does not exists', array( '%path' => $this->path ) ) );
        }
        
        if ( ! is_dir( $this->path ) )
        {
            throw new Exception( get_lang('The given path %path is not a valid folder', array( '%path' => $this->path ) ) );
        }
        
        $pathRemoved = array(
            'files' => array(),
            'folders' => array()
        );
        
        foreach ( new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->path, 
                FilesystemIterator::SKIP_DOTS), 
            RecursiveIteratorIterator::CHILD_FIRST) as $file)
        {
            if($file->isDir())
            {   
                rmdir($file->getPathname());
                $pathRemoved['folders'][] = $file->getPathname();
            }
            else
            {   
                unlink($file->getPathname());
                $pathRemoved['files'][] = $file->getPathname();
            }   
        }
        
        return $pathRemoved;
    }
}
