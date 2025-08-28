<?php

namespace ModulesGarden\TTSGGSModule\App\Cron\WithoutThread;

use Illuminate\Database\Capsule\Manager as DB;
use ModulesGarden\TTSGGSModule\App\Helpers\ModuleSettings;
use ModulesGarden\TTSGGSModule\App\Libs\ChildProcess;
use ModulesGarden\TTSGGSModule\App\Models\DemonTask as DemonTaskModel;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models\ModuleSettings as Model;

/**
 * Description of DemonTask
 */
class DemonTask extends AbstractCronControllerWithoutThread
{
    /**
     * @var \ModulesGarden\TTSGGSModule\App\Models\DemonTask
     */
    protected $demonTask;

    /**
     * @var
     */
    private $microTimeStart;

    /**
     * @var array
     */
    private $cronOutputsMessages = [];

    /**
     * @var DemonTaskModel
     */
    private $waitingTasks;

    private $lastReload = 0;

    /**
     * DemonTask constructor.
     * @param DemonTaskModel $demonTask
     */
    public function __construct(DemonTaskModel $demonTask)
    {
        $this->demonTask = $demonTask;
    }


    /**
     * @return mixed
     */
    public function start()
    {
        $this->checkProcessTime();

        while (true)
        {
            DB::connection()->reconnect();

            $this->updateProcessTime();

            if ($this->shouldCreateNewThread())
            {
                $this->fetchAndReserveTasks();
                $this->createNewThread();
            }

            $this->checkForFinishedThreads();
            $this->stopScriptAfterModuleUpdate();
            $this->wait(10000000);
        }
    }

    /**
     * @return void
     */
    protected function checkForFinishedThreads()
    {
        while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0)
        {
            $this->realeaseChildren($pid);
            $this->wait(50);
        }
    }

    protected function shouldCreateNewThread()
    {
        if ($this->getNumberOfWaitingTasks() == 0)
        {
            return false;
        }

        return count($this->children) < 4;
    }

    /**
     * @return void
     */
    protected function createNewThread()
    {
        $pid = pcntl_fork();
        if ($pid)
        {
            $this->children[] = $pid;
        }
        else
        {
            $this->runThread();
        }
    }

    /**
     * @return void
     */
    protected function runThread()
    {
        (new ChildProcess($this->demonTask, $this->waitingTasks, (new Model())))->run();
        exit; //side effect
    }

    /**
     * @param $pid
     * @return void
     */
    protected function realeaseChildren($pid)
    {
        foreach ($this->children as $key => $value)
        {
            if ($value == $pid)
            {
                unset($this->children[$key]);
            }
        }
    }

    /**
     * @return void
     */
    protected function stopScriptAfterModuleUpdate()
    {
        $moduleVersion = $this->getModuleVersion();
        if ($moduleVersion)
        {
            if (is_null($this->moduleVersion))
            {
                $this->moduleVersion = $moduleVersion;
                print_r('set  to: ' . $this->moduleVersion . PHP_EOL);
            }
        }

        if ($moduleVersion && $moduleVersion !== $this->moduleVersion)
        {
            exit();
        }
    }

    public function wait($microseconds = 500000)
    {
        usleep($microseconds);
    }


    public function run()
    {
        //
    }

    private function getNumberOfWaitingTasks()
    {
        return $this->demonTask->isStatusWaiting()->isNotOlder(120)->count();
    }

    private function fetchAndReserveTasks()
    {
        $this->waitingTasks = $this->demonTask
            ->isStatusWaiting()
            ->isNotOlder(120)
            ->limit((new ModuleSettings())->getSetting('recordsCount'))
            ->get();

        $this->demonTask->whereIn('id', $this->waitingTasks->pluck('id'))
            ->update([
                'status' => DemonTaskModel::STATUS_PROCESSING
            ]);
    }

    protected function reloadWhenNeeded($function, $timeout)
    {
        if (time() > $this->lastReload + $timeout)
        {
            $function();
            $this->lastReload = time();
        }
    }

    protected function checkProcessTime()
    {
        $setting = Model::where('setting', 'cronLastUpdate')->first();
        if($setting && ($setting->value + 30 > time()))
        {
            echo "\nCron is already running. Last update: ".$setting->value;
            exit;
        }
    }

    protected function updateProcessTime()
    {
        $query = Model::where('setting', 'cronLastUpdate');
        $time  = time();

        if ($query->count() > 0)
        {
            $query->update(['value' => $time]);
        }
        else
        {
            $query->create(['setting' => 'cronLastUpdate', 'value' => $time]);
        }
    }
}