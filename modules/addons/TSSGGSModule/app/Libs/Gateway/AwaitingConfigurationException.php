<?php

namespace TSSGGSModule\App\Libs\Gateway;

/**
 * The order is placed, but its domain validation method is not set yet: the gateway refuses to show it
 * (502 GEX-4 "Unexpected validation method: not yet set"). The order waits for its configuration; it has not failed.
 * The message is the client text clientText.awaitingValidationMethod (Transport::call).
 */
class AwaitingConfigurationException extends GatewayException
{
}
