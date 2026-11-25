<?php

namespace Tests\Unit\Kernel;

use ModulesGarden\TSSGGSModule\App\Kernel\Kernel;
use ModulesGarden\TSSGGSModule\App\Kernel\Lang;
use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Kernel\Request;
use PHPUnit\Framework\TestCase;

/**
 * The module's own kernel: which requests it takes, how input is read, links and texts.
 */
class KernelTest extends TestCase
{
    public function testOnlyPagesWithAClassAreHandled(): void
    {
        $this->assertTrue(Kernel::handles('admin', 'certificateLog'));
        $this->assertFalse(Kernel::handles('admin', 'noSuchPage'), 'a page without a class stays on the old framework');
        $this->assertFalse(Kernel::handles('admin', '../Kernel/Kernel'));
        $this->assertFalse(Kernel::handles('admin', null));
        $this->assertFalse(Kernel::handles('admin', ['certificateLog']));
    }

    public function testRequestReadsTypedInput(): void
    {
        $request = new Request(['mg-page' => 'certificateLog', 'service' => '12', 'type' => 'bogus', 'p' => '2x'], ['search' => ' abc '], ['REQUEST_METHOD' => 'post', 'HTTP_HX_REQUEST' => 'true']);

        $this->assertSame('certificateLog', $request->page());
        $this->assertSame('index', $request->action());
        $this->assertSame(12, $request->int('service'));
        $this->assertSame(1, $request->int('p', 1), 'not a number: the default');
        $this->assertSame('', $request->oneOf('type', ['success', 'info', 'error']));
        $this->assertSame('abc', $request->string('search'));
        $this->assertTrue($request->isPost());
        $this->assertTrue($request->isHtmx());
        $this->assertFalse($request->hasValidToken(), 'no token: a POST is refused');
    }

    public function testPageUrlLeavesOutEmptyValues(): void
    {
        $url = Page::pageUrl('certificateLog', 'index', ['service' => 0, 'type' => '', 'search' => 'a b', 'p' => 2]);

        $this->assertSame('addonmodules.php?module=' . Kernel::module() . '&mg-page=certificateLog&search=a+b&p=2', $url);
        $this->assertStringContainsString('&mg-action=clear', Page::pageUrl('certificateLog', 'clear'));
    }

    public function testLangReplacesNamesAndFallsBackToEnglish(): void
    {
        $lang = new Lang('admin', 'english');
        $this->assertSame('Page 2 of 5 (240 events)', $lang->get('admin.certificate_log.page', ['page' => 2, 'pages' => 5, 'total' => 240]));
        $this->assertSame('no.such.key', $lang->get('no.such.key'));

        $german = new Lang('admin', 'german');
        $this->assertNotSame($lang->get('admin.certificate_log.filter'), $german->get('admin.certificate_log.empty'));
        $this->assertTrue((new Lang('admin', 'klingon'))->has('admin.menu.home'), 'an unknown language shows English');
    }

    /**
     * WHMCS adds its token field to every form by looking for "<form ...>" up to the first ">". A ">" inside an
     * attribute of the tag (an arrow function in @submit) cuts the tag in two and the page shows the rest as text.
     */
    public function testNoFormTagOfATemplateContainsAGreaterThanSign(): void
    {
        $root = dirname(__DIR__, 3) . '/modules/addons/TSSGGSModule/resources/templates';
        $it   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        $seen = 0;
        foreach($it as $file)
        {
            preg_match_all('/<form\b[^>]*>/', (string)file_get_contents($file->getPathname()), $tags);
            foreach($tags[0] as $tag)
            {
                $seen++;
                $this->assertSame(0, substr_count($tag, '"') % 2, $file->getFilename() . ': ">" inside the form tag ' . substr($tag, 0, 80));
            }
        }
        $this->assertGreaterThan(0, $seen);
    }
}
