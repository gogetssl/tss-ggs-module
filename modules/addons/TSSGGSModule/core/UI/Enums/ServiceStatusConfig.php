<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\Enums;

use ModulesGarden\TSSGGSModule\Core\WHMCS\Enums\HostingDomainStatus;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Type;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonInfo;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonWarning;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonDanger;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonBasic;

class ServiceStatusConfig
{
    public static function all():array
    {
        return [
            HostingDomainStatus::STATUS_PENDING => [
                'type'   => Type::INFO,
                'button' => ButtonInfo::class,
                'icon'   => 'hourglass-alt',
            ],
            HostingDomainStatus::STATUS_ACTIVE => [
                'type'   => Type::SUCCESS,
                'button' => ButtonSuccess::class,
                'icon'   => 'check',
            ],
            HostingDomainStatus::STATUS_SUSPENDED => [
                'type'   => Type::WARNING,
                'button' => ButtonWarning::class,
                'icon'   => 'play',
            ],
            HostingDomainStatus::STATUS_TERMINATED => [
                'type'   => Type::DANGER,
                'button' => ButtonDanger::class,
                'icon'   => 'minus-circle-outline',
            ],
            HostingDomainStatus::STATUS_CANCELLED => [
                'type'   => Type::DEFAULT,
                'button' => ButtonBasic::class,
                'icon'   => 'minus-circle-outline',
            ],
            HostingDomainStatus::STATUS_FRAUD => [
                'type'   => Type::DANGER,
                'button' => ButtonDanger::class,
                'icon'   => 'minus-circle-outline',

            ],
            HostingDomainStatus::STATUS_COMPLETED => [
                'type'   => Type::INFO,
                'button' => ButtonInfo::class,
                'icon'   => 'hourglass-alt',
            ]
        ];
    }

    public static function byStatus(string $status):array
    {
        return self::all()[$status] ?: [];
    }
}