<?php
   
function generateCallTrace()
{
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
    
    for ($i = 0; $i < $length; $i++)
    {
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }
    
    return "\t" . implode("\n\t", $result);
}
$csyncwrapper_installed = false;   

class SV_Csync2StreamWrapper_csyncwrapper   
{

    /* Properties */

    public $context ;
    
    /* Methods */
    
    const prefix = "csync2";
     
    public static function RegisterStream()
    {
        global $csyncwrapper_installed,$csync2_deferred_count, $csync2_deferred_commit_bulk, $deferred_paths, $deferred_files, $csyncwrapper_debug_log, $csyncwrapper_database;
        if (isset($csyncwrapper_installed) && $csyncwrapper_installed)
            return;
        $csyncwrapper_installed = true;
        //setlocale(LC_CTYPE, "en_US.UTF-8");
        stream_wrapper_register(self::prefix, "SV_Csync2StreamWrapper_csyncwrapper");
        
        $csync2_deferred_count = 0;
        $csync2_deferred_commit_bulk = false;
        $deferred_paths = array();
        $deferred_files =  array();
        //$csyncwrapper_debug_log = "/var/www/html/error.log";
        //$csyncwrapper_database = "/var/lib/csync2";
    }
   
    protected static function absolutePath($path)
    {
        $isEmptyPath    = (strlen($path) == 0);
        $isRelativePath = ($path{0} != '/');
        $isWindowsPath  = !(strpos($path, ':') === false);

        if (($isEmptyPath || $isRelativePath) && !$isWindowsPath)
            $path= getcwd().DIRECTORY_SEPARATOR.$path;

        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $pathParts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutePathParts = array();
        foreach ($pathParts as $part) {
            if ($part == '.')
                continue;

            if ($part == '..') {
                array_pop($absolutePathParts);
            } else {
                $absolutePathParts[] = $part;
            }
        }
        $path = implode(DIRECTORY_SEPARATOR, $absolutePathParts);

        // resolve any symlinks
        if (file_exists($path) && linkinfo($path)>0)
            $path = readlink($path);

        // put initial separator that could have been lost
        $path= (!$isWindowsPath ? '/'.$path : $path);

        return $path;
    }   
    protected static function ParsePath($path)
    {
        $prefix = self::prefix . "://";        
        if (substr($path, 0, strlen($prefix)) == $prefix) {
            $path = substr($path, strlen($prefix));            
            $url = parse_url($path);
            if (isset($url['path']) && !isset($url['scheme']))
            {
                return self::absolutePath($path);
            }
        } 
        return False;
    }
    
    const csync2 = "/usr/sbin/csync2";
    //static $debug_log;
    
    
    public static function DeferrCommit($bulk_commit_hint = false)
    {
        global $csync2_deferred_count,$csync2_deferred_commit_bulk;
        $csync2_deferred_count += 1;
        $csync2_deferred_commit_bulk = $bulk_commit_hint;
    }
    
    public static function FinalizeCommit()
    {
        global $csync2_deferred_count, $csync2_deferred_commit_bulk, $deferred_paths, $deferred_files;
        $csync2_deferred_count -= 1;
        if ($csync2_deferred_count <= 0)
        {
            $csync2_deferred_count = 0;
            if (!$csync2_deferred_commit_bulk)
            {
                $touched = array();
                foreach($deferred_paths as $dir)
                {
                    if (isset($touched[$dir]))
                        continue;
                    $touched[$dir] = true;
                    self::ConsiderFileOrDir($dir, true);
                }
                $touched = array();
                foreach($deferred_files as $file)
                {
                    if (isset($touched[$file]))
                        continue;   
                    $touched[$file] = true;
                    self::ConsiderFileOrDir($file, false);
                }
            }
            self::CommitChanges($csync2_deferred_commit_bulk);
            $deferred_paths = array();
            $deferred_files = array();
        }
    }    
    
    protected static function ConsiderFileOrDir($path,$is_path)
    {
        global $csync2_deferred_count, $csync2_deferred_commit_bulk, $deferred_files, $deferred_paths, $csyncwrapper_debug_log, $csyncwrapper_database;
        if ($csync2_deferred_count > 0)
        {
            if (!$csync2_deferred_commit_bulk)
            {
                if ($is_path)
                    $deferred_paths[] = $path;
                else
                    $deferred_files[] = $path;
            }
            return;
        }
        // csync2 directly inspects the argc passed to the process and ignores shell expansions, so escaping doesn't work
        $flags = "cr" ;
        if (isset($csyncwrapper_debug_log) && $csyncwrapper_debug_log)
            $flags .= "v"; 
        if (isset($csyncwrapper_database) && $csyncwrapper_database)
            $flags .= " -D ".$csyncwrapper_database;
        $input = self::csync2 . " -".$flags." " . $path ." 2>&1";
        $output = shell_exec($input);
        
        if (isset($csyncwrapper_debug_log) && $csyncwrapper_debug_log)
        {
            file_put_contents($csyncwrapper_debug_log,generateCallTrace()."\n", FILE_APPEND);
            file_put_contents($csyncwrapper_debug_log,$input."\n", FILE_APPEND);
            file_put_contents($csyncwrapper_debug_log,$output."\n", FILE_APPEND);
        }
    }
    
    protected static function CommitChanges($bulk = false)
    {    
        global $csync2_deferred_count,$csyncwrapper_debug_log, $csyncwrapper_database;
        if ($csync2_deferred_count  > 0)
            return;
        
        if($bulk)
            $flags = "x" ;
        else
            $flags = "ur" ;
        if (isset($csyncwrapper_debug_log) && $csyncwrapper_debug_log)
            $flags .= "v";     
        if (isset($csyncwrapper_database) && $csyncwrapper_database)
            $flags .= " -D ".$csyncwrapper_database;           
        $input = self::csync2 . " -".$flags ." 2>&1";
        $output = shell_exec($input);
        
        if (isset($csyncwrapper_debug_log) && $csyncwrapper_debug_log)
        {        
            file_put_contents($csyncwrapper_debug_log,generateCallTrace()."\n", FILE_APPEND);
            file_put_contents($csyncwrapper_debug_log,$input."\n", FILE_APPEND);
            file_put_contents($csyncwrapper_debug_log,$output."\n", FILE_APPEND);        
        }
    }
    
    function __construct ( )
    {
    }
    
    function __destruct ( )
    {
    }
    
    protected $dirhandle;
    
    public function dir_closedir ( )
    {
        if (!isset($this->dirhandle))
            return False;    
        @closedir($this->dirhandle);
        unset($this->dirhandle);
        return True;
    }
    
    public function dir_opendir ( $path , $options )
    {
        $path = self::ParsePath($path);
        if (!$path)
            return False;
            
        $this->dirhandle = @opendir($path);
        return $this->dirhandle !== False;
    }
    
    public function dir_readdir()
    {
        if (!isset($this->dirhandle))
            return False;
        return readdir($this->dirhandle);
    }
    
    public function dir_rewinddir()
    {
        if (!isset($this->dirhandle))
            return False;    
        return rewinddir($this->dirhandle);
    }
    
    public function mkdir ( $path , $mode , $options )
    {
        $path = self::ParsePath($path);
        if (!$path)
            return False;
        $recursive = ($options & STREAM_MKDIR_RECURSIVE) == STREAM_MKDIR_RECURSIVE;
        $ret = mkdir($path, $mode, $recursive);
        if ($ret)
        {
            self::ConsiderFileOrDir($path, true);
            self::CommitChanges();
        }
        return $ret;        
    }
    
    public function rmdir ( $path , $options )
    {
        $path = self::ParsePath($path);
        if (!$path)
            return False; 
        $ret = rmdir($path);
        if ($ret)
        {
            self::ConsiderFileOrDir($path, true);
            self::CommitChanges();
        }
        return $ret;
    }

/*    
    public function rename ( $path_from , $path_to )
    {
        $paths = array();
        $path_from_fix = self::ParsePath($path_from);
        if (!$path_from_fix)
            $paths[] = $path_from_fix; 
        else
            $path_to_fix = $path_to;
            
        $path_to_fix = self::ParsePath($path_to);
        if (!$path_to_fix)
            $paths[] = $path_to_fix;
        else
            $path_to_fix = $path_to;
            
        $ret = rename($path_from_fix , $path_to_fix);
        
        throw new Exception(var_export($path_from, true) . var_export($path_to, true). var_export($paths, true) );
        
        foreach($paths as $path)
            self::ConsiderFileOrDir($path);
        self::CommitChanges();
        
        return $ret;
    }
*/        
    protected $streamhandle;
    protected $fileRequiresUpdate = false;
    protected $parsedPath;
    
    public function stream_close (  )
    {
        $ret = fclose($this->streamhandle);
        if ($this->fileRequiresUpdate)
        {
            self::ConsiderFileOrDir($this->parsedPath, false);
            self::CommitChanges();            
            unset($this->parsedPath);
            unset($this->streamhandle);
            $this->fileRequiresUpdate = false;
        }
    }
    
    public function stream_eof (  )
    {
        if(!isset($this->streamhandle))
            return true;
        return feof($this->streamhandle);        
    }
    
    public function stream_flush (  )
    {
        if(!isset($this->streamhandle))
            return false;
        return fflush($this->streamhandle);        
    }
    
    public function stream_metadata ( $path , $option , $value )
    {
        $path = self::ParsePath($path);
        if (!$path)
            return False;        
        $ret = False;
        switch($option)
        {
            case STREAM_META_TOUCH:
                if (isset($value[0]) && isset($value[1]))
                    $ret = touch($path, $value[0], $value[1]);
                else if (isset($value[0]))
                    $ret = touch($path, $value[0]);
                else
                    $ret = touch($path);
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                $ret = chown($path, $value);
                break;        
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                $ret = chgrp($path, $value);
                break;  
            case STREAM_META_ACCESS:
                $ret = chmod($path, $value);
                break;             
            default:                
                throw new Exception("SV_Csync2StreamWrapper_csyncwrapper::stream_metadata ". $option ." not implemented");
                break;
        }
        $this->fileRequiresUpdate = false;
    }
    
    public function stream_open ( $path , $mode , $options , &$opened_path )
    {
        $this->parsedPath = self::ParsePath($path);
        if (!$this->parsedPath)
        {
            if ($options & STREAM_REPORT_ERRORS)
            {
                trigger_error('stream_open() passed an invalid path', E_USER_WARNING);
            }
            return False;
        }
        
        $this->streamhandle = ($options & STREAM_REPORT_ERRORS) ? fopen($this->parsedPath, $mode) : @fopen($this->parsedPath, $mode);
        if ($this->streamhandle !== False)        
            $opened_path = $path;
        
        return $this->streamhandle !== False;
    }
    
    public function stream_read ( $count )
    {
        if(!isset($this->streamhandle))
            return '';
        return fread($this->streamhandle,$count);
    }
    
    public function stream_seek ( $offset , $whence  = SEEK_SET )
    {
        if(!isset($this->streamhandle))
            return False;
        return fseek($this->streamhandle,$offset,$whence) == 0;
    }
    
    public function stream_cast( $cast_as )
    {
        return $this->streamhandle;
    }   
    
    public function stream_stat (  )
    {
        if(!isset($this->streamhandle))
            return 0;
        return fstat($this->streamhandle);
    }
    
    public function stream_tell (  )
    {
        if(!isset($this->streamhandle))
            return 0;
        return ftell($this->streamhandle);
    }
    
    public function stream_truncate ( $new_size )
    {
        if(!isset($this->streamhandle))
            return 0;
        $this->fileRequiresUpdate = true;
        return ftruncate($this->streamhandle, $new_size);
    }
    
    public function stream_write ( $data )
    {
        if(!isset($this->streamhandle))
            return 0;
        $this->fileRequiresUpdate = true;
        return fwrite($this->streamhandle, $data);
    }

    public function unlink ( $path )
    {
        $path = self::ParsePath($path);
        if (!$path)
            return False;
        $is_dir = is_dir($path);
        $ret = @unlink($path);
        self::ConsiderFileOrDir($path, $is_dir);
        self::CommitChanges();
        return $ret;
    }

    public function url_stat ( $path , $flags )
    {    
        $path = self::ParsePath($path);
        if (!$path)
            return 0;        
        if ($flags & STREAM_URL_STAT_LINK)
            return ($flags & STREAM_URL_STAT_QUIET) ? @lstat($path) : lstat($path);
        else
            return ($flags & STREAM_URL_STAT_QUIET) ? @stat($path) : stat($path);
    }    
}



