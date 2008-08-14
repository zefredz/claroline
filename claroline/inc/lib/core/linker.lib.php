<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Claroline Resource Linker library
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     linker
 */

FromKernel::uses('core/url.lib', 'group.lib.inc');

interface ResourceLocator 
{
}

/**
 * Define a Claroline resource locator and provides a static method to parse a CRL into a locator
 *
 */
class ClarolineResourceLocator implements ResourceLocator
{
    protected $platformId,
            $courseId,
            $moduleLabel,
            $resourceId,
            $teamId;
            
    public function __construct( 
            $courseId = null,
            $moduleLabel = null,
            $resourceId = null,
            $teamId = null )
    {
        $this->platformId = get_conf('platform_id');
        $this->courseId = $courseId;
        $this->moduleLabel = rtrim( $moduleLabel, '_' );
        $this->resourceId = $resourceId;
        $this->teamId = $teamId;
    }
    
    public function getPlatformId()
    {
        return $this->platformId;
    }
    
    public function setPlatformId( $platformId )
    {
        $this->platformId = $platformId;
    }
    
    public function getCourseId()
    {
        return $this->courseId;
    }
    
    public function setCourseId( $courseId )
    {
        $this->courseId = $courseId;
    }
    
    public function inCourse()
    {
        return !empty( $this->courseId );
    }
    
    public function getModuleLabel()
    {
        return $this->moduleLabel;
    }
    
    public function setModuleLabel( $moduleLabel )
    {
        $this->moduleLabel = $moduleLabel;
    }
    
    public function inModule()
    {
        return !empty( $this->moduleLabel );
    }
    
    public function getGroupId()
    {
        return $this->teamId;
    }
    
    public function setGroupId( $teamId )
    {
        $this->teamId = $teamId;
    }
    
    public function inGroup()
    {
        return !empty( $this->teamId );
    }
    
    public function getResourceId()
    {
        return $this->resourceId;
    }
    
    public function setResourceId( $ressourceId )
    {
        $this->resourceId = $ressourceId;
    }
    
    public function hasResourceId()
    {
        return !empty( $this->resourceId );
    }
    
    public function __toString()
    {
        $crl = "crl://claroline.net/{$this->platformId}/{$this->courseId}";
        
        if ( !empty($this->teamId) )
        {
            $crl.= "/groups/{$this->teamId}";
        }
        
        if ( !empty($this->moduleLabel) )
        {
            $crl.= "/{$this->moduleLabel}";
            
            if ( !empty($this->resourceId) )
            {
                $crl.= "/{$this->resourceId}";
            }
        }
        
        return $crl;
    }
    
    public static function parse( $locatorString )
    {
        if ( ! preg_match( '~^crl\://~', $locatorString )
            && preg_match( '~^([a-zA-Z0-9]\://|[a-zA-Z0-9]\:)~', $locatorString ) )
        {
            return new ExternalResourceLocator( $url );
        }
        
        $matches = array();
        
        $locatorString = rtrim( $locatorString, '/' );
        
        if ( preg_match( '~^crl://claroline\.net/(\w+)/(\w+)$~', $locatorString, $matches ) )
        {
            // course
            $locator = new self( $matches[2] );
            $locator->setPlatformId( $matches[1] );
        }
        elseif ( preg_match( '~^crl://claroline\.net/(\w+)/(\w+)/groups/(\d+)$~', $locatorString, $matches ) )
        {
            // course and group
            $locator = new self( $matches[2], null, null, $matches[3] );
            $locator->setPlatformId( $matches[1] );
        }
        elseif ( preg_match( '~^crl://claroline\.net/(\w+)/(\w+)/groups/(\d+)/(\w+)$~', $locatorString, $matches ) )
        {
            // course, group and tool
            $locator = new self( $matches[2], $matches[4], null, $matches[3] );
            $locator->setPlatformId( $matches[1] );
        }
        elseif ( preg_match( '~^crl://claroline\.net/(\w+)/(\w+)/groups/(\d+)/(\w+)/(.+)$~', $locatorString, $matches ) )
        {
            // course, group, tool and resource
            $locator = new self( $matches[2], $matches[4], $matches[5], $matches[3] );
            $locator->setPlatformId( $matches[1] );
        }
        elseif ( preg_match( '~^crl://claroline\.net/(\w+)/(\w+)/(\w+)/(.+)$~', $locatorString, $matches ) )
        {
            // course, tool and resource
            $locator = new self( $matches[2], $matches[3], $matches[4] );
            $locator->setPlatformId( $matches[1] );
        }
        elseif ( preg_match( '~^crl://claroline\.net/(\w+)/(\w+)/(.+)$~', $locatorString, $matches ) )
        {
            // a course and a tool
            $locator = new self( $matches[2], $matches[3] );
            $locator->setPlatformId( $matches[1] );
        }
        elseif ( preg_match( '~^crl://claroline\.net/(\w+)$~', $locatorString, $matches ) )
        {
            // the platform itself
            $locator = new self();
            $locator->setPlatformId( $matches[1] );
        }
        else
        {
            // ???? error ????
            throw new Exception("Invalid Resource Locator {$locatorString}");
        }
        
        return $locator;
    }
}

/**
 * Defines a locator for external links...
 *
 */
class ExternalResourceLocator implements ResourceLocator
{
    protected $url;
    
    public function __construct( $url )
    {
        $this->url = $url;
    }
    
    public function __toString()
    {
        return $this->url;
    }
}

/**
 * Defines a resource
 *
 */
class LinkerResource
{
    protected $isLinkable;
    protected $isVisible;
    protected $isNavigable;
    protected $locator;
    protected $name;
    
    public function __construct( $name, ResourceLocator $locator, $isLinkable = true, $isVisible = true, $isNavigable = false)
    {
        $this->isLinkable = $isLinkable;
        $this->isVisible = $isVisible;
        $this->isNavigable = $isNavigable;
        $this->name = $name;
        $this->locator = $locator;
    }
    
    public function isVisible()
    {
        return $this->isVisible;
    }
    
    public function isLinkable()
    {
        return $this->isLinkable;
    }
    
    public function isNavigable()
    {
        return $this->isNavigable;
    }
    
    public function getLocator()
    {
        return $this->locator;
    }
    
    public function getName()
    {
        return $this->name;
    }
}

/**
 * Defines a resource that contains other resources such as 
 * a tool or a directory in document tool
 *
 */
class LinkerResourceIterator
    implements SeekableIterator, Countable
{
    protected $elementList;
    
    public function __construct(  )
    {
        $this->elementList = array();
    }
    
    public function addResource( $resource )
    {
        $this->elementList[] = $resource;
    }
    
    public function first()
    {
        $this->seek(0);
        return $this->current();
    }
    
    public function last()
    {
        $this->seek( count( $this->elementList ) - 1 );
        return $this->current();
    }
    
    // Countable
    
    public function count()
    {
        return count( $this->elementList );
    }
    
    // Iterator
    
    protected $idx = 0;
    
    public function valid()
    {
        return !empty($this->elementList)
            && $this->idx >= 0
            && $this->idx < count( $this );
    }
    
    public function rewind()
    {
        $this->idx = 0;
    }
    
    public function next()
    {
        $this->idx++;
    }
    
    public function current()
    {
        return $this->elementList[$this->idx];
    }
    
    public function key()
    {
        return $this->idx;
    }
    
    // SeekableIterator
    
    public function seek( $index )
    {
        $this->idx = $index;
        
        if ( !$this->valid() )
        {
            throw new OutOfBoundsException('Invalid seek position');
        }
    }
}

/**
 * Translate a locator to a real url and allows to find the name of a resource
 * from its locator
 *
 */
class ResourceLinkerResolver
{
    public function resolve( ResourceLocator $locator )
    {
        if ( $locator instanceof ExternalResourceLocator )
        {
            return new Url( $locator->__toString() );
        }
        else // ClarolineResourceLocator
        {
            // 1 . get most accurate resolver
            //  1.1 if Module
            if ( $locator->inModule() )
            {
                $resolver = $this->loadModuleResolver( $locator->getModuleLabel() );
            }
            //  1.2 elseif Group
            elseif ( $locator->inGroup() )
            {
                $resolver = new GroupResolver;
            }
            //  1.3 elseif Course
            elseif ( $locator->inCourse() )
            {
                $resolver = new CourseResolver;
            }
            
            //  1.4 get base url
            if( $resolver )
            {
                $url = $resolver->resolve( $locator );
            }
            else
            {
                $url = get_path('rootWeb');
            }
            
            $urlObj = new Url( $url );
            
            // 2. add context information
            $context = Claro_Context::getCurrentContext();
            
            if ( $locator->inGroup() )
            {
                $context[CLARO_CONTEXT_GROUP] = $locator->getGroupId();
            }
            else
            {
                $context[CLARO_CONTEXT_GROUP] = null;
            }
            
            if ( $locator->inCourse() )
            {
                $context[CLARO_CONTEXT_COURSE] = $locator->getCourseId();
            }
            else
            {
                $context[CLARO_CONTEXT_COURSE] = null;
            }
            
            $urlObj->relayContext( Claro_Context::getUrlContext( $context ) );
            
            return $urlObj->toUrl();
        }
    }
    
    public function loadModuleResolver( $moduleLabel )
    {
        $resolverClass = $moduleLabel . '_Resolver';
        
        if ( ! class_exists( $resolverClass ) )
        {
            $resolverPath = get_module_path( $moduleLabel ) . '/connector/linker.cnr.php';
            
            if ( file_exists( $resolverPath ) )
            {
                include_once $resolverPath;
            }
        }
        
        if ( class_exists( $resolverClass ) )
        {
            $resolver = new $resolverClass();
            
            return $resolver;
        }
        
        return false;
    }
    
    public function getResourceName( ResourceLocator $locator )
    {
        if ( $locator instanceof ExternalResourceLocator )
        {
            return $locator->__toString();
        }
        else // ClarolineResourceLocator
        {
            $nameParts = array();
            
            if ( $locator->inCourse() )
            {
                $resolver = new CourseResolver;
                $nameParts[] = $resolver->getResourceName( $locator );
            }
            
            if ( $locator->inGroup() )
            {
                $resolver = new GroupResolver;
                $nameParts[] = $resolver->getResourceName( $locator );
            }
            
            if ( $locator->inModule() && get_module_data($locator->getModuleLabel() ) )
            {
                $nameParts[] = get_module_data($locator->getModuleLabel(), 'moduleName' );
            }
            
            if( $locator->inModule() && $locator->hasResourceId() )
            {
                $resolver = $this->loadModuleResolver( $locator->getModuleLabel() );
                $nameParts[] = $resolver->getResourceName( $locator );
            }
            
            return implode(' > ', $nameParts);
        }
    }
}

/**
 * Resolver for course
 */
class CourseResolver
{
    public function resolve( ResourceLocator $locator )
    {
        return get_path('clarolineRepositoryWeb') . 'course/index.php?cid='.$locator->getCourseId(); 
    }
    
    public function getResourceName( ResourceLocator $locator )
    {
        $courseData = claro_get_course_data( $locator->getCourseId() );
        
        return $courseData['officialCode'] . ' : ' . $courseData['name'];
    }
}

/**
 * Resolver for group
 */
class GroupResolver
{
    public function resolve( ResourceLocator $locator )
    {
        return get_path('clarolineRepositoryWeb') . 'group/group_space.php';
    }
    
    public function getResourceName( ResourceLocator $locator )
    {
        $groupData = claro_get_group_data( array(
            CLARO_CONTEXT_COURSE => $locator->getCourseId(),
            CLARO_CONTEXT_GROUP => $locator->getGroupId() ) );
        
        return $groupData['name'];
    }
}

/**
 * Interface that should be implemented in each module
 *
 */
interface ModuleResourceResolver
{
    public function resolve( ResourceLocator $locator );
    public function getResourceName( ResourceLocator $locator );
}

class CLEXT_Resolver implements ModuleResourceResolver
{
    public function resolve( ResourceLocator $locator )
    {
        return $locator->getResourceId();
    }
    
    public function getResourceName( ResourceLocator $locator )
    {
        $externalCourseToolList = claro_get_course_external_link_list( $locator->getCourseId() );
        
        $url = $locator->getResourceId();
        
        foreach ( $externalCourseToolList as $externalCourseTool )
        {
            if ( $externalCourseTool['url'] == $url )
            {
                return $externalCourseTool['name'];
            }
        }
        
        return $url;
    }
}

/**
 * Returns the list of available resources from a locator
 *
 */
class ResourceLinkerNavigator
{
    public function getResourceList( ResourceLocator $rootNodeLocator = null )
    {
        $rootNodeLocator = empty( $rootNodeLocator )
            ? new ClarolineResourceLocator( claro_get_current_course_id() )
            : $rootNodeLocator
            ;
            
        if ( $rootNodeLocator->inGroup()
            && ! $rootNodeLocator->inModule() )
        {
            $navigator = new GroupNavigator;
            return $navigator->getResourceList($rootNodeLocator);
        }
        elseif ( $rootNodeLocator->inCourse()
            && ! $rootNodeLocator->inModule() )
        {
            $navigator = new CourseNavigator;
            return $navigator->getResourceList($rootNodeLocator);
        }
        elseif ( $rootNodeLocator->inModule() )
        {
            $navigator = self::loadModuleNavigator( $rootNodeLocator->getModuleLabel() );
                
            if ( $navigator )
            {
                return $navigator->getResourceList($rootNodeLocator);
            }
            else
            {
                return $this->moduleResource( $rootNodeLocator->getModuleLabel() );
            }
            
        }
        else
        {
            throw new Exception( "Not supported yet !" );
        }
    }
    
    protected function moduleResource( $moduleLabel, ResourceLocator $rootNodeLocator )
    {
        $resource = new LinkerResource( $moduleLabel, $rootNodeLocator, true, claro_is_tool_visible($moduleLabel) );
        
        return $resource;
    }
    
    public static function loadModuleNavigator( $moduleLabel )
    {
        $navigatorClass = $moduleLabel . '_Navigator';
        
        if ( ! class_exists( $navigatorClass ) )
        {
            $navigatorPath = get_module_path( $moduleLabel ) . '/connector/linker.cnr.php';
            
            if ( file_exists( $navigatorPath ) )
            {
                include_once $navigatorPath;
            }
        }
        
        if ( class_exists( $navigatorClass ) )
        {
            $navigator = new $navigatorClass();
            
            return $navigator;
        }
        
        return false;
    }
    
    public function getCurrentLocator( $params = array() )
    {
        $locator = new ClarolineResourceLocator;
        
        if ( claro_is_in_a_course() )
        {
            $locator->setCourseId( claro_get_current_course_id() );
        }
        
        if ( claro_is_in_a_group() )
        {
            $locator->setGroupId( claro_get_current_group_id() );
        }
        
        if ( get_current_module_label() )
        {
            $locator->setModuleLabel(get_current_module_label());
            
            $navigator = $this->loadModuleNavigator( get_current_module_label() );
            
            if ( $resourceId = $navigator->getResourceId( $params ) )
            {
                $locator->setResourceId( $resourceId );
            }
        }
        
        return $locator;
    }
}

/**
 * Defines a basic ResourceNavigator
 *
 */
interface ResourceNavigator
{
    public function getResourceList( ResourceLocator $rootNodeLocator );
}

/**
 * Interface that should be implemented in each module
 *
 */
interface ModuleResourceNavigator extends ResourceNavigator
{
    public function getResourceId( $params = array() );
}

/**
 * This navigator is mainly used to link resources from course home page
 *
 */
class CLHOME_Navigator implements ModuleResourceNavigator
{
    public function getResourceList( ResourceLocator $rootNodeLocator )
    {
        // should not be called
    }
    
    public function getResourceId( $params = array() )
    {
        if ( ! isset($params['id']) )
        {
            throw new Exception("Missing parameter");
        }
        
        return $params['id'];
    }
}

/**
 * This navigator allows navigation through tools of a course
 *
 */
class CourseNavigator implements ResourceNavigator
{
    public function getResourceList( ResourceLocator $rootNodeLocator )
    {
        $courseToolList = claro_get_course_tool_list(
            $rootNodeLocator->getCourseId(),
            claro_get_current_user_profile_id_in_course( $rootNodeLocator->getCourseId() )
        );
        
        $courseResource = new LinkerResourceIterator();
        
        foreach ( $courseToolList as $courseTool )
        {
            if( ! is_null( $courseTool['label'] ) )
            {
                $locator = new ClarolineResourceLocator(
                    $rootNodeLocator->getCourseId(),
                    $courseTool['label']
                );
            }
            else
            {
                $locator = new ExternalResourceLocator( $courseTool['url'] );
            }
            
            if ( ! is_null( $courseTool['label'] )
                && ResourceLinkerNavigator::loadModuleNavigator( $courseTool['label'] ) )
            {
                $isNavigable = true;
            }
            else
            {
                $isNavigable = false;
            }
            
            $resource = new LinkerResource(
                $courseTool['name'],
                $locator,
                true,
                $courseTool['visibility'] ? true : false,
                $isNavigable
            );
            
            $courseResource->addResource( $resource );
        }
        
        return $courseResource;
    }
}

/**
 * Thie navigator allows to navigate through tools of groups
 *
 */
class GroupNavigator implements ResourceNavigator
{
    public function getResourceList( ResourceLocator $rootNodeLocator )
    {
        $groupToolList = get_activated_group_tool_label_list( $rootNodeLocator->getCourseId() );
        $groupProperties = claro_get_main_group_properties($rootNodeLocator->getCourseId());
        
        $groupResource = new LinkerResourceIterator();
        
        foreach ( $groupToolList as $groupTool )
        {
            // skip disabled group tools
            if ( ! array_key_exists( $groupTool['label'], $groupProperties['tools'] )
                || ! $groupProperties['tools'][$groupTool['label']] )
            {
                continue;
            }
            
            $locator = new ClarolineResourceLocator(
                $rootNodeLocator->getCourseId(),
                $groupTool['label'],
                null,
                $rootNodeLocator->getGroupId()
            );
            
            if ( ResourceLinkerNavigator::loadModuleNavigator( $groupTool['label'] ) )
            {
                $isNavigable = true;
            }
            else
            {
                $isNavigable = false;
            }
            
            $resource = new LinkerResource(
                $groupTool['name'],
                $locator,
                true,
                $groupTool['visibility'] ? true : false,
                $isNavigable
            );
            
            $groupResource->addResource( $resource );
        }
        
        return $groupResource;
    }
}

/**
 * A helper for main functions
 *
 */
class ResourceLinker
{
    public static $Resolver;
    public static $Navigator;
    
    private static $_initialized = false;
    
    public static function init()
    {
        if ( ! self::$_initialized )
        {
            self::$Navigator = new ResourceLinkerNavigator;
            self::$Resolver = new ResourceLinkerResolver;
            
            self::$_initialized = true;
        }
    }
    
    /**
     * Get a resource URL from its parameters
     *
     * @param integer $courseId
     * @param string $moduleLabel
     * @param mixed $resourceId
     * @param integer $teamId
     * @return string url of resource
     */
    public static function getRessourceUrl(
        $courseId,
        $moduleLabel = null,
        $resourceId= null,
        $teamId = null )
    {
        $locator = new ClarolineResourceLocator(
            $courseId,
            $moduleLabel,
            $resourceId,
            $teamId );
        
        return self::$Resolver->resolve( $locator );
    }
    
    public static function getLocator( $params )
    {
        return self::$Navigator->getLocator( $params );
    }
    
    public static function getLocatorIdAndAddIfMissing( $crl )
    {
        $tbl = claro_sql_get_course_tbl();
        
        $sql = "SELECT `id` FROM `{$tbl['resources']}`\n"
            . "WHERE BINARY `crl` = " . Claroline::getDatabase()->quote($crl)
            ;
        
        $res = Claroline::getDatabase()->query( $sql );
        
        if ( $res->numRows() )
        {
            return (int) $res->fetch( Database_ResultSet::FETCH_VALUE );
        }
        else
        {
            $sql = "INSERT INTO `{$tbl['resources']}`\n"
                . "SET\n"
                . "`crl` = " . Claroline::getDatabase()->quote($crl) ."\n"
                . ",\n"
                . "`title` = " . Claroline::getDatabase()->quote(
                    self::getResourceName(
                        ClarolineResourceLocator::parse( $crl ) ) )
                ;
            
            Claroline::getDatabase()->exec ( $sql );
            
            return (int) Claroline::getDatabase()->insertId();
        }
    }
    
    public static function addLink( $crlFrom, $crlTo )
    {
        $crlFromId = self::getLocatorIdAndAddIfMissing( $crlFrom );
        $crlToId = self::getLocatorIdAndAddIfMissing( $crlTo );
        
        $tbl = claro_sql_get_course_tbl();
        
        $sql = "SELECT `id` FROM `{$tbl['links']}`\n"
            . "WHERE `src_id` = " . Claroline::getDatabase()->escape( $crlFromId ) ."\n"
            . "AND\n"
            . "`dest_id` = " . Claroline::getDatabase()->escape( $crlToId )
            ;
        
        $res = Claroline::getDatabase()->query( $sql );
        
        if ( $res->numRows() )
        {
            return false;
        }
        else
        {
            $sql = "INSERT INTO `{$tbl['links']}`\n"
                . "SET\n"
                . "`src_id` = " . Claroline::getDatabase()->escape( $crlFromId ) ."\n"
                . ",\n"
                . "`dest_id` = " . Claroline::getDatabase()->escape( $crlToId )
                ;
            
            Claroline::getDatabase()->exec ( $sql );
            
            return true;
        }
    }
    
    public static function removeLink( $crlFrom, $crlTo )
    {
        $crlFromId = self::getLocatorIdAndAddIfMissing( $crlFrom );
        $crlToId = self::getLocatorIdAndAddIfMissing( $crlTo );
        
        $tbl = claro_sql_get_course_tbl();
        
        $sql = "DELETE FROM `{$tbl['links']}`\n"
            . "WHERE\n"
            . "`src_id` = " . Claroline::getDatabase()->escape( $crlFromId ) . "\n"
            . "AND\n"
            . "`dest_id` = " . Claroline::getDatabase()->escape( $crlToId )
            ;
        
        Claroline::getDatabase()->exec ( $sql );
        
        return Claroline::getDatabase()->affectedRows();
    }
    
    public static function getLinkList( $crlFrom )
    {
        $tbl = claro_sql_get_course_tbl();
        
        $sql = "SELECT `dest`.`crl`, `dest`.`title`\n"
            . "FROM `{$tbl['links']}` AS `lnk`,\n"
            . "`{$tbl['resources']}` AS `dest`,\n"
            . "`{$tbl['resources']}` AS `src`\n"
            . "WHERE `src`.`crl` = " . Claroline::getDatabase()->quote( $crlFrom ) . "\n"
            . "AND `dest`.`id` = `lnk`.`dest_id`\n"
            . "AND `src`.`id` = `lnk`.`src_id`\n"
            ;
            
        $res = Claroline::getDatabase()->query( $sql );
        
        return $res;
    }
    
    /**
     * Get resources available
     *
     * @param string $crl
     * @return LinkerResource list of availble resources
     */
    public static function getResourceList( $crl )
    {
        return self::$Navigator->getResourceList( ClarolineResourceLocator::parse($crl) );
    }
    
    /**
     * Get resources available
     *
     * @param string $crl
     * @return LinkerResource list of availble resources
     */
    public static function getResourceName( $crl )
    {
        return self::$Resolver->getResourceName( ClarolineResourceLocator::parse($crl) );
    }
    
    public static function resolve( $crl )
    {
        return self::$Resolver->resolve( ClarolineResourceLocator::parse($crl) );
    }
}
