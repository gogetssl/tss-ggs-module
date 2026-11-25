<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Gateway;

/**
 * The gateway answered with an error. getMessage() is the text a client may see (SSLTrustCenterApi::readableError);
 * the gateway's own error code (GEX-1 ... GEX-5, AUX-1 ...), its raw message and the HTTP status are kept for code
 * that has to tell errors apart - compare those, never the readable text.
 */
class GatewayException extends \Exception
{
    /** @var string */
    protected $gatewayCode;

    /** @var string */
    protected $gatewayMessage;

    public function __construct(string $readable, int $httpCode, string $gatewayCode = '', string $gatewayMessage = '', ?\Throwable $previous = null)
    {
        parent::__construct($readable, $httpCode, $previous);
        $this->gatewayCode    = $gatewayCode;
        $this->gatewayMessage = $gatewayMessage;
    }

    public function getHttpCode(): int
    {
        return (int)$this->getCode();
    }

    /** e.g. "GEX-4"; '' when the answer had no code (a proxy error page, an empty answer) */
    public function getGatewayCode(): string
    {
        return $this->gatewayCode;
    }

    /** the gateway's message as it was sent, before it was made readable */
    public function getGatewayMessage(): string
    {
        return $this->gatewayMessage;
    }

    /** the gateway's message contains $text (case-insensitive) */
    public function says(string $text): bool
    {
        return $text !== '' && stripos($this->gatewayMessage, $text) !== false;
    }
}
