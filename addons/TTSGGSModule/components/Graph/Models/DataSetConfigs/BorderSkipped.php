<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs;

use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs\Source\StringDataSetConfig;

class BorderSkipped extends StringDataSetConfig
{
    public const START = "start";
    public const END = "end";
    public const MIDDLE = "middle";
    public const BOTTOM = "bottom";
    public const LEFT = "left";
    public const TOP = "top";
    public const RIGHT = "right";
}