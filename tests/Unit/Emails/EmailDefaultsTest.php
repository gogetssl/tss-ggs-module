<?php

namespace Tests\Unit\Emails;

use ModulesGarden\TSSGGSModule\App\Helpers\AutoInstallEmailTemplates;
use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use PHPUnit\Framework\TestCase;

/** Every e-mail the module ships has its default text in resources/emails, with a subject line */
class EmailDefaultsTest extends TestCase
{
    public function testEveryModuleEmailHasItsFile(): void
    {
        foreach(EmailTemplates::TEMPLATE_NAMES as $name)
        {
            [$subject, $message] = EmailTemplates::defaults($name);
            $this->assertNotSame('', trim($subject), $name . ': subject');
            $this->assertStringContainsString('{$client_name}', $message, $name . ': message greets the client');
        }
    }

    public function testEveryAutoInstallEmailHasItsFile(): void
    {
        $definitions = AutoInstallEmailTemplates::definitions();
        $this->assertCount(count(AutoInstallEmailTemplates::FILES), $definitions);
        foreach($definitions as $key => [$subject, $message])
        {
            $this->assertNotSame('', trim($subject), $key . ': subject');
            $this->assertStringStartsWith('<p>', $message, $key . ': HTML message');
        }
    }

    public function testNoUnusedFiles(): void
    {
        $used = array_merge(array_map(function($name) {
            return strtolower(str_replace('_', '-', $name));
        }, EmailTemplates::TEMPLATE_NAMES), array_values(AutoInstallEmailTemplates::FILES));
        $files = array_map(function($path) {
            return basename($path, '.html');
        }, glob(dirname(__DIR__, 3) . '/modules/addons/TSSGGSModule/resources/emails/*.html'));
        sort($used);
        sort($files);
        $this->assertSame($used, $files);
    }
}
