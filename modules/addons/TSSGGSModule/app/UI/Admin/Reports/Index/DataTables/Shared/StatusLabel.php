<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Reports\Index\DataTables\Shared;

use ModulesGarden\TSSGGSModule\Components\Label\Label;
use ModulesGarden\TSSGGSModule\Components\Label\LabelDanger;
use ModulesGarden\TSSGGSModule\Components\Label\LabelInfo;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\Label\LabelWarning;

/**
 * Colours of the order and payment statuses in the reports: green = done, yellow = waiting for someone,
 * blue = in progress, red = ended or failed, grey = anything else
 */
class StatusLabel
{
    const ORDER = [
        'active'                 => LabelSuccess::class,
        'issued'                 => LabelSuccess::class,
        'pending'                => LabelWarning::class,
        'incomplete'             => LabelWarning::class,
        'need_action'            => LabelWarning::class,
        'awaiting validation'    => LabelWarning::class,
        'unpaid'                 => LabelWarning::class,
        'suspended'              => LabelWarning::class,
        'processing'             => LabelInfo::class,
        'new_order'              => LabelInfo::class,
        'user_confirmed'         => LabelInfo::class,
        'pre_issue'              => LabelInfo::class,
        'reissue'                => LabelInfo::class,
        'renew'                  => LabelInfo::class,
        'cancelled'              => LabelDanger::class,
        'canceled'               => LabelDanger::class,
        'terminated'             => LabelDanger::class,
        'expired'                => LabelDanger::class,
        'rejected'               => LabelDanger::class,
        'revoked'                => LabelDanger::class,
        'refunded'               => LabelDanger::class,
        'failed'                 => LabelDanger::class,
        'fraud'                  => LabelDanger::class,
    ];

    const PAYMENT = [
        'paid'            => LabelSuccess::class,
        'pending'         => LabelWarning::class,
        'payment pending' => LabelWarning::class,
        'draft'           => Label::class,
    ];

    /** an order / service status ("Awaiting Configuration" and unknown ones are grey) */
    public static function order($value): Label
    {
        return self::make(self::ORDER, $value, Label::class);
    }

    /** an invoice status (unpaid, overdue, cancelled, refunded, collections are red) */
    public static function payment($value): Label
    {
        return self::make(self::PAYMENT, $value, LabelDanger::class);
    }

    protected static function make(array $map, $value, string $default): Label
    {
        $class = $map[strtolower(trim((string)$value))] ?? $default;
        $label = new $class();
        $label->setText($value);

        return $label;
    }
}
