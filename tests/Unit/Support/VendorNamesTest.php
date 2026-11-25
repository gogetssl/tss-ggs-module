<?php

namespace Tests\Unit\Support;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use PHPUnit\Framework\TestCase;

/** Vendor names in messages: the keys are stored lower case, older code passes them upper case */
class VendorNamesTest extends TestCase
{
    public function testVendorNameIgnoresCase(): void
    {
        $this->assertSame('GoGetSSL', Helpers::vendorToDisplay('ggs'));
        $this->assertSame('GoGetSSL', Helpers::vendorToDisplay('GGS'));
        $this->assertSame('TheSSLSTORE', Helpers::vendorToDisplay('tss'));
        $this->assertSame('TheSSLSTORE', Helpers::vendorToDisplay('TSS'));
    }
}
