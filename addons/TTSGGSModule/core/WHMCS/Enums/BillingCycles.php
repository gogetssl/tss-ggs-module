<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Enums;

final class BillingCycles
{
    const CYCLE_FREE            = "free";
    const CYCLE_ONETIME         = "onetime";
    const CYCLE_MONTHLY         = "monthly";
    const CYCLE_QUARTERLY       = "quarterly";
    const CYCLE_SEMI_ANNUALLY   = "semiannually";
    const CYCLE_ANNUALLY        = "annually";
    const CYCLE_BIENNIALLY      = "biennially";
    const CYCLE_TRIENNIALLY     = "triennially";

    const NON_RECURRING_CYCLES = [
        self::CYCLE_FREE,
        self::CYCLE_ONETIME
    ];

    const RECURRING_CYCLES = [
        self::CYCLE_MONTHLY,
        self::CYCLE_QUARTERLY,
        self::CYCLE_SEMI_ANNUALLY,
        self::CYCLE_ANNUALLY,
        self::CYCLE_BIENNIALLY,
        self::CYCLE_TRIENNIALLY
    ];
}