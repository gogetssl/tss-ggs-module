<?php

namespace Tests\Unit\Setup;

use PHPUnit\Framework\TestCase;
use TSSGGSModule\App\Kernel\Lang;

/**
 * The setup wizard and the Migration page: what their texts and templates promise the admin.
 */
class WizardScreensTest extends TestCase
{
    const LANGUAGES = ['english', 'german', 'danish'];

    protected static function addon(string $path): string
    {
        return (string)file_get_contents(dirname(__DIR__, 3) . '/modules/addons/TSSGGSModule/' . $path);
    }

    /** The migration is a step of its own, before the finalization: the page that announces it must not say otherwise */
    public function testLegacyStepDoesNotSendTheAdminToTheFinalization(): void
    {
        foreach(self::LANGUAGES as $language)
        {
            $text = (new Lang('admin', $language))->get('admin.configuration.content.step3.runs_later');
            $this->assertNotSame('admin.configuration.content.step3.runs_later', $text, $language);
            foreach(['Finalization', 'Abschluss', 'Afslutning'] as $word)
            {
                $this->assertStringNotContainsString($word, $text, $language);
            }
        }
    }

    /** The line of the check after a run names its three figures in every language */
    public function testVerificationLineKeepsItsFigures(): void
    {
        foreach(self::LANGUAGES as $language)
        {
            $text = (new Lang('admin', $language))->get('admin.migration.verify_result', ['checked' => 25, 'active' => 3, 'left' => 0]);
            $this->assertStringContainsString('25', $text, $language);
            $this->assertStringContainsString('3', $text, $language);
            $this->assertStringNotContainsString(':', str_replace(': ', '', $text), $language . ': a placeholder was left in the text');
        }
    }

    /** "Reload to see the result" belongs to a run that ended while the page was open, not to every finished run */
    public function testReloadNoteIsShownOnlyAfterTheRunEndedOnThisPage(): void
    {
        $template = self::addon('resources/templates/admin/migration/run.tpl');
        $this->assertStringContainsString('x-show="justFinished"', $template);
        $this->assertStringNotContainsString('x-show="progress.finished"', $template);

        $script = self::addon('resources/assets/js/pages/migration.js');
        $this->assertStringContainsString('justFinished: false', $script);
        $this->assertStringContainsString('self.justFinished = true', $script);
    }

    /** The questions of the migration (start, rollback, delete backups) are titled by what they are about, not by the page heading */
    public function testMigrationQuestionsHaveTheirOwnTitle(): void
    {
        $seen = 0;
        foreach(['run.tpl', 'source.tpl'] as $file)
        {
            preg_match_all('/<form\b[^>]*data-confirm=[^>]*>/', self::addon('resources/templates/admin/migration/' . $file), $tags);
            foreach($tags[0] as $tag)
            {
                $seen++;
                $this->assertStringContainsString('data-confirm-title=', $tag, $file);
            }
        }
        $this->assertSame(3, $seen);
    }

    /** The heading of a wizard page counts as the wizard does ("Step 6" over "Step 6 of 7") */
    public function testWizardHeadingFollowsThePosition(): void
    {
        $page = self::addon('app/Pages/Admin/Configuration.php');
        $this->assertStringContainsString("'admin.breadcrumbs.Configuration_step' . (\$pos + 1)", $page);
        $this->assertStringNotContainsString("'admin.breadcrumbs.Configuration_step' . \$page", $page);
    }
}
