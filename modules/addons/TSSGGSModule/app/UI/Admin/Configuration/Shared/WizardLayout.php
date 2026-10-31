<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Installation wizard layout (design: github.com/gogetssl/ssl-plugin-refresh): module header with progress,
 * vertical step list and the step card. Styles in resources/assets/css/wizard.css, scoped to .tssggs-wizard.
 */
class WizardLayout
{
    use TranslatorTrait;

    const STEPS = 6;

    /**
     * Full wizard page for step $step (1..6) with the step content widget
     */
    public function build(int $step, $content): Container
    {
        $wizard = new Container();
        $wizard->setCss('tssggs-wizard');
        $wizard->addElement($this->html($this->headerHtml($step)));

        $body = new Container();
        $body->setCss('tssggs-wizard__body');
        $body->addElement($this->html($this->stepsHtml($step)));

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
    public static function prepareStep(Widget $widget, int $step, string $title): void
    {
        $widget->appendCss('tssggs-step');
        $widget->addElement(self::heading($step, $title));
    }

    /**
     * "Step N of 6" + title, shown at the top of the step card (the widget's own header is hidden by CSS)
     */
    protected static function heading(int $step, string $title): Text
    {
        $layout = new self();
        $title  = preg_replace('/^\s*STEP\s+\d+\s*[.\-:]\s*/i', '', $title);

        return $layout->html(
            '<div class="tssggs-step__eyebrow">' . htmlspecialchars(sprintf($layout->tr('step_of'), $step, self::STEPS)) . '</div>'
            . '<h2 class="tssggs-step__title">' . htmlspecialchars($title) . '</h2>'
        );
    }

    protected function headerHtml(int $step): string
    {
        $pct     = (int)round(($step - 1) / (self::STEPS - 1) * 100);
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
        $html = '<nav class="tssggs-wizard__nav" aria-label="' . htmlspecialchars($this->tr('installation_steps')) . '">'
            . '<div class="tssggs-wizard__nav-title">' . htmlspecialchars($this->tr('installation_steps')) . '</div><ol>';

        for($i = 1; $i <= self::STEPS; $i++)
        {
            $state = $i < $current ? 'is-done' : ($i === $current ? 'is-active' : 'is-todo');
            $mark  = $i < $current ? $this->icon('check') : (string)$i;
            $inner = '<span class="tssggs-wizard__dot">' . ($i === $current ? '<span class="tssggs-wizard__ring"></span>' : '') . $mark . '</span>'
                . '<span class="tssggs-wizard__label"><span class="tssggs-wizard__step-title">' . htmlspecialchars($this->tr('step' . $i . '_name')) . '</span>'
                . '<span class="tssggs-wizard__step-desc">' . htmlspecialchars($this->tr('step' . $i . '_desc')) . '</span></span>';

            // Finished steps link back to their page
            $item = $i < $current
                ? '<a class="tssggs-wizard__item" href="addonmodules.php?module=TSSGGSModule&amp;mg-page=configuration&amp;mg-action=step' . $i . '">' . $inner . '</a>'
                : '<span class="tssggs-wizard__item"' . ($i === $current ? ' aria-current="step"' : '') . '>' . $inner . '</span>';

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
