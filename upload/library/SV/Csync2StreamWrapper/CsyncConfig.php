<?php

class SV_Csync2StreamWrapper_CsyncConfig
{
    public function __construct()
    {
        //setlocale(LC_CTYPE, "en_US.UTF-8");
        //$this->debug_log
        //$this->csync_database = "/var/lib/csync2";
    }


    public $deferred_count       = 0;
    public $deferred_commit_bulk = false;
    public $deferred_paths       = [];
    public $deferred_files       = [];
    public $csync2_binary        = "/usr/sbin/csync2";
    public $csync_database       = ""; //= "/var/lib/csync2";

    public $debug_mode = false;
    public $debug_log  = "";// = "/var/www/html/error.log";

    public $csync_groups  = ['www_code', 'www_data', 'www_templates'];
    public $www_code      = 'www_code';
    public $www_data      = 'www_data';
    public $www_templates = 'www_templates';

    protected $_installed = false;

    public function isInstalled()
    {
        return $this->_installed;
    }

    public function setInstalled($_installed)
    {
        $this->_installed = $_installed;
    }

    protected static $_instance;

    public static function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function RegisterStream()
    {
        if ($this->isInstalled())
        {
            return;
        }
        $this->setInstalled(true);

        stream_wrapper_register(SV_Csync2StreamWrapper_csyncwrapper::prefix, "SV_Csync2StreamWrapper_csyncwrapper");
    }

    public function DeferrCommit(array $group_hints, $bulk_commit_hint = false)
    {
        if (!$this->isInstalled())
        {
            return;
        }

        $this->deferred_count += 1;
        $this->deferred_commit_bulk = $bulk_commit_hint;
        if ($group_hints)
        {
            $this->csync_groups = $this->csync_groups + $group_hints;
        }
    }

    protected function isTemp($path)
    {
        static $xfTemp = null;
        if ($xfTemp === null)
        {
            $xfTemp = XenForo_Helper_File::getTempDir();
            $xfTemp = SV_Csync2StreamWrapper_csyncwrapper::ParsePath($xfTemp);
        }

        return !empty($xfTemp) && strpos($path, $xfTemp) !== false;
    }

    public function FinalizeCommit()
    {
        if (!$this->isInstalled())
        {
            return;
        }

        $this->deferred_count -= 1;
        if ($this->deferred_count <= 0)
        {
            $this->deferred_count = 0;
            if (!$this->deferred_commit_bulk)
            {
                $touched = [];
                foreach ($this->deferred_paths as &$dir)
                {
                    if (isset($touched[$dir]))
                    {
                        continue;
                    }
                    $touched[$dir] = true;
                    $this->ConsiderFileOrDir($dir, true);
                }
                $touched = [];
                foreach ($this->deferred_files as &$file)
                {
                    if (isset($touched[$file]))
                    {
                        continue;
                    }
                    $touched[$file] = true;
                    $this->ConsiderFileOrDir($file, false);
                }
            }
            $this->CommitChanges($this->deferred_commit_bulk);
            $this->deferred_paths = [];
            $this->deferred_files = [];
        }
    }

    protected $pendingChanges = false;

    public function ConsiderFileOrDir($path, $is_path)
    {
        if ($this->isTemp($path))
        {
            return;
        }
        if (!$this->isInstalled())
        {
            return;
        }

        if ($this->deferred_count > 0)
        {
            if (!$this->deferred_commit_bulk)
            {
                if ($is_path)
                {
                    $this->deferred_paths[] = $path;
                }
                else
                {
                    $this->deferred_files[] = $path;
                }
            }

            return;
        }
        $this->pendingChanges = true;
        $this->pushSingeChange($path);
    }

    public function CommitChanges($bulk = false)
    {
        if (!$this->isInstalled())
        {
            return;
        }

        if ($this->deferred_count > 0)
        {
            return;
        }

        if ($bulk)
        {
            $flags = "x";
            if ($this->csync_groups)
            {
                $flags .= ' -G ' . join(',', $this->csync_groups);
            }
        }
        else
        {
            if (!$this->pendingChanges)
            {
                return;
            }
            $flags = "ur";
        }
        $this->pendingChanges = false;
        $this->pushBulkChanges($flags);
    }

    protected function pushSingeChange($path)
    {
        // csync2 directly inspects the argc passed to the process and ignores shell expansions, so escaping doesn't work
        $flags = "cr";
        if ($this->debug_mode && $this->debug_log)
        {
            $flags .= "v";
        }
        if ($this->csync_database)
        {
            $flags .= " -D " . $this->csync_database;
        }
        $input = $this->csync2_binary . " -" . $flags . " " . $path . " 2>&1";
        $output = shell_exec($input);

        if ($this->debug_mode && $this->debug_log)
        {
            file_put_contents($this->debug_log, generateCallTrace() . "\n", FILE_APPEND);
            file_put_contents($this->debug_log, $input . "\n", FILE_APPEND);
            file_put_contents($this->debug_log, $output . "\n", FILE_APPEND);
        }
    }

    protected function pushBulkChanges($flags)
    {
        if ($this->debug_mode && $this->debug_log)
        {
            $flags .= " -v";
        }
        if ($this->csync_database)
        {
            $flags .= " -D " . $this->csync_database;
        }
        $input = $this->csync2_binary . " -" . $flags . " 2>&1";
        $output = shell_exec($input);

        if ($this->debug_mode && $this->debug_log)
        {
            file_put_contents($this->debug_log, generateCallTrace() . "\n", FILE_APPEND);
            file_put_contents($this->debug_log, $input . "\n", FILE_APPEND);
            file_put_contents($this->debug_log, $output . "\n", FILE_APPEND);
        }
    }
}
