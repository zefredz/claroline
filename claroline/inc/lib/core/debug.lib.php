<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     * Debugging functions and classes
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     CORE
     */
   
    function dbg_html_var( $var )
    {
        return htmlspecialchars(var_export( $var, true ));
    }

    class Profiler
    {
    	const PROFILER_STATUS_STARTED = 'started';
    	const PROFILER_STATUS_NOT_STARTED = 'not_started';
    	const PROFILER_STATUS_STOPPED = 'stopped';
    	
        private $startTime;
        private $status;
        private $endTime;
        private $log;

        public function __construct()
        {
            $this->log = array();
            $this->status = self::PROFILER_STATUS_NOT_STARTED;
        }

        public function start( $restart = false )
        {
            if ( $this->status == self::PROFILER_STATUS_STARTED
                && ! $restart )
            {
                return;
            }
            
            $this->startTime = $this->_getCurrentTime();
            $this->status = self::PROFILER_STATUS_STARTED;
            
            pushClaroMessage(sprintf("&gt;&gt; Profiler (re)started at %f", $this->startTime), 'profile');
        }

        public function restart()
        {
            $this->start( true );
        }

        public function stop()
        {
            if ( $this->status != self::PROFILER_STATUS_STARTED )
            {
                $this->restart();
            }

            $this->endTime = $this->_getCurrentTime();
            $this->status = self::PROFILER_STATUS_STOPPED;
            
            pushClaroMessage(sprintf("&gt;&gt; Profiler stoped at %f", $this->endTime), 'profile');
            pushClaroMessage(
                sprintf("** Elapsed time : %f seconds **", $this->getElapsedTime())
                , 'profile');
        }

        public function mark( $file, $line, $msg = '##MARK##' )
        {
            if ( $this->status != self::PROFILER_STATUS_STARTED )
            {
                $this->restart();
            }

            $now = $this->_getCurrentTime();

            $elapsed = $now - $this->startTime;
            $elapsed = sprintf( '%f seconds', $elapsed );
            $timestamp = sprintf( '[@%f]', $now );

            $mark = "$timestamp $msg <br />in $file at line $line after $elapsed";

            $this->log[] = $mark;
            pushClaroMessage($mark, 'profile');
        }

        public function report( $htmlReport = true )
        {
            if ( $this->status != self::PROFILER_STATUS_STOPPED )
            {
                $this->stop();
            }

            if ( $htmlReport )
            {
                return $this->_htmlReport();
            }
            else
            {
                return $this->_plainReport();
            }
        }
        
        public function getElapsedTime()
        {
            return $this->endTime - $this->startTime;
        }

        private function _htmlReport()
        {
            $report = '<pre>' . "\n";

            $report .= $this->_plainReport();

            $report .= '</pre>' . "\n";

            return $report;
        }

        private function _plainReport()
        {
            $report = "--- Start of Profiler Report ---\n";

            $report .= "\nProfiler started at " . $this->startTime . "\n";

            $report .= implode( "\n", $this->log );

            $report .= "\nProfiler stoped at " . $this->endTime . "\n";

            $strTime = "Elapsed time : %f seconds\n";
            $elapsedTime = $this->getElapsedTime();

            $report .= sprintf( $strTime, $elapsedTime );

            $report .= "--- End of Profiler Report ---\n\n";

            return $report;
        }

        private function _getCurrentTime()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }
    }
?>