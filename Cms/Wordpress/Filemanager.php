<?php
defined('AUTOUPDATER_LIB') or die;

class AutoUpdater_Cms_Wordpress_Filemanager extends AutoUpdater_Filemanager
{
    protected static $wp_filesystem;

    public function __construct()
    {
        parent::__construct();

        require_once ABSPATH . 'wp-admin/includes/file.php';
        if (function_exists('WP_Filesystem') and WP_Filesystem())
        {
            global $wp_filesystem;

            static::$wp_filesystem = $wp_filesystem;
        }
    }

    public function get_contents($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->get_contents($file);
        }

        return parent::get_contents($file);
    }

    public function get_contents_array($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->get_contents_array($file);
        }

        return parent::get_contents_array($file);
    }

    public function put_contents($file, $contents)
    {
        return parent::put_contents($file, $contents);
    }

    public function cwd()
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->cwd();
        }

        return parent::cwd();
    }

    public function chdir($dir)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->chdir($dir);
        }

        return parent::chdir($dir);
    }

    public function chgrp($file, $group, $recursive = false)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->chgrp($file, $group, $recursive);
        }

        return parent::chgrp($file, $group, $recursive);
    }

    public function chmod($file, $mode = false, $recursive = false)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->chmod($file, $mode, $recursive);
        }

        return parent::chmod($file, $mode, $recursive);
    }

    public function chown($file, $owner, $recursive = false)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->chown($file, $owner, $recursive);
        }

        return parent::chown($file, $owner, $recursive);
    }

    public function owner($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->owner($file);
        }

        return parent::owner($file);
    }

    public function getchmod($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->getchmod($file);
        }

        return parent::getchmod($file);
    }

    public function group($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->group($file);
        }

        return parent::group($file);
    }

    public function copy($source, $destination, $overwrite = false, $mode = false)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->copy($source, $destination, $overwrite, $mode);
        }

        return parent::copy($source, $destination, $overwrite, $mode);
    }

    public function move($source, $destination, $overwrite = false)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->move($source, $destination, $overwrite);
        }

        return parent::move($source, $destination, $overwrite);
    }

    public function delete($file, $recursive = false, $type = false)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->delete($file, $recursive, $type);
        }

        return parent::delete($file, $recursive, $type);
    }

    public function exists($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->exists($file);
        }

        return parent::exists($file);
    }

    public function is_file($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->is_file($file);
        }

        return parent::is_file($file);
    }

    public function is_dir($path)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->is_dir($path);
        }

        return parent::is_dir($path);
    }

    public function is_readable($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->is_readable($file);
        }

        return parent::is_readable($file);
    }

    public function is_writable($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->is_writable($file);
        }

        return parent::is_writable($file);
    }

    public function atime($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->atime($file);
        }

        return parent::atime($file);
    }

    public function mtime($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->mtime($file);
        }

        return parent::mtime($file);
    }

    public function size($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->size($file);
        }

        return parent::size($file);
    }

    public function touch($file, $time = 0, $atime = 0)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->touch($file, $time, $atime);
        }

        return parent::touch($file, $time, $atime);
    }

    public function mkdir($path, $chmod = false, $chown = false, $chgrp = false)
    {

        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->mkdir($path, $chmod, $chown, $chgrp);
        }

        return parent::mkdir($path, $chmod, $chown, $chgrp);
    }

    public function download($url, $destination = null)
    {
        $result = download_url($url);
        if (is_wp_error($result))
        {
            /** @var WP_Error $result */
            $e = new AutoUpdater_Exception_Response('Failed to download file from URL: ' . $url, 200);
            $e->setError($result->get_error_code(), $result->get_error_message());
            throw $e;
        }

        if ($destination)
        {
            $this->move($result, $destination);

            return $destination;
        }

        return $result;
    }

    public function unpack($file, $destination = null)
    {
        // WordPress will create destination directory if it does not exist
        if ($destination)
        {
            $path = $destination;
        }
        else
        {
            $path = dirname($file) . '/' . $this->getRandomName() . '/';
        }

        $result = unzip_file($file, $path);

        if (is_wp_error($result))
        {
            /** @var WP_Error $result */
            $e = new AutoUpdater_Exception_Response('Failed to unpack file: ' . basename($file), 200);
            $e->setError($result->get_error_code(), $result->get_error_message());
            throw $e;
        }

        return $path;
    }

    public function getTempPath()
    {
        return get_temp_dir();
    }

    public function dirlist($path, $include_hidden = true, $recursive = false)
    {

        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->dirlist($path, $include_hidden, $recursive);
        }

        return parent::dirlist($path, $include_hidden, $recursive);
    }

    public function gethchmod($file)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->gethchmod($file);
        }

        return parent::gethchmod($file);
    }

    public function getnumchmodfromh($mode)
    {
        if (static::$wp_filesystem)
        {
            return static::$wp_filesystem->getnumchmodfromh($mode);
        }

        return parent::getnumchmodfromh($mode);
    }
}