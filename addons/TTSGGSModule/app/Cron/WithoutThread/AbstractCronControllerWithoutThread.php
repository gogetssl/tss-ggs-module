<?php

namespace ModulesGarden\TTSGGSModule\App\Cron\WithoutThread;


use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use Illuminate\Database\Capsule\Manager as DB;


/**
 * Description of AbstractCronController
 */
abstract class AbstractCronControllerWithoutThread
{

    protected $exit = true;

    protected $isChild = false;

    protected $className;

    protected $child = 0;


    protected $parentId;

    protected $cronManager;

    protected $parentUserId;

    protected $parentGroupId;

    protected $moduleVersion = null;

    protected $children = [];

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function setChildId($childId)
    {
        $this->childId = $childId;

        return $this;
    }

    public function ischild($isChild)
    {
        $this->isChild = $isChild;

        return $this;
    }

    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }


    public function setCronManager($cronManager)
    {
        $this->cronManager = $cronManager;

        return $this;
    }



    protected function updatePid()
    {
        CronManager::updatePids($this->className, 0);

        if ($this->isChild && $this->parentGroupId === getmygid() && $this->parentUserId === getmyuid())
        {
            $this->removePid();
        }
    }

    protected function removePid()
    {
        CronManager::removePids($this->className, 0);
    }

    public function __destruct()
    {
        $this->removePid();
    }

    abstract public function run();



    abstract public function start();


    public function setParentGroupId($parentGroupId)
    {
        $this->parentGroupId = $parentGroupId;

        return $this;
    }

    public function setParentUserId($parentUserId)
    {
        $this->parentUserId = $parentUserId;

        return $this;
    }

    protected function getModuleVersion()
    {
        $moduleVersionFile = ModuleConstants::getModuleRootDir() . DIRECTORY_SEPARATOR . 'moduleVersion.php';

        if(file_exists($moduleVersionFile))
        {
            $content = file_get_contents($moduleVersionFile);
            $moduleVersion = $this->findModuleVersion($content);

            if(!$moduleVersion){
                throw new \Exception('Invalid module version');
            }

            return $moduleVersion;
        }

        return false;
    }

    private function findModuleVersion($content)
    {
        $matches = [];
        preg_match('/\$moduleVersion\s?=\s?\'([A-Za-z0-9_\.\-]+)\'/', (string)$content, $matches);
        return $matches[1];
    }
}