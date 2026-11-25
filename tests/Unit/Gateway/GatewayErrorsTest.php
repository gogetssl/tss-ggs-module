<?php

namespace Tests\Unit\Gateway;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\AwaitingConfigurationException;
use ModulesGarden\TSSGGSModule\App\Libs\Gateway\GatewayException;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use PHPUnit\Framework\TestCase;

/** How gateway errors become texts a client can read, and which of them mean "waiting for configuration" */
class GatewayErrorsTest extends TestCase
{
    public function testServerErrorWithMessageAsksToTryAgain(): void
    {
        $this->assertSame('The SSL service could not complete the request: Order not found. Please try again later.',
            SSLTrustCenterApi::readableError('Order not found!', 502));
    }

    public function testClientErrorIsTheMessageAsASentence(): void
    {
        $this->assertSame('Domain is not valid.', SSLTrustCenterApi::readableError('Domain is not valid', 400));
    }

    public function testVendorErrorFormatKeepsOnlyTheMessage(): void
    {
        $this->assertSame('CSR is invalid.', SSLTrustCenterApi::readableError('Vendor returns error:ErrorCode:-1|ErrorMessage:CSR is invalid.', 400));
    }

    public function testEmptyMessageNamesTheHttpCode(): void
    {
        $this->assertSame('The SSL service is temporarily unavailable (HTTP 503). Please try again later.', SSLTrustCenterApi::readableError('', 503));
        $this->assertSame('The SSL service could not process the request (HTTP 404).', SSLTrustCenterApi::readableError('', 404));
    }

    public function testAwaitingConfigurationIsRecognised(): void
    {
        $this->assertTrue(SSLTrustCenterApi::isAwaitingConfiguration(new AwaitingConfigurationException('anything', 502)));
        $this->assertTrue(SSLTrustCenterApi::isAwaitingConfiguration(new \Exception(SSLTrustCenterApi::readableError('Unexpected validation method: not yet set', 502))));
        $this->assertFalse(SSLTrustCenterApi::isAwaitingConfiguration(new \Exception(SSLTrustCenterApi::readableError('Unexpected validation method: pre-validated (public key reuse)', 502))));
    }

    public function testGatewayExceptionKeepsCodeAndRawMessage(): void
    {
        $e = new GatewayException('Readable.', 502, 'GEX-4', 'Unexpected validation method: not yet set');
        $this->assertSame(502, $e->getHttpCode());
        $this->assertSame('GEX-4', $e->getGatewayCode());
        $this->assertTrue($e->says('NOT YET SET'));
        $this->assertFalse($e->says(''));
        $this->assertInstanceOf(\Exception::class, $e, 'callers catching \Exception keep working');
        $this->assertInstanceOf(GatewayException::class, new AwaitingConfigurationException('x', 502));
    }

    public function testItemIdComesFromTheFirstItem(): void
    {
        $this->assertSame(455294, SSLTrustCenterApi::getItemId(['items' => [['id' => '455294']]]));
        $this->assertSame(7, SSLTrustCenterApi::getItemId(['items' => []], '7'));
    }

    public function testMissingItemIdWithoutDefaultThrows(): void
    {
        $this->expectException(\Exception::class);
        SSLTrustCenterApi::getItemId(['items' => [['id' => 0]]]);
    }
}
