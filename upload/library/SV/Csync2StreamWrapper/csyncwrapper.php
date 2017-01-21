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
    const prefix_full = "csync2://";

    public static function RegisterStream()
    {
        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();

        if ($config->isInstalled())
            return;
        $config->setInstalled(true);

        stream_wrapper_register(static::prefix, "SV_Csync2StreamWrapper_csyncwrapper");
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
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        $absolutePathParts = array();
        foreach ($pathParts as $part) {
            if ($part == '' && $part !== '0')
                continue;
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
        static $urls = array();

        if (isset($urls[$path]))
        {
            return $urls[$path];
        }

        $prefix_len = strlen(static::prefix_full);
        if (substr($path, 0, $prefix_len) == static::prefix_full) {
            $schemaless_path = substr($path, $prefix_len);
            $url = parse_url($schemaless_path);
            if (isset($url['path']) && !isset($url['scheme']))
            {
                $urls[$path] = static::absolutePath($schemaless_path);
                return $urls[$path];
            }
        }
        return False;
    }

    public static function DeferrCommit(array $group_hints, $bulk_commit_hint = false)
    {
        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        if (!$config->isInstalled())
            return;

        $config->deferred_count += 1;
        $config->deferred_commit_bulk = $bulk_commit_hint;
        if ($group_hints)
        {
            $config->csync_groups = $config->csync_groups + $group_hints;
        }
    }

    public static function isTemp($path)
    {
        static $xfTemp = null;
        if ($xfTemp === null)
        {
            $xfTemp = XenForo_Helper_File::getTempDir();
            $xfTemp = static::ParsePath($xfTemp);
        }
        return !empty($xfTemp) && strpos($path, $xfTemp) !== false;
    }

    public static function FinalizeCommit()
    {
        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        if (!$config->isInstalled())
            return;

        $config->deferred_count -= 1;
        if ($config->deferred_count <= 0)
        {
            $config->deferred_count = 0;
            if (!$config->deferred_commit_bulk)
            {
                $touched = array();
                foreach($config->deferred_paths as &$dir)
                {
                    if (isset($touched[$dir]))
                        continue;
                    $touched[$dir] = true;
                    static::ConsiderFileOrDir($dir, true);
                }
                $touched = array();
                foreach($config->deferred_files as &$file)
                {
                    if (isset($touched[$file]))
                        continue;
                    $touched[$file] = true;
                    static::ConsiderFileOrDir($file, false);
                }
            }
            static::CommitChanges($config->deferred_commit_bulk);
            $config->deferred_paths = array();
            $config->deferred_files = array();
        }
    }

    static $pendingChanges = false;
    protected static function ConsiderFileOrDir($path,$is_path)
    {
        if (static::isTemp($path))
        {
            return;
        }

        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        if (!$config->isInstalled())
            return;

        if ($config->deferred_count > 0)
        {
            if (!$config->deferred_commit_bulk)
            {
                if ($is_path)
                    $config->deferred_paths[] = $path;
                else
                    $config->deferred_files[] = $path;
            }
            return;
        }
        static::$pendingChanges = true;
        static::pushSingeChange($path);
    }

    protected static function CommitChanges($bulk = false)
    {
        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        if (!$config->isInstalled())
            return;

        if ($config->deferred_count  > 0)
            return;

        if($bulk)
        {
            $flags = "x";
            if ($config->csync_groups)
            {
                $flags .= ' -G '. join(',', $config->csync_groups);
            }
        }
        else
        {
            if (!static::$pendingChanges)
            {
                return;
            }
            $flags = "ur" ;
        }
        static::$pendingChanges = false;
        static::pushBulkChanges($flags);
    }
    
    protected static function pushSingeChange($path)
    {
        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        // csync2 directly inspects the argc passed to the process and ignores shell expansions, so escaping doesn't work
        $flags = "cr" ;
        if ($config->debug_mode && $config->debug_log)
            $flags .= "v";
        if ($config->csync_database)
            $flags .= " -D ".$config->csync_database;
        $input = $config->csync2_binary . " -".$flags." " . $path ." 2>&1";
        $output = shell_exec($input);

        if ($config->debug_mode && $config->debug_log)
        {
            file_put_contents($config->debug_log,generateCallTrace()."\n", FILE_APPEND);
            file_put_contents($config->debug_log,$input."\n", FILE_APPEND);
            file_put_contents($config->debug_log,$output."\n", FILE_APPEND);
        }
    }
    
    protected static function pushBulkChanges($flags)
    {
        $config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        if ($config->debug_mode && $config->debug_log)
        {
            $flags .= " -v";
        }
        if ($config->csync_database)
        {
            $flags .= " -D ".$config->csync_database;
        }
        $input = $config->csync2_binary . " -".$flags ." 2>&1";
        $output = shell_exec($input);

        if ($config->debug_mode && $config->debug_log)
        {
            file_put_contents($config->debug_log,generateCallTrace()."\n", FILE_APPEND);
            file_put_contents($config->debug_log,$input."\n", FILE_APPEND);
            file_put_contents($config->debug_log,$output."\n", FILE_APPEND);
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
        $path = static::ParsePath($path);
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
        $path = static::ParsePath($path);
        if (!$path)
            return False;
        $recursive = ($options & STREAM_MKDIR_RECURSIVE) == STREAM_MKDIR_RECURSIVE;
        $ret = mkdir($path, $mode, $recursive);
        if ($ret)
        {
            static::ConsiderFileOrDir($path, true);
            static::CommitChanges();
        }
        return $ret;
    }

    public function rmdir ( $path , $options )
    {
        $path = static::ParsePath($path);
        if (!$path)
            return False;
        $ret = rmdir($path);
        if ($ret)
        {
            static::ConsiderFileOrDir($path, true);
            static::CommitChanges();
        }
        return $ret;
    }

/*
    public function rename ( $path_from , $path_to )
    {
        $paths = array();
        $path_from_fix = static::ParsePath($path_from);
        if (!$path_from_fix)
            $paths[] = $path_from_fix;
        else
            $path_to_fix = $path_to;

        $path_to_fix = static::ParsePath($path_to);
        if (!$path_to_fix)
            $paths[] = $path_to_fix;
        else
            $path_to_fix = $path_to;

        $ret = rename($path_from_fix , $path_to_fix);

        throw new Exception(var_export($path_from, true) . var_export($path_to, true). var_export($paths, true) );

        foreach($paths as $path)
            static::ConsiderFileOrDir($path);
        static::CommitChanges();

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
            static::ConsiderFileOrDir($this->parsedPath, false);
            static::CommitChanges();
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
        $path = static::ParsePath($path);
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
        $this->parsedPath = static::ParsePath($path);
        if (!$this->parsedPath)
        {
            throw new Exception('stream_open() passed an invalid path');
        }
        $use_include_path  = ($options & STREAM_USE_PATH) == STREAM_USE_PATH;
        $this->streamhandle = fopen($this->parsedPath, $mode, $use_include_path );
        if ($this->streamhandle !== False)
            $opened_path = $path;
        else
            throw new Exception('Call to fopen() failed');

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
        $path = static::ParsePath($path);
        if (!$path)
            return False;
        $is_dir = is_dir($path);
        $ret = @unlink($path);
        static::ConsiderFileOrDir($path, $is_dir);
        static::CommitChanges();
        return $ret;
    }

    public function url_stat ( $path , $flags )
    {
        $path = static::ParsePath($path);
        if (!$path)
            return 0;
        if ($flags & STREAM_URL_STAT_LINK)
            return ($flags & STREAM_URL_STAT_QUIET) ? @lstat($path) : lstat($path);
        else
            return ($flags & STREAM_URL_STAT_QUIET) ? @stat($path) : stat($path);
    }
}



