<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use TSSGGSModule\App\Libs\Support\Csv;
use TSSGGSModule\App\Libs\Support\SecretValue;

/**
 * Rules that keep the module safe: what goes into a CSV file or a log, and what a client action checks first.
 */
class SecurityRulesTest extends TestCase
{
    protected static function source(string $path): string
    {
        return (string)file_get_contents(dirname(__DIR__, 3) . '/modules/' . $path);
    }

    public function testCsvCellsThatWouldRunAsAFormulaBecomeText(): void
    {
        $this->assertSame("'=HYPERLINK(\"https://x\",\"y\")", Csv::cell('=HYPERLINK("https://x","y")'));
        $this->assertSame("'+cmd|' /C calc'!A0", Csv::cell("+cmd|' /C calc'!A0"));
        $this->assertSame("'@SUM(1+1)", Csv::cell('@SUM(1+1)'));
        $this->assertSame("' =1+1", Csv::cell(' =1+1'), 'spaces before the sign do not hide it');
        $this->assertSame("'\t=1+1", Csv::cell("\t=1+1"));
        $this->assertSame("'-domain.tld", Csv::cell('-domain.tld'));
    }

    public function testCsvLeavesOrdinaryCellsAndNumbersAlone(): void
    {
        $this->assertSame('domain.tld', Csv::cell('domain.tld'));
        $this->assertSame('Company = Ltd', Csv::cell('Company = Ltd'));
        $this->assertSame('-12.50', Csv::cell('-12.50'), 'a negative amount stays a number');
        $this->assertSame(-3, Csv::cell(-3));
        $this->assertSame('', Csv::cell(''));
        $this->assertSame([1, "'=A1", 'ok'], Csv::row([1, '=A1', 'ok']));
    }

    public function testVendorLinksAndCodesAreMaskedInLogs(): void
    {
        $masked = SecretValue::redact(['token_init_code' => 'ABC123', 'token_setup_link' => 'https://vendor.tld/setup/xyz', 'enrollment_link' => 'https://vendor.tld/enroll/xyz', 'domain' => 'domain.tld']);
        $this->assertSame('***', $masked['token_init_code']);
        $this->assertSame('***', $masked['token_setup_link']);
        $this->assertSame('***', $masked['enrollment_link']);
        $this->assertSame('domain.tld', $masked['domain']);

        $text = SecretValue::redact('{"enrollment_link":"https://vendor.tld/enroll/xyz","status":"ok"}');
        $this->assertStringNotContainsString('vendor.tld', $text);
        $this->assertStringContainsString('"status":"ok"', $text);
    }

    /** Every client action that changes something asks for the token first, and the subscription actions for their product */
    public function testClientActionsCheckBeforeTheyAct(): void
    {
        $parts = dirname(__DIR__, 3) . '/modules/servers/TSSGGSModule/app/Client/Parts';
        $acme  = (string)file_get_contents($parts . '/AcmeActions.php');
        foreach(['createSubscriptionJSON', 'buyMoreDomainsJSON', 'upgradeConfigOptionsJSON'] as $action)
        {
            $this->assertMatchesRegularExpression('/function ' . $action . '\([^)]*\)\s*\{\s*\$this->requireCsrfToken\(\);\s*(\/\/[^\n]*\s*)*\$this->requireOrderableService\(\$input, AcmeHelpers::isAcmeWhmcsProduct/', $acme, $action);
        }
        foreach(['removeDomainJSON', 'cancelSubscriptionJSON', 'stopAutoRenewalJSON'] as $action)
        {
            $this->assertMatchesRegularExpression('/function ' . $action . '\([^)]*\)\s*\{\s*\$this->requireCsrfToken\(\);\s*\$this->requireProduct\(AcmeHelpers::isAcmeWhmcsProduct/', $acme, $action);
        }
        $this->assertStringContainsString('$this->requireRenewable((int)$input[\'id\']);', (string)file_get_contents($parts . '/SslServiceActions.php'));
        $this->assertStringContainsString("(\$params['status'] ?? '') !== 'Active'", (string)file_get_contents($parts . '/SslConfigurationActions.php'));
    }

    /** The service a client action works on is the one WHMCS checked the owner of */
    public function testClientRequestsUseTheServiceWhmcsChecked(): void
    {
        $clientArea = self::source('servers/TSSGGSModule/ClientArea.php');
        $this->assertSame(1, substr_count($clientArea, "\$input['id']     = (int)\$params['serviceid'];"));
        // both the page and the background call go through run(), which sets it
        $this->assertSame(2, substr_count($clientArea, 'self::run(self::action($input)'));
    }

    /** An admin address is an instance method without arguments: a public helper of a page is not one */
    public function testRouterDoesNotServeHelpers(): void
    {
        $kernel = self::source('addons/TSSGGSModule/app/Kernel/Kernel.php');
        $this->assertStringContainsString('->isStatic()', $kernel);
        $this->assertStringContainsString('->getNumberOfParameters() > 0', $kernel);
    }

    /** The update work folder is never more open than the module, and its repair script runs only as the updater wrote it */
    public function testUpdateWorkFolderRules(): void
    {
        $update = 'addons/TSSGGSModule/app/Libs/Update/';
        foreach(['Workdir.php', 'Installer.php', 'Swap.php', 'Health.php'] as $file)
        {
            $this->assertDoesNotMatchRegularExpression('/(mkdir|chmod)\([^)]*,\s*0(775|664|777|666)\b/', self::source($update . $file), $file);
        }
        $installer = self::source($update . 'Installer.php');
        $this->assertStringContainsString('self::verifyStaging($manifest[\'edition\'][\'files\']);', $installer);
        $this->assertStringContainsString('if(!self::backupIntact($backup))', $installer);
        $this->assertStringContainsString('bin2hex(random_bytes(8))', $installer);
        $this->assertStringNotContainsString("addFromString('settings.json'", $installer);
        foreach(['addons/TSSGGSModule/hooks.php', 'servers/TSSGGSModule/hooks.php'] as $hooks)
        {
            $this->assertStringContainsString('fileowner($moduleUpdateRecover)', self::source($hooks), $hooks);
            $this->assertStringContainsString('& 0022) === 0', self::source($hooks), $hooks);
        }
    }
}
