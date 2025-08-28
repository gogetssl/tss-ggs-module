<?php

namespace ModulesGarden\TTSGGSModule\Core\Http;

use ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Description of Response
 */
class BinaryFileResponse extends \Symfony\Component\HttpFoundation\BinaryFileResponse implements ResponseInterface
{
    protected $offset = 0;
    protected $maxlen = -1;

    public function __construct($file, int $status = 200, array $headers = [], bool $public = true, string $contentDisposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT, bool $autoEtag = false, bool $autoLastModified = true)
    {
        parent::__construct($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);
    }
}
