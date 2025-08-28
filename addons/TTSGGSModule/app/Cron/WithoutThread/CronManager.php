<?php

namespace ModulesGarden\TTSGGSModule\App\Cron\WithoutThread;

use ModulesGarden\TTSGGSModule\Core\FileReader\Validator;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection;
use ModulesGarden\TTSGGSModule\Core\FileReader\File;

/**
 * Description of CronManager
 */
class CronManager
{
    const AVAILABLE_MODES         = ['cli', 'cgi-fcgi'];

    protected $arguments    = [];
    protected $appCronPath  = '';
    protected $coreCronPath = '';
    protected $cronPids     = '';
    protected $isParent     = true;
    protected $parentId;
    protected $parentUId;
    protected $parentGId;
    protected $childId;

    /**
     * @var Validator
     */
    protected $fileValidator;

    public function __construct(Validator $fileValidator)
    {
        $this->fileValidator = $fileValidator;
        $this->appCronPath   = ModuleConstants::getModuleRootDir() . DS . 'app' . DS . 'Cron' . DS;
        $this->coreCronPath  = ModuleConstants::getModuleRootDir() . DS . 'core' . DS . 'Cron' . DS;
        $this->cronPidsPath  = ModuleConstants::getModuleRootDir() . DS . 'storage' . DS . 'crons' . DS;

        if ($this->fileValidator->isExist($this->cronPidsPath) === false)
        {
            mkdir($this->cronPidsPath);
            File::setPermission($this->cronPidsPath);
        }

        $this->appCronPath  .= "WithoutThread" . DS;
        $this->coreCronPath .= "WithoutThread" . DS;

        $this->parentGId = getmygid();
        $this->parentUId = getmyuid();
    }

    public function setArgv($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function execute()
    {
        $this->loadArguments();

        if ($this->isInCliMode() === false)
        {
            return $this;
        }
        foreach ($this->arguments as $className)
        {
            $className = trim(trim($className, '--'), '-');
            $class = ModuleConstants::getRootNamespace() . "\\";
            if ($this->fileValidator->isExist($this->appCronPath . $className . ".php"))
            {
                $class .= "App\Cron\\" . $this->getType() . "\\" . $className;
                $this->runTask($class, $className);
            }
            elseif ($this->fileValidator->isExist($this->coreCronPath . $className . ".php"))
            {
                $class .= "Core\Cron\\" . $this->getType() . "\\" . $className;
                $this->runTask($class, $className);
            }
        }

        return $this;
    }

    protected function runTask($class, $className)
    {
        if ($this->classExist($class) === true && $this->isCronRunning($className) === false)
        {
            $instant = DependencyInjection::create($class, null, true);

            $instant->setCronManager($this);

            $instant->ischild(!$this->isParent)
                ->setChildId($this->childId)
                ->setParentId($this->parentId)
                ->setParentGroupId($this->parentGId)
                ->setParentUserId($this->parentUId);

            $instant->setClassName($className);
            $instant->start();
        }
    }

    protected function getType()
    {
        return 'WithoutThread';
    }

    public static function isThread()
    {
        return false;
    }

    protected function loadArguments()
    {
        $deleteClass = [];
        foreach ($this->arguments as $key => $name)
        {
            if (strpos($name, 'cron.php') !== false)
            {
                $deleteClass[] = $key;
            }
            elseif ($name === '--parent')
            {
                $this->parentId = $this->arguments[$key + 1];
                $this->isParent = false;
                $deleteClass[]  = $key;
                $deleteClass[]  = $key + 1;
            }
            elseif ($name === '--child')
            {
                $this->childId  = $this->arguments[$key + 1];
                $this->isParent = false;
                $deleteClass[]  = $key;
                $deleteClass[]  = $key + 1;
            }
            elseif ($name === '--parentuid')
            {
                $this->parentUId  = $this->arguments[$key + 1];
                $deleteClass[]  = $key;
                $deleteClass[]  = $key + 1;
            }
            elseif ($name === '--parentgid')
            {
                $this->parentGId  = $this->arguments[$key + 1];
                $deleteClass[]  = $key;
                $deleteClass[]  = $key + 1;
            }
        }

        foreach ($deleteClass as $clasKey)
        {
            unset($this->arguments[$clasKey]);
        }

        return $this;
    }

    protected function classExist($name)
    {
        return class_exists($name);
    }

    public static function updatePids($name, $id = '')
    {
        touch(ModuleConstants::getModuleRootDir() . DS . 'storage' . DS . 'crons' . DS . $name . 'Pid' . $id . '.php');
    }

    public static function removePids($name, $id = '')
    {
        $file = ModuleConstants::getModuleRootDir() . DS . 'storage' . DS . 'crons' . DS . $name . 'Pid' . $id . '.php';

        if (file_exists($file))
        {
            unlink($file);
        }
    }

    public function createPids($file)
    {
        return file_put_contents($file, '<?php die(); ' . getmypid());
    }

    public function isChildRunning($name, $id)
    {
        $file = $this->cronPidsPath . $name . 'Pid' . $id . '.php';

        if ($this->fileValidator->isExist($file) && filemtime($file) > time() - 600)
        {
            return true;
        }

        return false;
    }


    private function isCronRunning($name)
    {
        if ($this->isParent === true)
        {
            $file = $this->cronPidsPath . $name . 'Pid.php';

            if ($this->fileValidator->isExist($file) && filemtime($file) > time() - 600)
            {
                return true;
            }

            if ($this->createPids($file) === false)
            {
                return true;
            }

            return false;
        }
        else
        {
            $file = $this->cronPidsPath . $name . 'Pid' . $this->childId . '.php';

            if ($this->fileValidator->isExist($file) && filemtime($file) > time() - 600)
            {
                return true;
            }

            if ($this->createPids($file) === false)
            {
                return true;
            }

            return false;
        }
    }

    private function isInCliMode()
    {
        if (in_array(php_sapi_name(), self::AVAILABLE_MODES))
        {
            return true;
        }

        return false;
    }
}

