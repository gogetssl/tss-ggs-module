<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Forms;


use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\CronStatus;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Providers\CronSettingsProvider;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Components\Form\Form;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Toolbar\Toolbar;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\FormSubmit;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

/**
 * One block per cron job - what it does, when it ran last and, right below, how often it runs - in one form with one
 * Save button.
 */
class CronConfigurationForm extends Form implements AdminAreaInterface, AjaxComponentInterface
{
    /** job => [setting key of its frequency (null: runs with every start), choices] */
    const JOBS = [
        'ProductPricingUpdate'      => ['cron1', ['1d', '1w', '1mo', 'never']],
        'SSLCertificatesProcessing' => ['cron5', ['5m', '10m', '30m', '1h', '3h', 'never']],
        'SSLCertificates'           => ['cron2', ['1d', '12h', '1w', 'never']],
        'RenewalNotifyCertificate'  => ['cron3', ['1d', 'never']],
        'SSLAutoInstall'            => [null, []],
    ];

    const LABELS = [
        '5m' => 'every 5 minutes', '10m' => 'every 10 minutes', '30m' => 'every 30 minutes', '1h' => 'every hour', '3h' => 'every 3 hours',
        '12h' => 'every 12 hours', '1d' => 'every day', '1w' => 'every week', '1mo' => 'every month', 'never' => 'never',
    ];

    protected $configuration;

    public function __construct($configuration = false)
    {
        parent::__construct();

        $this->configuration  = $configuration;
        $this->provider       = CronSettingsProvider::class;
        $this->providerAction = CronSettingsProvider::ACTION_UPDATE;
    }

    public function loadHtml(): void
    {
        $this->builder = BuilderCreator::oneColumn($this);
        $this->setId('CronConfigurationForm');

        $status = CronStatus::rows();
        $script = dirname(__DIR__, 6) . '/cron/cron.php';

        foreach(self::JOBS as $job => [$key, $choices])
        {
            $block = new Widget();
            $block->setCss('tssggs-cronjob');
            $this->addElement($block);

            $row     = isset($status[$job]) ? $status[$job] : ['last' => null, 'error' => ''];
            $failing = $row['error'] !== '';
            $badge   = $row['last'] === null
                ? '<span class="tssggs-badge tssggs-badge--idle">' . $this->translate('neverRunBadge') . '</span>'
                : ($failing
                    ? '<span class="tssggs-badge tssggs-badge--bad">' . $this->translate('failing') . '</span>'
                    : '<span class="tssggs-badge tssggs-badge--ok">' . $this->translate('healthy') . '</span>');
            $last    = $row['last'] === null ? $this->translate('neverRun') : htmlspecialchars($row['last']);

            $html = '<div class="tssggs-cronjob__head"><h3>' . $this->translate('name_' . $job) . '</h3>' . $badge . '</div>'
                . '<p class="tssggs-cronjob__about">' . $this->translate('about_' . $job) . '</p>'
                . ($failing ? '<div class="tssggs-cronjob__fail">' . $this->translate('lastRun') . ' ' . $last . ' &mdash; ' . htmlspecialchars($row['error']) . ' (' . $this->translate('seeLog') . ')</div>' : '')
                . '<hr class="tssggs-cronjob__sep">'
                . '<div class="tssggs-cronjob__freq"><i class="mdi mdi-clock-outline"></i><span>' . $this->translate('howOften') . '</span>'
                . ($key === null ? ' <b>' . $this->translate('everyStart') . '</b>' : '') . '</div>';
            $block->addElement((new Text())->setText($html));

            if($key !== null)
            {
                $options = [];
                foreach($choices as $choice)
                {
                    $options[$choice] = self::LABELS[$choice];
                }
                $field = new Dropdown();
                $field->required();
                $field->setDefaultValueAsFirstOption();
                $field->setName($key);
                $field->setOptions($options);
                $this->builder->addFieldInContainer($block, $field);
            }

            $block->addElement((new Text())->setText(
                '<p class="tssggs-cronjob__meta">' . $this->translate('lastRun') . ' ' . $last . '<br>'
                . $this->translate('runNow') . ' <code>php -q ' . $script . ' ' . $job . ' --force</code></p>'
            ));
        }

        $toolbar = new Toolbar();
        $toolbar->addElement(
            (new ButtonSuccess())
                ->setId('buttonSave')
                ->setTitle($this->translate('save'))
                ->onClick(new FormSubmit($this))
        );

        $this->addElement($toolbar);
    }
}
