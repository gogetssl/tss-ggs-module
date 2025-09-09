<?php

namespace ModulesGarden\TSSGGSModule\Core\Http;

use ModulesGarden\TSSGGSModule\Core\Traits\IsAdmin;
use ModulesGarden\TSSGGSModule\Core\Traits\OutputBuffer;

/**
 * Description of AbstractController
 */
class AbstractController
{
    use IsAdmin;
    use OutputBuffer;
}
