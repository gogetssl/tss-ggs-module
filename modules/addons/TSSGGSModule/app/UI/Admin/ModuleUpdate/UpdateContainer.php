<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\ModuleUpdate;

use ModulesGarden\TSSGGSModule\App\Http\Admin\ModuleUpdate;
use ModulesGarden\TSSGGSModule\App\Libs\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Installer;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Preflight;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Release;
use ModulesGarden\TSSGGSModule\App\Libs\Update\State;
use ModulesGarden\TSSGGSModule\App\Libs\Update\Workdir;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

/**
 * Module update page: the installed and the newest version, its notes, the server checks, Install (or why not, with
 * the download link), the progress of a running install, rollback and the update history.
 * Styles in resources/assets/css/theme.css (.tssggs-upd).
 */
class UpdateContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $session = $_SESSION[ModuleUpdate::SESSION] ?? [];
        unset($_SESSION[ModuleUpdate::SESSION]);

        $state     = State::get();
        $installed = State::installedVersion();
        $available = Release::available($state);
        $token     = function_exists('generate_token') ? \generate_token('plain') : '';
        $url       = function($action) { return 'addonmodules.php?module=TSSGGSModule&amp;mg-page=moduleUpdate&amp;mg-action=' . $action; };
        $form      = function($action, $label, $class, array $fields = [], $confirm = '') use ($url, $token) {
            $hidden = '<input type="hidden" name="token" value="' . Card::e($token) . '">';
            foreach($fields as $name => $value)
            {
                $hidden .= '<input type="hidden" name="' . Card::e($name) . '" value="' . Card::e($value) . '">';
            }

            return '<form method="post" action="' . $url($action) . '" class="tssggs-upd__form"' . ($confirm !== '' ? ' onsubmit="return confirm(' . Card::e(json_encode($confirm)) . ')"' : '') . '>'
                . $hidden . '<button type="submit" class="tssggs-upd__btn ' . $class . '">' . Card::e($label) . '</button></form>';
        };

        $html = '<div class="tssggs-upd"><h2>' . $this->t('title') . '</h2>';

        // result of the last step
        if(!empty($session['error']))
        {
            $html .= '<div class="tssggs-upd__msg is-error">' . Card::e($session['error']) . '</div>';
        }
        elseif(!empty($session['notice']))
        {
            $html .= '<div class="tssggs-upd__msg is-ok">' . $this->t('notice_' . $session['notice'], [':version' => (string)($session['detail'] ?? '')]) . '</div>';
        }

        // an install in progress: the next step is posted by the page itself
        if(!empty($session['next']))
        {
            $html .= '<div class="tssggs-upd__card"><h3>' . $this->t('installing', [':version' => (string)($state['version'] ?? '')]) . '</h3><ol class="tssggs-upd__steps">';
            foreach(ModuleUpdate::STEPS as $step)
            {
                $done  = array_search($step, ModuleUpdate::STEPS, true) <= array_search($session['done'], ModuleUpdate::STEPS, true);
                $html .= '<li class="' . ($done ? 'is-done' : ($step === $session['next'] ? 'is-running' : '')) . '">' . $this->t('step_' . $step) . '</li>';
            }
            $html .= '</ol><p>' . $this->t('installing_wait') . '</p>'
                . '<form id="tssggs-upd-next" method="post" action="' . $url('step') . '"><input type="hidden" name="token" value="' . Card::e($token) . '"><input type="hidden" name="step" value="' . Card::e($session['next']) . '"></form>'
                . '<script>setTimeout(function(){document.getElementById("tssggs-upd-next").submit();}, 300);</script></div></div>';
            $this->addElement(Card::html($html));

            return;
        }

        // versions
        $checked = !empty($state['checkedAt']) ? date('Y-m-d H:i', (int)$state['checkedAt']) : $this->t('never');
        $html   .= '<div class="tssggs-upd__card"><dl class="tssggs-upd__facts">'
            . '<dt>' . $this->t('installed') . '</dt><dd>' . Card::e($installed) . '</dd>'
            . '<dt>' . $this->t('newest') . '</dt><dd>' . Card::e($state['version'] ?? '-') . (!empty($state['page']) ? ' <a href="' . Card::e($state['page']) . '" target="_blank" rel="noopener">' . $this->t('release_page') . '</a>' : '') . '</dd>'
            . '<dt>' . $this->t('checked') . '</dt><dd>' . Card::e($checked) . (!empty($state['error']) ? ' <span class="tssggs-upd__muted">(' . Card::e($state['error']) . ')</span>' : '') . '</dd>'
            . '</dl>' . $form('check', $this->tRaw('check_now'), 'is-plain') . '</div>';

        if($available)
        {
            $checks = Preflight::run($state, true);
            $ok     = Preflight::passed($checks);
            $html  .= '<div class="tssggs-upd__card"><h3>' . $this->t('available', [':version' => (string)$state['version']]) . '</h3>';
            if(!empty($state['notes']))
            {
                $html .= '<div class="tssggs-upd__notes">' . Card::e($state['notes']) . '</div>';
            }
            $html .= '<h4>' . $this->t('checks') . '</h4><ul class="tssggs-upd__checks">';
            foreach($checks as $check)
            {
                $html .= '<li class="' . ($check['ok'] ? 'is-ok' : ($check['blocking'] ? 'is-fail' : 'is-warn')) . '"><b>' . $this->t('check_' . $check['key']) . '</b>'
                    . ($check['detail'] !== '' ? ' <span class="tssggs-upd__muted">' . Card::e($check['detail']) . '</span>' : '') . '</li>';
            }
            $html .= '</ul>';
            if($ok && ModuleUpdate::fullAdmin())
            {
                $html .= '<p>' . $this->t('install_info') . '</p>'
                    . $form('step', $this->tRaw('install', [':version' => (string)$state['version']]), 'is-primary', ['step' => 'prepare'], $this->tRaw('install_confirm', [':version' => (string)$state['version']]));
            }
            elseif($ok)
            {
                $html .= '<p>' . $this->t('full_admin_only') . '</p>';
            }
            else
            {
                $html .= '<p class="tssggs-upd__manual">' . $this->t('manual_only') . (!empty($state['page']) ? ' <a href="' . Card::e($state['page']) . '" target="_blank" rel="noopener">' . $this->t('download', [':version' => (string)$state['version']]) . '</a>' : '') . '</p>';
            }
            $html .= '</div>';
        }
        else
        {
            $html .= '<div class="tssggs-upd__card"><p>' . $this->t('up_to_date') . '</p></div>';
        }

        // rollback
        $backup = null;
        foreach(glob(Workdir::path('backups') . '/' . Edition::folder() . '-*.zip') ?: [] as $file)
        {
            $version = Installer::backupVersion($file);
            if($version && version_compare($version, $installed, '<') && (!$backup || filemtime($file) > filemtime($backup[0])))
            {
                $backup = [$file, $version];
            }
        }
        if($backup && ModuleUpdate::fullAdmin())
        {
            $html .= '<div class="tssggs-upd__card"><h4>' . $this->t('rollback_title') . '</h4>';
            $html .= (isset($state['rollbackSafe']) && !$state['rollbackSafe'])
                ? '<p>' . $this->t('rollback_unsafe') . '</p>'
                : '<p>' . $this->t('rollback_info', [':version' => $backup[1]]) . '</p>' . $form('rollback', $this->tRaw('rollback', [':version' => $backup[1]]), 'is-plain', [], $this->tRaw('rollback_confirm', [':version' => $backup[1]]));
            $html .= '</div>';
        }

        // history
        $history = State::get('moduleUpdateHistory');
        if($history)
        {
            $html .= '<div class="tssggs-upd__card"><h4>' . $this->t('history') . '</h4><table class="tssggs-upd__history"><tr><th>' . $this->t('h_date') . '</th><th>' . $this->t('h_change') . '</th><th>' . $this->t('h_result') . '</th></tr>';
            foreach($history as $entry)
            {
                $html .= '<tr><td>' . Card::e($entry['at'] ?? '') . '</td><td>' . Card::e(ucfirst((string)($entry['kind'] ?? '')) . ' ' . ($entry['from'] ?? '') . ' > ' . ($entry['to'] ?? '')) . '</td><td>'
                    . Card::e(($entry['result'] ?? '') . (!empty($entry['error']) ? ': ' . $entry['error'] : '')) . '</td></tr>';
            }
            $html .= '</table></div>';
        }

        $html .= '<p class="tssggs-upd__muted">' . $this->t('recover_hint', [':path' => Workdir::path('recover.php')]) . '</p></div>';
        $this->addElement(Card::html($html));
    }

    protected function tRaw($key, array $vars = [])
    {
        return (string)$this->translate($key, $vars, ['admin.module_update']);
    }

    protected function t($key, array $vars = [])
    {
        return Card::e($this->tRaw($key, $vars));
    }
}
