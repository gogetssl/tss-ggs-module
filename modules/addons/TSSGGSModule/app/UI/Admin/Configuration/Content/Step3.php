<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Migration as MigrationController;
use ModulesGarden\TSSGGSModule\App\Migration\Choice;
use ModulesGarden\TSSGGSModule\App\Migration\Detector;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Wizard step "Legacy modules found": what was detected and, per module, the decision Migrate / Install standalone.
 * The migration itself runs after the API connection and product steps (page "migration").
 */
class Step3 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        WizardLayout::prepareStep($this, 3, $this->translate('step3_title'));

        $found = Detector::detect();
        $html  = '<p class="tssggs-checks__intro">' . htmlspecialchars($this->translate('intro')) . '</p>';

        foreach($found as $source => $info)
        {
            $c      = $info['counts'];
            $choice = Choice::get($source) ?: ($info['support']['supported'] ? Choice::MIGRATE : Choice::SKIP);
            $band   = $info['support']['band'];

            $html .= '<div class="tssggs-legacy">'
                . '<div class="tssggs-legacy__head"><b>' . htmlspecialchars($info['label']) . '</b> <span class="tssggs-muted">' . htmlspecialchars($info['version'] ?: $this->translate('version_unknown')) . '</span>'
                . ' <span class="tssggs-checks__status is-' . ($band === 'full' ? 'passed' : ($band === 'partial' ? 'warning' : 'failed')) . '">' . htmlspecialchars($this->translate('support_' . $band)) . '</span></div>'
                . '<ul class="tssggs-legacy__facts">'
                . '<li>' . htmlspecialchars($this->translate('fact_products', ['n' => (int)$c['products']['total']])) . '</li>'
                . '<li>' . htmlspecialchars($this->translate('fact_services', ['active' => (int)($c['services']['Active'] ?? 0), 'total' => array_sum($c['services'])])) . '</li>'
                . '<li>' . htmlspecialchars($this->translate('fact_orders', ['n' => array_sum($c['orders']), 'unconfigured' => (int)$c['unconfigured'], 'unpaid' => (int)$c['unpaid']])) . '</li>'
                . '<li>' . htmlspecialchars($this->translate('fact_subscriptions', ['acme' => (int)$c['acme'], 'ais' => (int)$c['ais'], 'autoinstall' => (int)$c['autoinstall']])) . '</li>'
                . '</ul>';

            if($info['support']['reason'])
            {
                $html .= '<p class="tssggs-muted">' . htmlspecialchars($info['support']['reason']) . '</p>';
            }

            if($info['support']['supported'])
            {
                $html .= '<div class="tssggs-legacy__choice">'
                    . '<label><input type="radio" name="choice[' . $source . ']" value="migrate"' . ($choice === Choice::MIGRATE ? ' checked' : '') . '> <b>' . htmlspecialchars($this->translate('choice_migrate')) . '</b><span>' . htmlspecialchars($this->translate('choice_migrate_desc')) . '</span></label>'
                    . '<label><input type="radio" name="choice[' . $source . ']" value="skip"' . ($choice === Choice::SKIP ? ' checked' : '') . '> <b>' . htmlspecialchars($this->translate('choice_skip')) . '</b><span>' . htmlspecialchars($this->translate('choice_skip_desc')) . '</span></label>'
                    . '</div>';
            }
            else
            {
                $html .= '<div class="tssggs-note is-bad">' . htmlspecialchars($this->translate('unsupported_hint')) . '</div>'
                    . '<input type="hidden" name="choice[' . $source . ']" value="skip">';
            }
            $html .= '</div>';
        }

        $html .= '<p class="tssggs-muted">' . htmlspecialchars($this->translate('runs_later')) . '</p>'
            . '<button type="submit" class="lu-btn lu-btn--success mt30_custom">' . htmlspecialchars($this->translate('next_step_btn')) . '</button>';

        $form = new Text();
        $form->setCss('tssggs-html');
        $form->setText('<form method="post" action="addonmodules.php?module=TSSGGSModule&mg-page=configuration&mg-action=saveLegacyChoice" class="tssggs-legacy-form">'
            . '<input type="hidden" name="token" value="' . htmlspecialchars(MigrationController::token()) . '">' . $html . '</form>');
        $this->addElement($form);
    }
}
