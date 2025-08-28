<?php

namespace ModulesGarden\TTSGGSModule\Core\FileReader;

/**
 * Wrapper for files and directories validation methods
 */
class PathValidator
{
    /**
     * @param string $path -a a path to be validated, can be a file path or a dir path
     * @param bool $isReadable - states if path should be readable
     * @param bool $isWritable - states if path should be writable
     * @param bool $create - states if should try to create a directory if not exists
     * @return bool
     */
    public function validatePath($path = '', $isReadable = true, $isWritable = false, $create = true)
    {
        //try to create a dir if does not exist
        if ($create)
        {
            $this->createDirIfNotExists($path);
        }

        //if path does not exists
        if (!$this->pathExists($path))
        {
            return false;
        }

        //if should be readable and it is not
        if ($isReadable && !$this->isPathReadable($path))
        {
            return false;
        }

        //if should be writable and it is not
        if ($isWritable && !$this->isPathWritable($path))
        {
            return false;
        }

        return true;
    }

    /**
     * @param string $path - if provided path does not exist, a dir will be created
     */
    public function createDirIfNotExists($path)
    {
        if (!$this->pathExists($path))
        {
            mkdir($path);
        }
    }

    /**
     * @param string $path -a a path to be validated, can be a file path or a dir path
     * @return bool
     */
    public function pathExists($path)
    {
        return file_exists($path);
    }

    /**
     * @param $path -a a path to be validated, can be a file path or a dir path
     * @return bool
     */
    public function isPathReadable($path)
    {
        return is_readable($path);
    }

    /**
     * @param $path -a a path to be validated, can be a file path or a dir path
     * @return bool
     */
    public function isPathWritable($path)
    {
        return is_writable($path);
    }
}
