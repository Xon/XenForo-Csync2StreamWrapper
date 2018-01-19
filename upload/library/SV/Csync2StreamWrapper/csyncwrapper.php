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

    public $context;

    /* Methods */

    const prefix      = "csync2";
    const prefix_full = "csync2://";

    public static function RegisterStream()
    {
        SV_Csync2StreamWrapper_CsyncConfig::getInstance()->RegisterStream();
    }

    /**
     * @param string $path
     * @return string
     */
    protected static function absolutePath($path)
    {
        $isEmptyPath = (strlen($path) == 0);
        $isRelativePath = ($path{0} != '/');
        $isWindowsPath = !(strpos($path, ':') === false);

        if (($isEmptyPath || $isRelativePath) && !$isWindowsPath)
        {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        $absolutePathParts = [];
        foreach ($pathParts as $part)
        {
            if ($part == '' && $part !== '0')
            {
                continue;
            }
            if ($part == '.')
            {
                continue;
            }

            if ($part == '..')
            {
                array_pop($absolutePathParts);
            }
            else
            {
                $absolutePathParts[] = $part;
            }
        }
        $path = implode(DIRECTORY_SEPARATOR, $absolutePathParts);

        // resolve any symlinks
        if (file_exists($path) && linkinfo($path) > 0)
        {
            $path = readlink($path);
        }

        // put initial separator that could have been lost
        $path = (!$isWindowsPath ? '/' . $path : $path);

        return $path;
    }

    /**
     * @param string $path
     * @return string|bool
     */
    public static function ParsePath($path)
    {
        static $urls = [];

        if (isset($urls[$path]))
        {
            return $urls[$path];
        }

        $prefix_len = strlen(static::prefix_full);
        if (substr($path, 0, $prefix_len) == static::prefix_full)
        {
            $schemaless_path = substr($path, $prefix_len);
            $url = parse_url($schemaless_path);
            if (isset($url['path']) && !isset($url['scheme']))
            {
                $urls[$path] = static::absolutePath($schemaless_path);

                return $urls[$path];
            }
        }

        return false;
    }

    /**
     * @param string[] $group_hints
     * @param bool  $bulk_commit_hint
     */
    public static function DeferrCommit(array $group_hints, $bulk_commit_hint = false)
    {
        SV_Csync2StreamWrapper_CsyncConfig::getInstance()->DeferrCommit($group_hints, $bulk_commit_hint);
    }

    public static function FinalizeCommit()
    {
        SV_Csync2StreamWrapper_CsyncConfig::getInstance()->FinalizeCommit();
    }

    /**
     * @param string $path
     * @param bool $is_path
     */
    protected static function ConsiderFileOrDir($path, $is_path)
    {
        SV_Csync2StreamWrapper_CsyncConfig::getInstance()->ConsiderFileOrDir($path, $is_path);
    }

    /**
     * @param bool $bulk
     */
    protected static function CommitChanges($bulk = false)
    {
        SV_Csync2StreamWrapper_CsyncConfig::getInstance()->CommitChanges($bulk);
    }

    function __construct()
    {
    }

    function __destruct()
    {
    }

    protected $dirhandle;

    /**
     * @return bool
     */
    public function dir_closedir()
    {
        if (!isset($this->dirhandle))
        {
            return false;
        }
        @closedir($this->dirhandle);
        unset($this->dirhandle);

        return true;
    }

    /**
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function dir_opendir($path, /** @noinspection PhpUnusedParameterInspection */
                                $options)
    {
        $path = static::ParsePath($path);
        if (!$path)
        {
            return false;
        }

        $this->dirhandle = @opendir($path);

        return $this->dirhandle !== false;
    }

    /**
     * @return bool|string
     */
    public function dir_readdir()
    {
        if (!isset($this->dirhandle))
        {
            return false;
        }

        return readdir($this->dirhandle);
    }

    /**
     * @return bool
     */
    public function dir_rewinddir()
    {
        if (!isset($this->dirhandle))
        {
            return false;
        }

        rewinddir($this->dirhandle);
        return true;
    }

    /**
     * @param string $path
     * @param int $mode
     * @param int $options
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        $path = static::ParsePath($path);
        if (!$path)
        {
            return false;
        }
        $recursive = ($options & STREAM_MKDIR_RECURSIVE) == STREAM_MKDIR_RECURSIVE;
        $ret = mkdir($path, $mode, $recursive);
        if ($ret)
        {
            static::ConsiderFileOrDir($path, true);
            static::CommitChanges();
        }

        return $ret;
    }

    /**
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function rmdir(/** @noinspection PhpUnusedParameterInspection */$path, $options)
    {
        $path = static::ParsePath($path);
        if (!$path)
        {
            return false;
        }
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
    /** @var resource */
    protected $streamhandle;
    /** @var bool */
    protected $fileRequiresUpdate = false;
    /** @var string */
    protected $parsedPath;

    public function stream_close()
    {
        fclose($this->streamhandle);
        if ($this->fileRequiresUpdate)
        {
            static::ConsiderFileOrDir($this->parsedPath, false);
            static::CommitChanges();
            unset($this->parsedPath);
            unset($this->streamhandle);
            $this->fileRequiresUpdate = false;
        }
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        if (!isset($this->streamhandle))
        {
            return true;
        }

        return feof($this->streamhandle);
    }

    /**
     * @return bool
     */
    public function stream_flush()
    {
        if (!isset($this->streamhandle))
        {
            return false;
        }

        return fflush($this->streamhandle);
    }

    /**
     * @param string $path
     * @param int $option
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function stream_metadata($path, $option, $value)
    {
        $path = static::ParsePath($path);
        if (!$path)
        {
            return false;
        }
        switch ($option)
        {
            case STREAM_META_TOUCH:
                if (isset($value[0]) && isset($value[1]))
                {
                    $ret = touch($path, $value[0], $value[1]);
                }
                else if (isset($value[0]))
                {
                    $ret = touch($path, $value[0]);
                }
                else
                {
                    $ret = touch($path);
                }
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
                throw new Exception("SV_Csync2StreamWrapper_csyncwrapper::stream_metadata " . $option . " not implemented");
                break;
        }
        $this->fileRequiresUpdate = false;
        return $ret;
    }

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string $opened_path
     * @return bool
     * @throws Exception
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->parsedPath = static::ParsePath($path);
        if (!$this->parsedPath)
        {
            throw new Exception('stream_open() passed an invalid path');
        }
        $use_include_path = ($options & STREAM_USE_PATH) == STREAM_USE_PATH;
        $this->streamhandle = fopen($this->parsedPath, $mode, $use_include_path);
        if ($this->streamhandle !== false)
        {
            $opened_path = $path;
        }
        else
        {
            throw new Exception('Call to fopen() failed');
        }

        return $this->streamhandle !== false;
    }

    /**
     * @param $count
     * @return bool|string
     */
    public function stream_read($count)
    {
        if (!isset($this->streamhandle))
        {
            return '';
        }

        return fread($this->streamhandle, $count);
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        if (!isset($this->streamhandle))
        {
            return false;
        }

        return fseek($this->streamhandle, $offset, $whence) == 0;
    }

    /**
     * @param int $cast_as
     * @return resource
     */
    public function stream_cast($cast_as)
    {
        return $this->streamhandle;
    }

    /**
     * @return array|int
     */
    public function stream_stat()
    {
        if (!isset($this->streamhandle))
        {
            return 0;
        }

        return fstat($this->streamhandle);
    }

    /**
     * @return bool|int
     */
    public function stream_tell()
    {
        if (!isset($this->streamhandle))
        {
            return 0;
        }

        return ftell($this->streamhandle);
    }

    /**
     * @param int $new_size
     * @return bool|int
     */
    public function stream_truncate($new_size)
    {
        if (!isset($this->streamhandle))
        {
            return 0;
        }
        $this->fileRequiresUpdate = true;

        return ftruncate($this->streamhandle, $new_size);
    }

    /**
     * @param string $data
     * @return bool|int
     */
    public function stream_write($data)
    {
        if (!isset($this->streamhandle))
        {
            return 0;
        }
        $this->fileRequiresUpdate = true;

        return fwrite($this->streamhandle, $data);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function unlink($path)
    {
        $path = static::ParsePath($path);
        if (!$path)
        {
            return false;
        }
        $is_dir = is_dir($path);
        $ret = @unlink($path);
        static::ConsiderFileOrDir($path, $is_dir);
        static::CommitChanges();

        return $ret;
    }

    /**
     * @param string $path
     * @param int $flags
     * @return array|int
     */
    public function url_stat($path, $flags)
    {
        $path = static::ParsePath($path);
        if (!$path)
        {
            return 0;
        }
        if ($flags & STREAM_URL_STAT_LINK)
        {
            return ($flags & STREAM_URL_STAT_QUIET) ? @lstat($path) : lstat($path);
        }
        else
        {
            return ($flags & STREAM_URL_STAT_QUIET) ? @stat($path) : stat($path);
        }
    }
}



