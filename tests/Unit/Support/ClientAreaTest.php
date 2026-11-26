<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use TSSGGSModule\Server\ClientArea;
use TSSGGSModule\Server\Text;

/**
 * The client area runs on the module's own small classes: texts (Text), the page router (ClientArea). No code
 * inherited from a third-party framework is left in either module.
 */
class ClientAreaTest extends TestCase
{
    public function testTextsAreFoundByTheirPathAndAreHtmlSafe(): void
    {
        $this->assertNotSame('title', Text::get('sslTools', 'title'));
        $this->assertSame('missingKey', Text::get('sslTools', 'missingKey'), 'a missing text answers its key');
        $this->assertSame('nowhere', Text::get('nowhere', 'title'));
        $this->assertNull(Text::plain('sslTools', 'missingKey'));
        $this->assertSame(Text::plain('sslTools', 'title'), html_entity_decode(Text::get('sslTools', 'title'), ENT_QUOTES, 'UTF-8'));
    }

    public function testPageTemplatesStartInTheirOwnGroup(): void
    {
        $page = new Text(ClientArea::TEXT_BASE);
        // a key of the client pages ($_LANG['serverCA']['home'][...]) is found without naming the group
        $this->assertSame(Text::get('serverCA', 'home', 'anErrorOccurred'), $page->t('anErrorOccurred'));
        $this->assertNotSame('anErrorOccurred', $page->t('anErrorOccurred'));
    }

    public function testOnlyPublicPageMethodsAreAddresses(): void
    {
        $run = new \ReflectionMethod(ClientArea::class, 'run');
        $run->setAccessible(true);
        foreach(['noSuchActionHTML', 'requireProductHTML', 'requireCsrfToken'] as $method)
        {
            try
            {
                $run->invoke(null, $method, ['serviceid' => 1], []);
                $this->fail($method . ' was run');
            }
            catch(\RuntimeException $e)
            {
                $this->assertSame('Unknown action', $e->getMessage(), $method);
            }
        }

        $action = new \ReflectionMethod(ClientArea::class, 'action');
        $action->setAccessible(true);
        $this->assertSame('index', $action->invoke(null, ['mod-action' => '../x']));
        $this->assertSame('index', $action->invoke(null, []));
        $this->assertSame('renew', $action->invoke(null, ['mod-action' => 'renew']));
    }

    /** Names and code of the old framework stay out of the module */
    public function testNoInheritedFrameworkCodeIsLeft(): void
    {
        $root  = dirname(__DIR__, 3) . '/modules';
        $found = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach($files as $file)
        {
            if(!preg_match('/\.(php|tpl|js|css|yml|json)$/', $file->getFilename()))
            {
                continue;
            }
            $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            if(preg_match('#^servers/[^/]+/core/|(^|/)mgLibs/|^servers/[^/]+/Loader\.php$|modLibs|mg-?style#i', $path))
            {
                $found[] = $path;
                continue;
            }
            $text = (string)file_get_contents($file->getPathname());
            if(preg_match('/ModulesGarden|Modules Garden|MGModule|mgLibs|MGLANG|MODLANG|AbstractMainDriver|MainInstance|runControler|absoluteT\(/i', $text, $m))
            {
                $found[] = $path . ' (' . $m[0] . ')';
            }
        }
        $this->assertSame([], $found);
    }
}
