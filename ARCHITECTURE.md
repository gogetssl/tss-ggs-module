# Architecture and development guide

The unified WHMCS module, built as two editions from this one code base: **TheSSLStore Plugin** (`tss`) and the
**GoGetSSL Module** (`ggs`). Source folder names use `TSSGGSModule`; `build/build.js` renames them per edition
(`TSSPlugin`, `GGSModule`), replaces `%%VENDOR%%` in texts and writes `app/Config/edition.php`.

## Layout

```
modules/addons/TSSGGSModule/          addon module (admin pages, settings, crons, gateway, business logic)
  app/
    Libs/                             business logic, one folder per domain:
      Gateway/                        API client (SSLTrustCenterApi + Endpoints/ per product line), GatewayException
      Certificates/                   TLS certificate lifecycle (CertificateService + Concerns/), sync, CAA, SANs, keys
      Billing/                        prices, margins, markups, renewal policy, balance, upgrades, invoices
      Products/                       catalogue sync, product settings and configurable options
      ProductLines/                   S/MIME, code / document signing, LEI, PCI, CodeGuard (GatewayOrderService)
      Subscriptions/                  ACME (Automated SSL) and AIS / Plan + Automate
      AutoInstall/                    cPanel AutoInstall (AutoInstallHelpers + Concerns/), queue, report
      Emails/                         e-mail catalogue and manager
      Support/                        edition, settings, logging, texts, icons, helpers
      Update/                         one-click module update
    Database/Schema.php               every database change after schema.sql, as numbered steps (see below)
    Cron/                             console commands (cron/cron.php <Command>), RunAll starts every job when due
    Kernel/                           the module's own runtime: boot, admin pages, hooks, cron runner (see below)
    Pages/Admin/                      the admin pages, one class per page
    Hooks/                            the addon's WHMCS hooks, one file per hook point
    Models/                           the module's tables (extend Models/Model); Models/Whmcs wraps WHMCS's own models
  TSSGGSModule.php                    the functions WHMCS calls (config, activate, upgrade, output, clientarea)
  hooks.php, cron/cron.php            the other two entry points; all three load app/Kernel/Boot.php first
  resources/templates/                Smarty templates of the admin pages
  resources/emails/*.html             default e-mail texts, subject in the first line
modules/servers/TSSGGSModule/         server (provisioning) module: WHMCS product actions and the client area
  TSSGGSModule.php                    entry points WHMCS calls (CreateAccount, ClientArea, ...)
  hooks.php                           registers the WHMCS hooks; each hook is a class in app/hooks
  app/controllers/server/clientarea/  client area: one trait per product line in parts/
  app/services/                       provisioning steps (SSLStepOne/Two/Three, Renew, ...)
tests/                                Unit/ (PHPUnit, no WHMCS), Smoke/ (run inside a WHMCS install), stubs/
tools/qa.sh                           runs the checks (below)
build/                                release build (build.js), signing and publishing
```

Classes in `Libs` are mostly static services. A large one keeps its public methods in one class and groups the
bodies by responsibility in traits under `Concerns/` (e.g. `CertificateService` + `Concerns/CertificateReissue`), so
callers always use the class name.

## Runtime (no framework, no Composer)

The module has no third-party PHP code of its own: Eloquent, Symfony Console and Smarty are the ones WHMCS ships.

- `app/Kernel/Boot.php` registers the autoloader: `TSSGGSModule\App\X\Y` is `app/X/Y.php`. It does
  nothing else. The server module has its own loader for its own namespace and reaches `App\...` through this one.
- `app/Kernel/Lifecycle.php`: what WHMCS asks an addon for - description, activate (e-mail templates, tables),
  deactivate (keeps everything), upgrade (tables). Tables: `app/Database/schema.sql` (run by `Database/SqlFile`), then
  the numbered changes in `Database/Schema.php`.
- `app/Kernel/Hooks.php` loads `app/Hooks/*.php`; the file name is the hook point, the file calls
  `$hookManager->register(function($vars) { ... }, $priority)`.
- `app/Kernel/Console/`: `Command` is the base of a cron job (one run at a time, `--force`), `Runner` finds the jobs
  in `app/Cron`.
- Logging: `App\Libs\Logs\Logger::info($message, $data)` (table `<prefix>_Logs`, admin page Logs). Small settings
  kept one per row: `App\Libs\Support\ModuleSettings`. WHMCS's API: `App\Libs\Support\WhmcsApi::run()`.
- A text outside a page (a hook answering with an error): `App\Kernel\Lang::text($key)`.
- HTML built in PHP (notices, hints): `App\Libs\Support\Html`.

## Admin pages: Smarty + htmx + Alpine.js

```
app/Kernel/                 Kernel (dispatch), Request, Response, Page (base), View (Smarty), Layout, Lang
app/Pages/Admin/<Page>.php  one class per mod-page; public methods are the actions
resources/templates/        Smarty templates: admin/layout.tpl, admin/partials, admin/<page>/*.tpl
overrides/templates/        a reseller's copy of a template at the same path wins; updates never touch it
resources/assets/js/vendor  htmx.js, alpine.js (readable, shipped with the module, no CDN, no build step)
resources/assets/js/app.js  the module's own script (token header for htmx, loading state)
resources/assets/css        the module's own stylesheet: ui-base.css, ui-controls.css, ui-layout.css (classes mod-*,
                            everything under #mod-root), then one file per area; resources/build/css.php lists them
```

- A page is a class in `app/Pages/Admin` (`mod-page=certificateLog` is `CertificateLog`); a page that does not exist
  answers "This page does not exist" inside the layout.
- Actions: `GET mod-action=x` calls `x()`, `POST mod-action=x` calls `postX()` after the WHMCS token check. Anything
  that changes data is a POST action.
- An action returns `$this->view(template, data)` (full page in the layout), `$this->fragment(...)` (a part, for
  htmx) or `$this->redirect(...)`. For an htmx request (`$this->request->isHtmx()`) answer with the fragment.
- Templates escape every `{$variable}`; ready HTML needs `{$variable nofilter}`. Helpers: `{t key=...}`,
  `{icon name=...}`, `{url action=... page=...}`.
- Behaviour goes into the template as attributes (`hx-get`, `hx-post`, `hx-target`, `x-data`, `@click`), not into
  new JavaScript files. No inline styles: classes in `theme.css`.
- Read input through `Request` (`int`, `string`, `oneOf`), never `$_GET` / `$_POST` directly.
- **No `>` inside a `<form ...>` tag** (no arrow function in `@submit` / `x-init`): WHMCS adds its token field after
  the first `>` it finds and cuts the tag in two. `KernelTest` checks every template.
- A question before a change: `data-confirm="..."` on the form or the button (`app.js`), `hx-confirm` on an htmx
  element. A slow part of a page: `{include file='admin/partials/lazy.tpl' url=...}` (spinner, then the fragment).
- Shared partials in `admin/partials`: `flash.tpl` (message of the last action), `alert.tpl`, `lazy.tpl`,
  `menu_item.tpl`. A script that only one page needs: `resources/assets/js/pages/<page>.js`, loaded by its template.
- Pages in `Kernel::SETUP_FIRST` lead to the setup wizard until the vendor API is connected.
- A page whose action replaces the module's files or sends a file (module update, CSV export) uses plain form
  posts and redirects, not htmx.
- **Setup wizard** (`app/Pages/Admin/Configuration.php`): one action per page (`step1` ... `step7`), their order in
  `app/Libs/Setup/Wizard.php`; `admin/configuration/wizard.tpl` is the frame, `stepN.tpl` the page. `Page::CHROME =
  false`: no module header and menu. Step 4 uses the API settings provider, step 5 loads the Products list, step 7
  shows the blocks of the Migration page.
- **Migration** (`app/Pages/Admin/Migration.php`): what it shows is built by `app/Libs/Setup/MigrationScreen.php`;
  the runner (`assets/js/pages/migration.js`, Alpine) posts `batch` until the run is finished (`Response::json`).
- `{t key=... html=true}` prints a text that holds its own HTML (`<b>`); everything else is escaped.
- **Settings** (`app/Pages/Admin/Settings.php`): one entry per screen in `SCREENS` - a provider from
  `app/Libs/Settings` (`read()` gives values and choices, `update()` saves and may return a `Result`), a template
  `admin/settings/<name>.tpl` built from `section_start.tpl` / `field.tpl` / `section_end.tpl`, optional `extra`
  class (`data($lang)`: what the screen shows besides its fields). A screen without a provider has no form and no
  Save bar (Email notifications). `test` adds Test Connection (the provider's `test()`), `always` keeps Save enabled.
- **Lists** (`Kernel\Table`): `searchUsing()` for a search of the page's own (Logs: by service), `toolbar()`,
  `selectable()` for ticked rows. A dialog is a fragment put into `#tssggs-dialog` (`partials/dialog_start.tpl`).
- **Deploy to the test box builds from the last commit** (`build/build.js HEAD`): commit before `deploypkg.sh`,
  or the box still runs the old code while `qa.sh` (which copies the working tree) already sees the new one.

## Rules that keep it maintainable

- **Gateway errors**: catch `Gateway\GatewayException` and compare `getGatewayCode()` / `getHttpCode()` /
  `says()`, never the readable message (it is translated and reworded).
- **Database changes**: add a numbered step to `App\Database\Schema::STEPS` (idempotent), never a lazy
  `hasTable` / `hasColumn` check in feature code. Released steps are never edited or renumbered.
- **HTML in PHP**: escape with `Html::e()`, notices with `Html::notice()` / `Html::hint()`; e-mail texts belong in
  `resources/emails`.
- **Paths**: no `dirname(__DIR__, n)` into other folders where a constant exists (`ModuleConstants::getFullPath`);
  moving a file changes `__DIR__`.
- **Texts**: admin texts in `modules/addons/TSSGGSModule/langs`, client texts in `modules/servers/TSSGGSModule/langs`
  (all languages); vendor names through `%%VENDOR%%`, never the gateway.
- **Client data**: never in code, comments, commits or tests (use `domain.tld`).

## Checks

No PHP is needed on the developer machine: `tools/qa.sh` runs the tools on a Linux host with PHP 8.2+ and a WHMCS
install (PHPUnit, PHPStan and PHP-CS-Fixer phars in `~/tools` there).

```
QA_HOST=user@host QA_WHMCS=/path/to/whmcs QA_MODULE=TSSPlugin tools/qa.sh all     # unit + stan + cs
tools/qa.sh fix       # apply the code style
tools/qa.sh smoke     # inside the WHMCS install where the build is deployed: admin pages, client area, gateway, schema
```

- **PHPStan** level 2 on `app/` of both modules; issues that existed when it was introduced are in
  `phpstan-baseline.neon` - new code must not add any. Raise the level step by step.
- **Unit tests** cover the rules that need no database (gateway errors, billing, domains, logging, e-mails, edition).
  Add a test with every bug fix that can be tested without WHMCS.
- **Smoke tests** build every admin page, the client area page of a service per category and state, call the
  gateway read-only and check the database schema.
