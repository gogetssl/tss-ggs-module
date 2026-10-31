<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TSSGGSModule\App\Libs\SystemRequirements;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared\WizardLayout;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * System compatibility check - see App\Libs\SystemRequirements
 */
class Step2 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        $this->setTitle($this->translate('step2_title'));
        WizardLayout::prepareStep($this, 2, $this->translate('step2_title'));

        $checks  = (new SystemRequirements())->check();
        $blocked = SystemRequirements::hasBlockingFailure($checks);

        $intro = new Text();
        $intro->setCss('tssggs-html');
        $intro->setText('<p class="tssggs-checks__intro">' . htmlspecialchars($this->translate($blocked ? 'intro_failed' : 'intro_passed')) . '</p>');
        $this->addElement($intro);

        $rows = '';
        foreach($checks as $check)
        {
            $replace = ['subject' => $check['subject']];
            $name    = $this->translate('check_' . $check['key'], $replace);
            $hint    = $check['status'] === SystemRequirements::STATUS_PASSED ? '' : $this->translate('hint_' . $check['key'], $replace);

            $rows .= '<tr class="is-' . $check['status'] . '">'
                . '<td><span class="tssggs-checks__name">' . htmlspecialchars($name) . '</span>'
                . ($hint !== '' ? '<span class="tssggs-checks__hint">' . htmlspecialchars($hint) . '</span>' : '') . '</td>'
                . '<td>' . htmlspecialchars($check['required']) . '</td>'
                . '<td>' . htmlspecialchars($check['found']) . '</td>'
                . '<td><span class="tssggs-checks__status">' . htmlspecialchars($this->translate('status_' . $check['status'])) . '</span></td>'
                . '</tr>';
        }

        $table = new Text();
        $table->setCss('tssggs-html');
        $table->setText('<div class="tssggs-checks"><table>'
            . '<thead><tr><th>' . htmlspecialchars($this->translate('col_check')) . '</th><th>' . htmlspecialchars($this->translate('col_required'))
            . '</th><th>' . htmlspecialchars($this->translate('col_found')) . '</th><th>' . htmlspecialchars($this->translate('col_status')) . '</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table></div>');
        $this->addElement($table);

        $nextStep = new ButtonSuccess();
        $nextStep->setCss('lu-btn lu-btn--success mt30_custom');
        $nextStep->onClick(Action::redirect('addonmodules.php?module=TSSGGSModule&mg-page=configuration&mg-action=step3'));
        $nextStep->setTitle($this->translate('next_step_btn'));
        if($blocked)
        {
            $nextStep->setDisabled();
        }
        $this->addElement($nextStep);
    }
}
