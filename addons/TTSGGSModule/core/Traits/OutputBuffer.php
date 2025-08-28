<?php

namespace ModulesGarden\TTSGGSModule\Core\Traits;

use function ob_clean;
use function ob_get_contents;
use function ob_start;

/**
 * @deprecated
 */
trait OutputBuffer
{
    /**
     * @deprecated
     */
    protected function cleanOutputBuffer()
    {
        $outputBuffering = ob_get_contents();
        if ($outputBuffering !== false)
        {
            if (!empty($outputBuffering))
            {
                ob_clean();
                ob_start();
            }
            else
            {
                ob_start();
            }
        }

        return $this;
    }
}
