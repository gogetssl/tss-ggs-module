<?php

namespace Tests\Unit\Certificates;

use TSSGGSModule\App\Libs\Certificates\FreeName;
use TSSGGSModule\App\Libs\Certificates\IpCertificate;
use PHPUnit\Framework\TestCase;

/** The free www / base-domain name the CA adds itself, and public IP detection */
class DomainNamesTest extends TestCase
{
    public function testTwinOfABaseDomainIsWww(): void
    {
        $this->assertSame('www.domain.tld', FreeName::twin('Domain.TLD '));
    }

    public function testTwinOfWwwIsTheBaseDomain(): void
    {
        $this->assertSame('domain.tld', FreeName::twin('www.domain.tld'));
    }

    public function testTwinOfAWildcardIsTheBaseDomain(): void
    {
        $this->assertSame('domain.tld', FreeName::twin('*.domain.tld'));
    }

    public function testIpAndEmptyNamesHaveNoTwin(): void
    {
        $this->assertSame('', FreeName::twin('203.0.113.5'));
        $this->assertSame('', FreeName::twin(''));
    }

    public function testStripRemovesOnlyTheTwinInBothListShapes(): void
    {
        $this->assertSame(['sub.domain.tld'], FreeName::strip(['www.domain.tld', 'sub.domain.tld'], 'domain.tld'));
        $this->assertSame([['name' => 'sub.domain.tld']], FreeName::strip([['name' => 'WWW.domain.tld'], ['name' => 'sub.domain.tld']], 'domain.tld'));
    }

    public function testPublicIp(): void
    {
        $this->assertTrue(IpCertificate::isPublicIp('8.8.8.8'));
        $this->assertFalse(IpCertificate::isPublicIp('192.168.1.10'));
        $this->assertFalse(IpCertificate::isPublicIp('127.0.0.1'));
        $this->assertFalse(IpCertificate::isPublicIp('domain.tld'));
    }
}
