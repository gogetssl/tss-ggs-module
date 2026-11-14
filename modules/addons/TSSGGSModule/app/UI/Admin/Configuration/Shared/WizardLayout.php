<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Installation wizard layout (design: github.com/gogetssl/ssl-plugin-refresh): module header with progress,
 * vertical step list and the step card. Styles in resources/assets/css/wizard.css, scoped to .tssggs-wizard.
 *
 * Page step3 (legacy modules) is only part of the wizard when a legacy module is installed; without one the
 * wizard has five steps and the numbering adjusts.
 */
class WizardLayout
{
    use TranslatorTrait;

    /** page => [name key, description key] */
    const ALL_STEPS = [
        1 => ['step1_name', 'step1_desc'],
        2 => ['step2_name', 'step2_desc'],
        3 => ['step3_name', 'step3_desc'], // legacy modules found
        4 => ['step4_name', 'step4_desc'],
        5 => ['step5_name', 'step5_desc'],
        6 => ['step6_name', 'step6_desc'],
        7 => ['step7_name', 'step7_desc'], // the migration, between products and finalization
    ];

    protected static $hasLegacy = null;

    public static function hasLegacy(): bool
    {
        if(self::$hasLegacy === null)
        {
            try
            {
                self::$hasLegacy = Detector::anyFound();
            }
            catch(\Throwable $e)
            {
                self::$hasLegacy = false;
            }
        }

        return self::$hasLegacy;
    }

    /**
     * Ordered list of the wizard's pages: [1, 2, (3,) 4, 5, 6]
     */
    public static function pages(): array
    {
        if(!self::hasLegacy())
        {
            return [1, 2, 4, 5, 6];
        }

        // "migrate" chosen in step 3: the migration itself is the step before finalization (page 7)
        return self::migrationPlanned() ? [1, 2, 3, 4, 5, 7, 6] : [1, 2, 3, 4, 5, 6];
    }

    public static function migrationPlanned(): bool
    {
        try
        {
            return Choice::get(Edition::get()) === Choice::MIGRATE;
        }
        catch(\Throwable $e)
        {
            return false;
        }
    }

    public static function pageUrl(int $page): string
    {
        return 'addonmodules.php?module=TSSGGSModule&mg-page=configuration&mg-action=step' . $page;
    }

    public static function nextPage(int $page): int
    {
        $pages = self::pages();
        $i     = array_search($page, $pages, true);

        return $i !== false && isset($pages[$i + 1]) ? $pages[$i + 1] : $page;
    }

    /**
     * Full wizard page for page $page (1..6) with the step content widget
     */
    public function build(int $page, $content): Container
    {
        $wizard = new Container();
        $wizard->setCss('tssggs-wizard');
        $wizard->addElement($this->html($this->headerHtml($page)));

        $body = new Container();
        $body->setCss('tssggs-wizard__body');
        $body->addElement($this->html($this->stepsHtml($page)));

        $section = new Container();
        $section->setCss('tssggs-wizard__section');
        $section->addElement($content);
        $body->addElement($section);

        $wizard->addElement($body);

        return $wizard;
    }

    /**
     * Marks a step widget for the wizard styles and adds its heading. Called from the step's own loadHtml():
     * AJAX-loaded widgets are re-created on the server, so CSS classes set from outside would be lost.
     */
    public static function prepareStep(Widget $widget, int $page, string $title): void
    {
        $widget->appendCss('tssggs-step');
        $widget->addElement(self::heading($page, $title));
    }

    /**
     * "Step N of M" + title, shown at the top of the step card (step widgets have no widget title, so this is the only heading)
     */
    protected static function heading(int $page, string $title): Text
    {
        $layout = new self();
        $title  = preg_replace('/^\s*STEP\s+\d+\s*[.\-:]\s*/i', '', $title);
        $pages  = self::pages();
        $index  = (int)array_search($page, $pages, true) + 1;

        return $layout->html(
            '<div class="tssggs-step__eyebrow">' . htmlspecialchars(sprintf($layout->tr('step_of'), $index, count($pages))) . '</div>'
            . '<h2 class="tssggs-step__title">' . htmlspecialchars($title) . '</h2>'
        );
    }

    protected function headerHtml(int $page): string
    {
        $pages   = self::pages();
        $index   = (int)array_search($page, $pages, true);
        $pct     = count($pages) > 1 ? (int)round($index / (count($pages) - 1) * 100) : 100;
        $version = (string)Config::get('configuration.version', '');

        return '<div class="tssggs-wizard__header">'
            . '<div class="tssggs-wizard__brand">'
            . '<span class="tssggs-wizard__logo">' . $this->icon('shield') . '</span>'
            . '<span><span class="tssggs-wizard__name">' . htmlspecialchars(Edition::name()) . '</span>'
            . '<span class="tssggs-wizard__sub">' . htmlspecialchars($this->tr('module_installation')) . ($version !== '' ? ' &middot; v' . htmlspecialchars($version) : '') . '</span></span>'
            . '</div>'
            . '<div class="tssggs-wizard__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . $pct . '">'
            . '<span class="tssggs-wizard__bar"><span style="width:' . $pct . '%"></span></span>'
            . '<span class="tssggs-wizard__pct">' . $pct . '%</span>'
            . '</div>'
            . '</div>';
    }

    protected function stepsHtml(int $current): string
    {
        $pages = self::pages();
        $pos   = (int)array_search($current, $pages, true);
        $html  = '<nav class="tssggs-wizard__nav" aria-label="' . htmlspecialchars($this->tr('installation_steps')) . '">'
            . '<div class="tssggs-wizard__nav-title">' . htmlspecialchars($this->tr('installation_steps')) . '</div><ol>';

        foreach($pages as $i => $page)
        {
            $state = $i < $pos ? 'is-done' : ($i === $pos ? 'is-active' : 'is-todo');
            $mark  = $i < $pos ? $this->icon('check') : (string)($i + 1);
            $inner = '<span class="tssggs-wizard__dot">' . ($i === $pos ? '<span class="tssggs-wizard__ring"></span>' : '') . $mark . '</span>'
                . '<span class="tssggs-wizard__label"><span class="tssggs-wizard__step-title">' . htmlspecialchars($this->tr(self::ALL_STEPS[$page][0])) . '</span>'
                . '<span class="tssggs-wizard__step-desc">' . htmlspecialchars($this->tr(self::ALL_STEPS[$page][1])) . '</span></span>';

            // finished steps link back to their page
            $item = $i < $pos
                ? '<a class="tssggs-wizard__item" href="addonmodules.php?module=TSSGGSModule&amp;mg-page=configuration&amp;mg-action=step' . $page . '">' . $inner . '</a>'
                : '<span class="tssggs-wizard__item"' . ($i === $pos ? ' aria-current="step"' : '') . '>' . $inner . '</span>';

            $html .= '<li class="' . $state . '">' . $item . '</li>';
        }

        return $html . '</ol></nav>';
    }

    protected function html(string $html): Text
    {
        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);

        return $text;
    }

    protected function tr(string $key): string
    {
        return (string)$this->translate($key, [], ['instalation.wizard']);
    }

    protected function icon(string $name): string
    {
        $paths = [
            'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
            'check'  => '<path d="M20 6 9 17l-5-5"/>',
        ];

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
    }
}
