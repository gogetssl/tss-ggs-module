<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Concerns;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;

/**
 * Shared parts of the settings screens (design: one card, sections with an icon and a short description, one sticky
 * Save bar for the whole screen). The skin is resources/assets/css/settings.css, the dirty / saved behaviour of the bar
 * is resources/assets/js/core/savebar.js.
 */
trait SettingsLayout
{
    /**
     * A section of the screen: icon + title + description ("<title>_desc" in the language file), fields go inside
     */
    protected function section($title, $icon, $parent = null)
    {
        $widget = new Widget();
        $widget->setCss('tssggs-sec');
        $widget->setIcon($icon);
        $widget->setTitle($this->translate($title));
        ($parent ?: $this)->addElement($widget);
        $widget->addElement((new Text())->setText('<p class="tssggs-sec__desc">' . htmlspecialchars($this->translate($title . '_desc')) . '</p>'));

        return $widget;
    }

    /**
     * A small heading inside a section, e.g. "Recurring Orders"
     */
    protected function subSection($title, $parent)
    {
        $widget = new Widget();
        $widget->setCss('tssggs-sec tssggs-sec--sub');
        $widget->setTitle($this->translate($title));
        $parent->addElement($widget);

        return $widget;
    }

    /**
     * The sticky Save bar: Save changes (enabled once something changed) and the "unsaved changes" status.
     *
     * @param string|null $saveTitle   button label, default "Save changes"
     * @param bool        $alwaysOn    no dirty tracking (wizard step, where "Next" must always work)
     * @param array       $extraButtons e.g. the API "Test connection" button
     */
    protected function saveBar($saveTitle = null, $alwaysOn = false, array $extraButtons = [])
    {
        $toolbar = new Toolbar();
        $toolbar->setCss('tssggs-savebar');
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($saveTitle ?: $this->translate('saveChanges'))
                ->onClick(new FormSubmit($this))
        );

        foreach($extraButtons as $button)
        {
            $toolbar->addElement($button);
        }

        $attr = function($key) {
            return htmlspecialchars($this->translate($key));
        };
        $toolbar->addElement((new Text())->setText(
            '<span class="tssggs-savebar__status" role="status" aria-live="polite"' . ($alwaysOn ? ' data-always="1"' : '')
            . ' data-clean="' . $attr('noUnsaved') . '" data-dirty="' . $attr('unsaved') . '" data-failed="' . $attr('notSaved') . '" data-confirm="' . $attr('leaveConfirm') . '">'
            . ($alwaysOn ? '' : $attr('noUnsaved')) . '</span>'
        ));

        $this->addElement($toolbar);
    }
}
