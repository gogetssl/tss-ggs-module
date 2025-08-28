<?php

namespace ModulesGarden\TTSGGSModule\Core\Http;

use ModulesGarden\TTSGGSModule\Core\Traits\IsAdmin;
use ModulesGarden\TTSGGSModule\Core\Traits\OutputBuffer;

/**
 * Description of AbstractController
 */
class AbstractController
{
    use IsAdmin;
    use OutputBuffer;
}
