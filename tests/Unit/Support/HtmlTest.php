<?php

namespace Tests\Unit\Support;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Html;
use PHPUnit\Framework\TestCase;

/** The shared HTML pieces: escaping, notices, hints - unknown tones fall back, text is escaped */
class HtmlTest extends TestCase
{
    public function testEscapesQuotesAndTags(): void
    {
        $this->assertSame('&lt;b&gt; &quot;x&quot; &#039;y&#039; &amp;', Html::e('<b> "x" \'y\' &'));
        $this->assertSame('5', Html::e(5));
    }

    public function testNoticeTones(): void
    {
        $this->assertSame('<div class="tssggs-note is-ok">Saved</div>', Html::notice('Saved', 'ok'));
        $this->assertSame('<div class="tssggs-note">Info</div>', Html::notice('Info'));
        $this->assertSame('<div class="tssggs-note">x</div>', Html::notice('x', 'nonsense'));
    }

    public function testNoticeTextIsEscaped(): void
    {
        $this->assertSame('<div class="tssggs-note is-bad">&lt;script&gt;</div>', Html::noticeText('<script>', 'bad'));
    }

    public function testHint(): void
    {
        $this->assertStringContainsString('tssggs-renewal-check__warning--green', Html::hint('ok', 'green'));
        $this->assertStringContainsString('tssggs-renewal-check__warning--amber', Html::hint('x', 'purple'));
    }
}
