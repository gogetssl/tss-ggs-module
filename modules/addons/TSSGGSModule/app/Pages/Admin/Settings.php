<?php

namespace ModulesGarden\TSSGGSModule\App\Pages\Admin;

use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Libs\Settings\GeneralSettingsProvider;
use ModulesGarden\TSSGGSModule\App\Libs\Settings\ProductsSettingsProvider;
use ModulesGarden\TSSGGSModule\App\Libs\Settings\Result;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * Admin page "Settings": a menu of screens on the left, the chosen screen (settings-page=...) on the right - one
 * form with sections and a sticky Save bar. A screen's values and choices come from its provider (App\Libs\Settings),
 * which also saves them; its fields are in its own template (resources/templates/admin/settings/<template>.tpl).
 *
 * The screens are being moved one by one: SCREENS lists the ones the kernel serves, the others are still shown by
 * the old framework (serves()).
 */
class Settings extends Page
{
    const LANG       = 'admin.settings.index';
    const BODY_CLASS = 'tssggs-settings';

    /** the menu: screen => icon, in this order; the first is the default */
    const MENU = [
        'apiSettings'       => 'api',
        'generalSettings'   => 'web',
        'sslSettings'       => 'shield-check-outline',
        'automatedSettings' => 'sync',
        'productsSettings'  => 'package-variant-closed',
        'financeSettings'   => 'cash-multiple',
        'emailSettings'     => 'bell-outline',
        'cronSettings'      => 'file-clock-outline',
        'logsSettings'      => 'text-box-outline',
    ];

    /**
     * the screens on the kernel: provider (values, choices, save), template, and the groups of its texts -
     * "form" for the fields and sections, "widget" for the title and subtitle of the screen
     */
    const SCREENS = [
        'generalSettings'  => ['provider' => GeneralSettingsProvider::class, 'template' => 'general', 'form' => 'general_settings_form', 'widget' => 'general_settings_widget'],
        'productsSettings' => ['provider' => ProductsSettingsProvider::class, 'template' => 'products', 'form' => 'products_settings_form', 'widget' => 'products_settings_widget'],
        'logsSettings'     => ['provider' => GeneralSettingsProvider::class, 'template' => 'logs', 'form' => 'logs_settings_form', 'widget' => 'logs_settings_widget'],
    ];

    /** Whether the kernel shows the screen this request asks for */
    public static function serves(array $query): bool
    {
        return isset(self::SCREENS[(string)($query['settings-page'] ?? '')]);
    }

    public function index()
    {
        $screen = $this->screen();

        return $this->view('admin/settings/index', $this->screenData($screen, null) + ['menu' => $this->menu($screen)]);
    }

    /** POST the screen's fields: saves them and shows the screen again with the result */
    public function postSave()
    {
        $screen   = $this->screen();
        $class    = self::SCREENS[$screen]['provider'];
        $provider = new $class($this->request->posted(), $this->lang);
        try
        {
            $result = $provider->update();
            $okKey  = 'forms.' . self::SCREENS[$screen]['form'] . '.update_success';
            $result = $result instanceof Result ? $result : (new Result())->setSuccess($this->lang->has(static::LANG . '.' . $okKey) ? $this->t($okKey) : $this->lang->get('admin.kernel.saved'));
        }
        catch(\Throwable $e)
        {
            $result = (new Result())->setError($e->getMessage());
        }

        if(!$this->request->isHtmx())
        {
            $this->flash($result->message(), $result->isError() ? 'bad' : 'ok');

            return $this->redirect('index', ['settings-page' => $screen]);
        }

        return $this->fragment('admin/settings/screen', $this->screenData($screen, $result));
    }

    protected function screen(): string
    {
        return $this->request->oneOf('settings-page', array_keys(self::SCREENS), (string)array_key_first(self::SCREENS));
    }

    /** @return array[] the menu on the left */
    protected function menu(string $current): array
    {
        $menu = [];
        foreach(self::MENU as $name => $icon)
        {
            $menu[] = ['title' => $this->t('widgets.sidebar.' . $name), 'icon' => $icon, 'active' => $name === $current,
                       'url'   => self::pageUrl('settings', 'index', ['settings-page' => $name])];
        }

        return $menu;
    }

    /** Everything the screen's templates need; $result: what a save just said (null when the screen is only shown) */
    protected function screenData(string $screen, ?Result $result): array
    {
        $config   = self::SCREENS[$screen];
        $class    = $config['provider'];
        $provider = new $class([], $this->lang);
        $provider->read();
        $flash    = $result ? ['text' => $result->message(), 'tone' => $result->isError() ? 'bad' : 'ok'] : $this->takeFlash();

        return [
            'screen'   => $screen,
            'template' => $config['template'],
            // groups of the texts: {t key="{$f}.clientSummary"}
            'f'        => 'forms.' . $config['form'],
            'w'        => 'widgets.' . $config['widget'],
            'icon'     => self::MENU[$screen],
            'data'     => $provider->values(),
            // after a failed save the screen shows what was typed
            'typed'    => $result && $result->isError() ? $this->request->posted() : [],
            'options'  => $provider->options(),
            'flash'    => $flash,
            'saved'    => $result !== null && !$result->isError(),
            'saveUrl'  => self::pageUrl('settings', 'save', ['settings-page' => $screen]),
            'vendors'  => Edition::vendors(),
            'wiki'     => Edition::wiki(),
            'hasOpenCorporatesToken' => (string)GeneralSettings::get('openCorporatesToken') !== '',
        ];
    }
}
