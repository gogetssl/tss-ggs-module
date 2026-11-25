<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Layout;
use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Libs\Settings\ApiSettingsProvider;
use TSSGGSModule\App\Libs\Settings\Result;
use TSSGGSModule\App\Libs\Setup\MigrationScreen;
use TSSGGSModule\App\Libs\Setup\Wizard;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Libs\Support\SystemRequirements;
use TSSGGSModule\App\Migration\Choice;
use TSSGGSModule\App\Migration\Credentials;
use TSSGGSModule\App\Migration\Detector;
use TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;

/**
 * The setup wizard ("Module Installation"): welcome, system check, legacy modules found (only when there is one),
 * API connection, products, the migration (only when it was chosen) and the finalization. One action per page
 * (step1 ... step7); their order is App\Libs\Setup\Wizard. Templates: resources/templates/admin/configuration -
 * wizard.tpl is the frame (header with the progress, the list of steps), stepN.tpl the content of a page.
 * Once the wizard is finalized it leads to the dashboard.
 */
class Configuration extends Page
{
    const LANG       = 'admin.configuration.content';
    const HEADING    = false;
    const CHROME     = false;

    const WIZARD = 'instalation.wizard.';

    public function index()
    {
        return $this->redirect('step1');
    }

    public function step1()
    {
        return $this->step(1, [
            'intro'  => $this->t('step1.first_p', ['moduleName' => Edition::name()]),
            'next'   => $this->url('step2'),
        ]);
    }

    /** The system compatibility check (App\Libs\Support\SystemRequirements) */
    public function step2()
    {
        $checks = (new SystemRequirements())->check();
        $rows   = [];
        foreach($checks as $check)
        {
            $replace = ['subject' => $check['subject']];
            $passed  = $check['status'] === SystemRequirements::STATUS_PASSED;
            $rows[]  = [
                'status'      => $check['status'],
                'name'        => $this->t('step2.check_' . $check['key'], $replace),
                'hint'        => $passed ? '' : $this->t('step2.hint_' . $check['key'], $replace),
                'command'     => !$passed && !empty($check['command']) ? (string)$check['command'] : '',
                'required'    => (string)$check['required'],
                'found'       => (string)$check['found'],
                'statusText'  => $this->t('step2.status_' . $check['status']),
            ];
        }

        return $this->step(2, [
            'checks'  => $rows,
            'blocked' => SystemRequirements::hasBlockingFailure($checks),
            'next'    => $this->url('step' . Wizard::nextPage(2)),
        ]);
    }

    /**
     * Legacy modules found: per module the decision Migrate / Install standalone. Without a legacy module the
     * wizard has no such page: the vendor is fixed by the edition.
     */
    public function step3()
    {
        if(!Wizard::hasLegacy())
        {
            (new AddonModuleRepository())->saveVendor([Edition::get()]);

            return $this->redirect('step4');
        }

        $found   = Detector::detect();
        $names   = array_map(function($info) {
            return $info['source'] === 'ggs' ? 'GoGetSSL' : 'TheSSLStore';
        }, $found);
        $modules = [];
        foreach($found as $source => $info)
        {
            $c         = $info['counts'];
            $band      = (string)$info['support']['band'];
            $supported = (bool)$info['support']['supported'];
            $modules[] = [
                'source'    => $source,
                'label'     => (string)$info['label'],
                'version'   => $info['version'] ?: $this->t('step3.version_unknown'),
                'band'      => $band === 'full' ? 'passed' : ($band === 'partial' ? 'warning' : 'failed'),
                'bandText'  => $this->t('step3.support_' . $band),
                'facts'     => [
                    $this->t('step3.fact_products', ['n' => (int)$c['products']['total']]),
                    $this->t('step3.fact_services', ['active' => (int)($c['services']['Active'] ?? 0), 'total' => array_sum((array)$c['services'])]),
                    $this->t('step3.fact_orders', ['n' => array_sum((array)$c['orders']), 'unconfigured' => (int)$c['unconfigured'], 'unpaid' => (int)$c['unpaid']]),
                    $this->t('step3.fact_subscriptions', ['acme' => (int)$c['acme'], 'ais' => (int)$c['ais'], 'autoinstall' => (int)$c['autoinstall']]),
                ],
                'reason'    => (string)$info['support']['reason'],
                'supported' => $supported,
                // what is chosen now: the saved choice, else Migrate where it is possible
                'choice'    => Choice::get($source) ?: ($supported ? Choice::MIGRATE : Choice::SKIP),
                'choiceLabel' => $this->lang->get('admin.configuration.shared.forms.legacy_choice_form.choice_' . $source),
                'choiceHint'  => $this->lang->get('admin.configuration.shared.forms.legacy_choice_form.choice_' . $source . '_description'),
            ];
        }

        return $this->step(3, [
            'intro'   => $this->t(count($names) > 1 ? 'step3.intro_many' : 'step3.intro_one', ['modules' => implode(' ' . $this->t('step3.and') . ' ', $names)]),
            'modules' => $modules,
            'migrate' => Choice::MIGRATE,
            'skip'    => Choice::SKIP,
        ]);
    }

    /**
     * POST choice_<source>: saves Migrate / Install standalone per legacy module. The vendors to configure on the
     * next page are the edition's own plus every legacy module chosen for migration.
     */
    public function postChoice()
    {
        $vendors = [Edition::get()];
        foreach(array_keys(Detector::detect()) as $source)
        {
            $choice = $this->request->string('choice_' . $source) === Choice::MIGRATE ? Choice::MIGRATE : Choice::SKIP;
            Choice::set($source, $choice);
            if($choice === Choice::MIGRATE)
            {
                $vendors[] = $source;
            }
        }
        (new AddonModuleRepository())->saveVendor(array_values(array_unique($vendors)));

        return $this->redirect('step4');
    }

    /** The API connection: the account of the edition's own vendor (the same fields as Settings > API) */
    public function step4()
    {
        $provider = new ApiSettingsProvider([], $this->lang);
        $provider->read();
        [, $filled] = Credentials::prefilled();
        $stored     = (new AddonModuleRepository())->storedCredentials();
        $vendor     = Edition::primaryVendor();

        return $this->step(4, [
            'vendor'    => $vendor,
            'data'      => $provider->values(),
            'options'   => $provider->options(),
            'typed'     => [],
            'f'         => 'admin.settings.index.forms.api_settings_form',
            'switchOn'  => 'on',
            'switchOff' => 'off',
            'hasLive'   => trim((string)($stored[$vendor]['AuthToken'] ?? '')) !== '',
            'hasTest'   => trim((string)($stored[$vendor]['TestAuthToken'] ?? '')) !== '',
            // credentials copied from a legacy module chosen for migration (nothing is stored until saved)
            'prefilled' => $filled ? $this->t('step4.credentials_prefilled', ['modules' => implode(', ', array_map(function($source) {
                return $source === 'ggs' ? 'GoGetSSL' : 'TheSSLStore';
            }, $filled))]) : '',
            'flash'     => $this->takeFlash(),
            'saveUrl'   => $this->url('api'),
            'testUrl'   => $this->url('test'),
        ]);
    }

    /** POST the credentials: tests the connection, saves and goes on to the products; a failure is shown on the page */
    public function postApi()
    {
        $result = $this->api(function(ApiSettingsProvider $provider) {
            return $provider->update();
        });
        if(!$result->isError())
        {
            return $this->redirect('step5');
        }

        return $this->apiResult($result);
    }

    /** POST the credentials: tests the connection only */
    public function postTest()
    {
        return $this->apiResult($this->api(function(ApiSettingsProvider $provider) {
            return $provider->test();
        }));
    }

    /** The products of the vendor to import (the list of the Products page, loaded into this page) */
    public function step5()
    {
        return $this->step(5, [
            'next'        => $this->url('step' . Wizard::nextPage(5)),
        ]);
    }

    public function step6()
    {
        return $this->step(6, [
            'pending'  => Wizard::migrationPlanned() && !Wizard::migrationCompleted(),
            'products' => sprintf($this->t('step6.step6_products'), (new ProductRepository())->countProducts()),
        ]);
    }

    /** POST: the wizard is done; on to the Migration page while this edition's legacy module is still to migrate, else the dashboard */
    public function postFinalize()
    {
        (new AddonModuleRepository())->finalize();

        return Response::redirect(self::pageUrl(Wizard::migrationPlanned() && !Wizard::migrationCompleted() ? 'migration' : 'home'));
    }

    /** The migration of the legacy module (only when it was chosen on page 3): the blocks of the Migration page */
    public function step7()
    {
        if(!Wizard::migrationPlanned())
        {
            return $this->redirect('step6');
        }

        return $this->step(7, (new MigrationScreen($this->lang, true))->data());
    }

    /* ---- helpers ---- */

    /** A page of the wizard in its frame; a finalized wizard leads to the dashboard */
    protected function step(int $page, array $data): Response
    {
        if($this->finalized())
        {
            return Response::redirect(self::pageUrl('home'));
        }

        $pages = Wizard::pages();
        $pos   = Wizard::position($page);
        $steps = [];
        foreach($pages as $i => $number)
        {
            $steps[] = [
                'state' => $i < $pos ? 'done' : ($i === $pos ? 'active' : 'todo'),
                'mark'  => $i + 1,
                'title' => $this->lang->get(self::WIZARD . 'step' . $number . '_name'),
                'desc'  => $this->lang->get(self::WIZARD . 'step' . $number . '_desc'),
                // finished steps link back to their page
                'url'   => $i < $pos ? $this->url('step' . $number) : '',
            ];
        }

        return $this->view('admin/configuration/wizard', $data + [
            'page'      => $page,
            'steps'     => $steps,
            'percent'   => Wizard::percent($page),
            'stepOf'    => sprintf($this->lang->get(self::WIZARD . 'step_of'), $pos + 1, count($pages)),
            // "STEP 2. System Compatibility Check" -> "System Compatibility Check" (the number is in the line above)
            'stepTitle' => (string)preg_replace('/^\s*STEP\s+\d+\s*[.\-:]\s*/i', '', $this->t('step' . $page . '.step' . $page . '_title')),
            // the heading counts as the wizard does (page 7 is its sixth step when the migration is part of it)
            'crumb'     => $this->lang->get('admin.breadcrumbs.Configuration_step' . ($pos + 1)),
            'module'    => Edition::name(),
            'moduleVersion' => Layout::version(),
        ]);
    }

    protected function finalized(): bool
    {
        $configuration = (new AddonModuleRepository())->getModuleConfiguration();

        return ($configuration['test_connection'] ?? '') === 'success';
    }

    /** Runs the API settings provider with what was posted */
    protected function api(callable $run): Result
    {
        try
        {
            // the wizard's page has the fields of the edition's own account only
            $result = $run((new ApiSettingsProvider($this->request->posted(), $this->lang))->ownVendorOnly());

            return $result instanceof Result ? $result : (new Result())->setSuccess('');
        }
        catch(\Throwable $e)
        {
            return (new Result())->setError(htmlspecialchars($e->getMessage()));
        }
    }

    /** What the connection test or a failed save said: into the page's result box (htmx), or on the page again */
    protected function apiResult(Result $result): Response
    {
        $flash = ['text' => $result->message(), 'tone' => $result->isError() ? 'bad' : 'ok'];
        if($this->request->isHtmx())
        {
            return $this->fragment('admin/settings/test_result', ['flash' => $flash]);
        }
        $this->flash($flash['text'], $flash['tone']);

        return $this->redirect('step4');
    }
}
