<?php

namespace Tests\Unit\Support;

use ModulesGarden\TSSGGSModule\App\Libs\CronErrors;
use ModulesGarden\TSSGGSModule\App\Libs\LogPolicy;
use ModulesGarden\TSSGGSModule\App\Libs\SecretValue;
use PHPUnit\Framework\TestCase;

/** What goes into logs: secrets masked, cron failures summed up, debug service lists parsed */
class LoggingTest extends TestCase
{
    public function testSecretKeysAreMaskedInArrays(): void
    {
        $this->assertSame(['token' => '***', 'nested' => ['password' => '***', 'name' => 'x'], 'empty' => ''],
            SecretValue::redact(['token' => 'abc', 'nested' => ['password' => 'p', 'name' => 'x'], 'empty' => '']));
    }

    public function testSecretsAreMaskedInRawRequests(): void
    {
        $raw = "GET /x HTTP/2\r\nAuthorization: Bearer abc\r\n\r\n{\"auth_token\":\"secret\",\"domain\":\"domain.tld\"}";
        $out = SecretValue::redact($raw);
        $this->assertStringContainsString('Authorization: ***', $out);
        $this->assertStringContainsString('"auth_token":"***"', $out);
        $this->assertStringContainsString('"domain":"domain.tld"', $out);
        $this->assertStringNotContainsString('abc', $out);
    }

    public function testPrivateKeysAreRemoved(): void
    {
        $out = SecretValue::redact("key: -----BEGIN RSA PRIVATE KEY-----\nMIIE\n-----END RSA PRIVATE KEY----- end");
        $this->assertSame('key: -----PRIVATE KEY REMOVED----- end', $out);
    }

    public function testCronSummaryListsThreeAndCountsTheRest(): void
    {
        $summary = CronErrors::summary([1 => "a\n b", 2 => 'b', 3 => 'c', 4 => 'd'], 10);
        $this->assertSame('4 of 10 orders failed - service #1: a b; service #2: b; service #3: c; and 1 more (details in the module log)', $summary);
    }

    public function testDebugServiceIdsAreParsedFromFreeText(): void
    {
        $this->assertSame([71, 58], LogPolicy::parseIds('#71, 58 and 71; 0'));
        $this->assertSame([], LogPolicy::parseIds(''));
    }
}
