<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs;

use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs\Source\StringDataSetConfig;

class CubicInterpolationMode extends StringDataSetConfig
{
    public const DEFAULT = "default";
    public const MONOTONE = "monotone";
}