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
    UI/Admin/...                      admin pages (ModulesGarden framework: Widgets, Forms, Providers, Containers)
    UI/Shared/Html.php                escaping, notices, hints - use it instead of writing the markup again
    Http/, Hooks/, Models/            framework controllers, addon hooks, Eloquent models
  resources/emails/*.html             default e-mail texts, subject in the first line
  core/, components/, packages/       bundled ModulesGarden framework - not ours, avoid changing it
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

## Own kernel: Smarty + htmx + Alpine.js (replaces the ModulesGarden framework page by page)

```
app/Kernel/                 Kernel (dispatch), Request, Response, Page (base), View (Smarty), Layout, Lang
app/Pages/Admin/<Page>.php  one class per mg-page; public methods are the actions
resources/templates/        Smarty templates: admin/layout.tpl, admin/partials, admin/<page>/*.tpl
overrides/templates/        a reseller's copy of a template at the same path wins; updates never touch it
resources/assets/js/vendor  htmx.js, alpine.js (readable, shipped with the module, no CDN, no build step)
resources/assets/js/app.js  the module's own script (token header for htmx, loading state)
```

- A page with a class in `app/Pages/Admin` is served by the kernel (`Configuration/Addon/base.php`); every other
  page still runs on the old framework. To move a page: write the class and templates, delete its
  `app/Http/Admin/<Page>.php` and `app/UI/Admin/<Page>`, run `tools/qa.sh all` and `smoke`.
- Actions: `GET mg-action=x` calls `x()`, `POST mg-action=x` calls `postX()` after the WHMCS token check. Anything
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
- **Every admin page is on the kernel.** The old framework (`core`, `components`, `packages`) still serves the
  client area and the server module (`app/Http/Client`, `app/UI/Client`, the hooks); it can go once those are moved.
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
