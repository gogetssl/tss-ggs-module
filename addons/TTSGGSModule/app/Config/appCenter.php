<?php


use ModulesGarden\VultrVps\Core\Data\Container;
use ModulesGarden\VultrVps\Packages\AppCenter\Enums\AppStatus;
use ModulesGarden\VultrVps\Packages\AppCenter\Libs\Models\App;
use ModulesGarden\VultrVps\Packages\AppCenter\Libs\Models\AppConfigItem;
use ModulesGarden\VultrVps\Packages\AppCenter\Libs\Models\AppGroup;

return [
    //implements AppInterface
    'Apps'     => [
        //You
        \ModulesGarden\TTSGGSModule\Packages\AppCenter\Libs\App\BaseApp::class
    ],
    'LoadApps' => function() {

        /*$group = new AppGroup();
        $group->setName('Sample gropup');
        $group->setDescription('Automatically generated sample group');

        $app = (new \ModulesGarden\VultrVps\Packages\AppCenter\Libs\Models\App())
            ->setGroup($group)
            ->setName('sample App')
            ->setDescription('Description of sample app')
            ->setType(\ModulesGarden\VultrVps\App\Libs\AppCenter\Apps\AppTemplate\AppTemplate::class)
            ->setStatus(AppStatus::STATUS_ACTIVE)
            ->setConfig([
                new AppConfigItem('id', 'sample_id'),
            ]);

        return [$app];*/
    },

    'InstallApp' => function(Container $formData, array $params, App $app) {
        return true;
    },
];