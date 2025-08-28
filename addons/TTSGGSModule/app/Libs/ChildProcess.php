<?php

namespace ModulesGarden\TTSGGSModule\App\Libs;


use Illuminate\Database\Capsule\Manager as DB;
use ModulesGarden\TTSGGSModule\App\Models\DemonTask;
use ModulesGarden\TTSGGSModule\App\Models\DemonTask as DemonTaskModel;
use ModulesGarden\TTSGGSModule\App\Models\Request;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models\ModuleSettings;

class ChildProcess
{
    /**
     * @var \ModulesGarden\TTSGGSModule\App\Models\DemonTask
     */
    protected $demonTask;
    /**
     * @var int
     */
    protected $recordCount = 0;

    /**
     * @var
     */
    private $microTimeStart;

    /**
     * @var array
     */
    private $cronOutputsMessages = [];


    /**
     * @var Collection
     */
    private $waitingTasks;

    private $moduleSettings;

    /**
     * DemonTask constructor.
     * @param DemonTaskModel $demonTask
     * @param \ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models\ModuleSettings $moduleSettings
     */
    public function __construct(DemonTaskMOdel $demonTask, $waitingTasks, ModuleSettings $moduleSettings)
    {
        $this->demonTask          = $demonTask;
        $this->waitingTasks       = $waitingTasks;
        $this->moduleSettings     = $moduleSettings;
    }

    /**
     *
     */
    public function run()
    {
        DB::reconnect();
        $this->process();
        DB::disconnect();
    }

    /**
     *
     */
    public function process()
    {
        /**
         *
         */
        $this->initiateStartProcessDetails();

        $data = $this->waitingTasks->toArray();

        foreach ($data as $task) {

            // update status to do
            $serviceId = $task['service_id'];
            $service = DB::table('tblhosting')->where('id', $serviceId)->first();
            $ssl = DB::table('tblsslorders')->where('serviceid', $serviceId)->first();

            $configProduct = (new ProductRepository())->getProductConfiguration($service->packageid);
            $addonConfig = (new AddonModuleRepository())->getModuleConfiguration();

            $provider = strtolower($configProduct['provider']);
            $credentials = $addonConfig['credentials'][$provider];

            if($provider == 'tss' && $credentials['OperationMode'] == 'sandbox')
            {
                $credentials['PartnerCode'] = $credentials['TestPartnerCode'];
                $credentials['AuthToken'] =  $credentials['TestAuthToken'];
            }

            $api = new SSLTrustCenterApi($configProduct['provider'], $credentials['PartnerCode'], $credentials['AuthToken'], $configProduct['category']);
            $orderData = $api->getOrder($ssl->remoteid, $serviceId);

            $orderFiles = [];
            Request::where('serviceid', $serviceId)->where('name', 'addOrder')->update([
                'status' => $orderData['order']['status']
            ]);

            $request = ['orderData' => $orderData, 'orderFiles' => $orderFiles];

            Request::where('serviceid', $serviceId)->where('name', 'certificate')->delete();
            Request::insert([
                'serviceid' => $serviceId,
                'name' => 'certificate',
                'request' => \encrypt(json_encode($request)),
                'status' => $orderData['order']['status'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($orderData['order']['status'] == 'cancelled' || $orderData['order']['status'] == 'expired' || $orderData['order']['status'] == 'filed' || $orderData['order']['status'] == 'rejected')
            {
                Service::where('id', $serviceId)->update([
                    'domainstatus' => 'Cancelled'
                ]);
            }

            if($orderData['order']['status'] == 'processing')
            {
                DemonTask::where('id', $task['id'])->update([
                    'status' => 'waiting'
                ]);
            }

        }

        $this->finishProcessDetails();
        $this->outputMessagesOnConsole();
    }
    protected function initiateStartProcessDetails()
    {
        $this->cronOutputsMessages = [];
        $this->microTimeStart      = microtime(true);
        $script                    = implode(' ', $_SERVER['argv']);

        $this->cronOutput("\r\n");
        $this->cronOutput("Script: {$script}");
        $this->cronOutput("Process Start:" . date('Y-m-d H:i:s'));
    }

    /**
     *
     */
    protected function finishProcessDetails()
    {
        $time = microtime(true) - $this->microTimeStart;
        $this->cronOutput("Process Stop:" . date('Y-m-d H:i:s'));
        $this->cronOutput("Cron Job has been finished in: {$time} sec");
    }

    /**
     * @param string $message
     */
    protected function cronOutput($message = "")
    {
        $this->cronOutputsMessages[] = "\r\n{$message}";

    }

    /**
     *
     */
    protected function outputMessagesOnConsole()
    {
        foreach ($this->cronOutputsMessages as $message)
        {
            echo $message;
        }
    }
}