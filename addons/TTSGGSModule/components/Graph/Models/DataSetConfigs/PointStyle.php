<?php

namespace ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs;

use ModulesGarden\TTSGGSModule\Components\Graph\Models\DataSetConfigs\Source\StringDataSetConfig;

class PointStyle extends StringDataSetConfig
{
    public const CIRCLE       = 'circle';
    public const CROSS_ROT    = 'crossRot';
    public const DASH         = 'dash';
    public const LINE         = 'line';
    public const RECT         = 'rect';
    public const RECT_ROT     = 'rectRot';
    public const RECT_ROUNDED = 'rectRounded';
    public const STAR         = 'star';
    public const TRIANGLE     = 'triangle';

}