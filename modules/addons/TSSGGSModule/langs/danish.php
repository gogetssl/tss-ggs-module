<?php

// main menu
$_LANG['admin.menu.home']       = 'Dashboard';
$_LANG['admin.menu.products']   = 'Produkter';
$_LANG['admin.menu.import']     = 'Import';
$_LANG['admin.menu.statistics'] = 'Statistik';
$_LANG['admin.menu.reports']    = 'Rapporter';
$_LANG['admin.menu.pricing']    = 'Priser';
$_LANG['admin.menu.settings']   = 'Indstillinger';
$_LANG['admin.menu.automationQueue'] = 'Automatiseringskø';
$_LANG['admin.menu.migration']       = 'Migrering';
$_LANG['admin.menu.more'] = 'Mere';
$_LANG['admin.breadcrumbs.Migration_index'] = 'Migrering';

$_LANG['admin.home.index.home_container.title']                 = 'Dashboard';
$_LANG['admin.home.index.home_container.period']                = 'Sidste måned';
$_LANG['admin.home.index.home_container.attention_title']       = 'Automatiseringen kræver opmærksomhed';
$_LANG['admin.home.index.home_container.attention_body']        = 'Cron-job kører ikke. Certifikatbehandling og prisopdateringer kan blive påvirket.';
$_LANG['admin.home.index.home_container.attention_button']      = 'Gennemgå system';
$_LANG['admin.home.index.home_container.kpi_sales']             = 'Samlet salg';
$_LANG['admin.home.index.home_container.kpi_sales_detail']      = 'Betalt salg · :currency';
$_LANG['admin.home.index.home_container.kpi_profit']            = 'Bruttofortjeneste';
$_LANG['admin.home.index.home_container.kpi_profit_detail']     = 'Sidste måned · :currency';
$_LANG['admin.home.index.home_container.kpi_issued']            = 'Udstedte certifikater';
$_LANG['admin.home.index.home_container.kpi_issued_detail']     = 'Sidste måned';
$_LANG['admin.home.index.home_container.kpi_expiring']          = 'Certifikater der udløber';
$_LANG['admin.home.index.home_container.kpi_expiring_detail']   = 'Inden for de næste 90 dage';
$_LANG['admin.home.index.home_container.version']               = 'Modulversion :version';

$_LANG['admin.home.index.widgets.overview_widget.title']        = 'Salgsoversigt';
$_LANG['admin.home.index.widgets.overview_widget.statistics']   = 'Al statistik';
$_LANG['admin.home.index.widgets.overview_widget.paid']         = 'Betalt';
$_LANG['admin.home.index.widgets.overview_widget.unpaid']       = 'Ubetalt';
$_LANG['admin.home.index.widgets.overview_widget.overdue']      = 'Forfalden';
$_LANG['admin.home.index.widgets.overview_widget.period']       = 'Sidste måned';

$_LANG['admin.home.index.widgets.announcements_widget.title']       = 'Meddelelser';
$_LANG['admin.home.index.widgets.announcements_widget.latest']      = 'Seneste opdateringer';
$_LANG['admin.home.index.widgets.announcements_widget.none']        = 'Ingen meddelelser.';
$_LANG['admin.home.index.widgets.announcements_widget.unavailable'] = 'Meddelelser kunne ikke indlæses: :error';

$_LANG['admin.home.index.widgets.support_widget.title']          = 'Supporthjælp';
$_LANG['admin.home.index.widgets.support_widget.phone']          = 'Telefon: :phone';
$_LANG['admin.home.index.widgets.support_widget.contact']        = 'Kontakt support';
$_LANG['admin.home.index.widgets.support_widget.contact_vendor'] = 'Kontakt :vendor-support';

$_LANG['admin.home.index.widgets.system_check_widget.title']     = 'Systemstatus';
$_LANG['admin.home.index.widgets.system_check_widget.ok']        = 'Alt kører';
$_LANG['admin.home.index.widgets.system_check_widget.attention'] = 'Handling påkrævet';
$_LANG['admin.home.index.widgets.system_check_widget.tasks']     = 'Planlagte opgaver';
$_LANG['admin.home.index.widgets.system_check_widget.never']     = 'Aldrig';
$_LANG['admin.home.index.widgets.system_check_widget.error']     = 'Fejl';
$_LANG['admin.home.index.widgets.system_check_widget.review']    = 'Gennemgå cron-konfiguration';
$_LANG['admin.home.index.widgets.system_check_widget.gateway']         = 'Forbindelse til gateway';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_ok']      = 'Forbundet (:ms ms)';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_error']   = 'Kan ikke nås';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_recheck'] = 'Kontroller forbindelsen igen';

$_LANG['admin.home.index.widgets.migration_widget.title']           = 'Migrering af ældre modul';
$_LANG['admin.home.index.widgets.migration_widget.intro']           = 'Flyt dine eksisterende SSL-tjenester til dette modul.';
$_LANG['admin.home.index.widgets.migration_widget.services']        = ':n aktive tjenester';
$_LANG['admin.home.index.widgets.migration_widget.choice_migrate']  = 'skal migreres';
$_LANG['admin.home.index.widgets.migration_widget.choice_skip']     = 'beholdes selvstændigt';
$_LANG['admin.home.index.widgets.migration_widget.last_run']        = 'Migrering #:id · :status';
$_LANG['admin.home.index.widgets.migration_widget.last_run_counts'] = ':done migreret, :failed fejlet, :skipped sprunget over';
$_LANG['admin.home.index.widgets.migration_widget.backups_until']   = 'Sikkerhedskopier gemmes indtil :date';
$_LANG['admin.home.index.widgets.migration_widget.open']            = 'Åbn migrering';

$_LANG['admin.home.index.widgets.partner_widget.title'] = 'Din partner growth manager';
$_LANG['admin.home.index.widgets.partner_widget.none']  = 'Ingen partner growth manager er tildelt endnu.';

$_LANG['admin.home.index.widgets.reporting_widget.title']      = 'Rapportering af modulproblemer';
$_LANG['admin.home.index.widgets.reporting_widget.info']       = 'Del modulets aktivitetslog med vores supportteam: den viser fejl og hjælper med at fejlfinde problemer, som du eller dine kunder oplever.';
$_LANG['admin.home.index.widgets.reporting_widget.sendReport'] = 'Send rapport';


$_LANG['admin.migration.title']                 = 'Migrering af ældre modul';
$_LANG['admin.migration.none_found']            = 'Intet ældre GoGetSSL- eller TheSSLStore-modul blev fundet på denne installation.';
$_LANG['admin.migration.card_products']         = 'Produkter';
$_LANG['admin.migration.card_products_sub']     = ':hidden skjulte, :retired udgåede';
$_LANG['admin.migration.card_services']         = 'Aktive tjenester';
$_LANG['admin.migration.card_orders']           = 'SSL-ordrer';
$_LANG['admin.migration.card_subscriptions']    = 'Abonnementer';
$_LANG['admin.migration.card_autoinstall']      = 'AutoInstall (cPanel)';
$_LANG['admin.migration.card_queue_open']       = ':n åbne opgaver';
$_LANG['admin.migration.card_unconfigured']     = 'Ikke konfigureret';
$_LANG['admin.migration.card_unconfigured_sub'] = ':pre forudbestilt hos leverandøren, :unpaid ubetalt';
$_LANG['admin.migration.support_full']          = 'Migrering understøttes.';
$_LANG['admin.migration.support_partial']       = 'Migrering understøttes med begrænsninger.';
$_LANG['admin.migration.support_unsupported']   = 'Denne ældre version kan ikke migreres.';
$_LANG['admin.migration.files_missing']         = 'Det ældre moduls filer findes ikke længere på serveren (kun data): en tilbagerulning gendanner dataene, men ikke et fungerende ældre modul.';
$_LANG['admin.migration.unsupported_found']     = 'Understøttes ikke af dette modul og efterlades på det ældre modul:';
$_LANG['admin.migration.other_modules']         = 'Andre SSL-moduler til stede (berøres ikke):';
$_LANG['admin.migration.choice_current']        = 'Beslutning fra opsætningsguiden:';
$_LANG['admin.migration.choice_migrate']        = 'migrer';
$_LANG['admin.migration.choice_skip']           = 'installer selvstændigt (spring over)';
$_LANG['admin.migration.choice_none']           = 'endnu ingen';
$_LANG['admin.migration.btn_report']            = 'Download prøvekørselsrapport (CSV)';
$_LANG['admin.migration.btn_start']             = 'Start migrering';
$_LANG['admin.migration.btn_skip']              = 'Spring over (behold selvstændigt)';
$_LANG['admin.migration.credentials_already_set'] = 'API-legitimationsoplysninger: dette modul har sine egne.';
$_LANG['admin.migration.credentials_available']   = 'API-legitimationsoplysninger: kopieres fra det ældre modul, hvis de stadig er tomme, når migreringen starter.';
$_LANG['admin.migration.credentials_missing']     = 'API-legitimationsoplysninger: ingen i dette modul eller i det ældre modul; indtast dem under Indstillinger før migrering.';
$_LANG['admin.migration.start_defaults']        = 'Start migrering: der oprettes først sikkerhedskopier af tabeller; AutoInstall private nøgler hentes fra cPanel (TheSSLStore); det ældre addon deaktiveres efter skiftet; ældre logtabeller indgår i sikkerhedskopien.';
$_LANG['admin.migration.btn_start_nokeys']      = 'Start uden at hente cPanel-nøgler';
$_LANG['admin.migration.btn_start_keeplegacy']  = 'Start og behold det ældre addon aktivt';
$_LANG['admin.migration.confirm_start']         = 'Start migreringen nu? Der oprettes først sikkerhedskopier af tabeller. Konfigurer ikke SSL-tjenester, mens den kører.';
$_LANG['admin.migration.unsupported_version_hint'] = 'Opdater det ældre modul til den nyeste version og genindlæs siden, eller behold det som et selvstændigt modul.';
$_LANG['admin.migration.run_title']             = 'Migrering #:id (:source)';
$_LANG['admin.migration.run_stale']             = 'Ingen fremdrift i mere end 10 minutter. Kørslen fortsætter, mens denne side er åben; eller rul den tilbage.';
$_LANG['admin.migration.verify_result']         = 'Verifikation: :checked migrerede tjenester kontrolleret, :active aktive SSL-tjenester med komplette data, :left ældre ordrerækker tilbage.';
$_LANG['admin.migration.exceptions']            = ':n undtagelser (efterladt på det ældre modul)';
$_LANG['admin.migration.col_object']            = 'Objekt';
$_LANG['admin.migration.col_status']            = 'Status';
$_LANG['admin.migration.col_reason']            = 'Årsag';
$_LANG['admin.migration.cron_title']            = 'Cron-job';
$_LANG['admin.migration.cron_remove']           = 'Fjern disse ældre cron-linjer fra serveren:';
$_LANG['admin.migration.cron_add']              = 'Sørg for, at disse cron-linjer til dette modul findes:';
$_LANG['admin.migration.legacy_deactivated']    = 'Ældre addon deaktiveret: :result';
$_LANG['admin.migration.yes']                   = 'ja';
$_LANG['admin.migration.no']                    = 'nej (deaktiver det manuelt under Setup > Addon Modules)';
$_LANG['admin.migration.btn_log']               = 'Download migreringslog (CSV)';
$_LANG['admin.migration.backups_until']         = 'Sikkerhedskopier gemmes indtil :date';
$_LANG['admin.migration.backups_deleted']       = 'Sikkerhedskopier slettet; tilbagerulning er ikke længere mulig.';
$_LANG['admin.migration.btn_extend']            = 'Behold sikkerhedskopier 14 dage mere';
$_LANG['admin.migration.btn_delete_backups']    = 'Slet sikkerhedskopier nu';
$_LANG['admin.migration.btn_rollback']          = 'Rul denne migrering tilbage';
$_LANG['admin.migration.confirm_delete']        = 'Slet sikkerhedskopitabellerne? Tilbagerulning er herefter ikke længere mulig.';
$_LANG['admin.migration.confirm_rollback']      = 'Rul tilbage? Produkter, ordrer og indstillinger gendannes fra sikkerhedskopierne, og det ældre addon genaktiveres. Tjenester solgt på dette modul siden migreringen bevares.';
$_LANG['admin.migration.runner_done']           = 'migreret';
$_LANG['admin.migration.runner_skipped']        = 'sprunget over';
$_LANG['admin.migration.runner_failed']         = 'fejlet';
$_LANG['admin.migration.runner_pending']        = 'afventer';
$_LANG['admin.migration.runner_keep_open']      = 'Kører. Hold denne side åben, indtil den er færdig.';
$_LANG['admin.migration.runner_finished']       = 'Færdig.';
$_LANG['admin.migration.runner_reload']         = 'Genindlæs for at se resultatet';
$_LANG['admin.migration.state_preparing']       = 'Forbereder (sikkerhedskopier)';
$_LANG['admin.migration.state_running']         = 'Migrerer tjenester';
$_LANG['admin.migration.state_switching']       = 'Skifter produkter og ordrer';
$_LANG['admin.migration.state_completed']       = 'Gennemført';
$_LANG['admin.migration.state_failed']          = 'Fejlet';
$_LANG['admin.migration.state_rolled_back']     = 'Rullet tilbage';

$_LANG['admin.breadcrumbs.Configuration']       = 'Modulinstallation';
$_LANG['admin.menu.configuration']              = 'Modulinstallation';
$_LANG['admin.breadcrumbs.Configuration_step1'] = 'Trin 1';
$_LANG['admin.breadcrumbs.Configuration_step2'] = 'Trin 2';
$_LANG['admin.breadcrumbs.Configuration_step3'] = 'Trin 3';
$_LANG['admin.breadcrumbs.Configuration_step4'] = 'Trin 4';
$_LANG['admin.breadcrumbs.Configuration_step5'] = 'Trin 5';
$_LANG['admin.breadcrumbs.Configuration_step6'] = 'Trin 6';
$_LANG['admin.breadcrumbs.Configuration_step7'] = 'Trin 7';

$_LANG['instalation.wizard.installation_steps']  = 'Installationstrin';
$_LANG['instalation.wizard.module_installation'] = 'Modulinstallation';
$_LANG['instalation.wizard.step_of']             = 'Trin %d af %d';
$_LANG['instalation.wizard.step1_name']          = 'Velkommen';
$_LANG['instalation.wizard.step1_desc']          = 'Introduktion';
$_LANG['instalation.wizard.step2_name']          = 'Kompatibilitetstjek';
$_LANG['instalation.wizard.step2_desc']          = 'Systemkrav';
$_LANG['instalation.wizard.step3_name']          = 'Ældre moduler';
$_LANG['instalation.wizard.step3_desc']          = 'Migrer eksisterende data';
$_LANG['instalation.wizard.step4_name']          = 'API-forbindelse';
$_LANG['instalation.wizard.step4_desc']          = 'Legitimationsoplysninger';
$_LANG['instalation.wizard.step5_name']          = 'Vælg produkter';
$_LANG['instalation.wizard.step5_desc']          = 'Importer katalog';
$_LANG['instalation.wizard.step6_name']          = 'Afslutning';
$_LANG['instalation.wizard.step6_desc']          = 'Gennemgå og afslut';

$_LANG['admin.configuration.content.step1.feature1_title'] = 'Importer produkter';
$_LANG['admin.configuration.content.step1.feature1_desc']  = 'Opret SSL-produkter i bulk';
$_LANG['admin.configuration.content.step1.feature2_title'] = 'Automatisk prissætning';
$_LANG['admin.configuration.content.step1.feature2_desc']  = 'Synkroniser med leverandørens priser';
$_LANG['admin.configuration.content.step1.feature3_title'] = 'Automatiseret udstedelse';
$_LANG['admin.configuration.content.step1.feature3_desc']  = 'Ordrer behandles via API';

$_LANG['admin.configuration.content.step1.step1_title']   = 'TRIN 1. Velkommen';
$_LANG['admin.configuration.content.step1.first_p']       = 'Tak, fordi du installerede <b>:moduleName</b>!';
$_LANG['admin.configuration.content.step1.second_p']      = 'Dette addon hjælper dig med at importere produkter som SSL-certifikater og andre sikkerhedsprodukter til din WHMCS. Du skal blot bruge en konto, og så kan du begynde at sælge disse produkter.';
$_LANG['admin.configuration.content.step1.third_p']       = 'Klik på knappen <b>"Næste"</b>, så vi kan kontrollere, om din WHMCS-installation er kompatibel.';
$_LANG['admin.configuration.content.step1.next_step_btn'] = 'Næste trin';

$_LANG['admin.configuration.content.step2.step2_title']                 = 'TRIN 2. Kontrol af systemkompatibilitet';
$_LANG['admin.configuration.content.step2.intro_passed']    = 'Din server opfylder kravene. Advarsler blokerer ikke installationen, men bør rettes.';
$_LANG['admin.configuration.content.step2.intro_failed']    = 'Nogle krav er ikke opfyldt. Ret de mislykkede punkter og genindlæs siden for at fortsætte.';
$_LANG['admin.configuration.content.step2.col_check']       = 'Kontrol';
$_LANG['admin.configuration.content.step2.col_required']    = 'Påkrævet';
$_LANG['admin.configuration.content.step2.col_found']       = 'Fundet';
$_LANG['admin.configuration.content.step2.col_status']      = 'Status';
$_LANG['admin.configuration.content.step2.status_passed']   = 'Bestået';
$_LANG['admin.configuration.content.step2.status_failed']   = 'Mislykket';
$_LANG['admin.configuration.content.step2.status_warning']  = 'Advarsel';
$_LANG['admin.configuration.content.step2.check_php']       = 'PHP-version';
$_LANG['admin.configuration.content.step2.hint_php']        = 'Opgrader PHP til WHMCS. PHP-versionen, som cron-jobbene bruger, skal også opfylde kravet.';
$_LANG['admin.configuration.content.step2.check_whmcs']     = 'WHMCS-version';
$_LANG['admin.configuration.content.step2.hint_whmcs']      = 'Opgrader WHMCS til en understøttet version.';
$_LANG['admin.configuration.content.step2.check_ext_curl']      = 'PHP-udvidelse: cURL';
$_LANG['admin.configuration.content.step2.hint_ext_curl']       = 'Påkrævet til alle API-forespørgsler. Installer eller aktiver php-curl.';
$_LANG['admin.configuration.content.step2.check_ext_openssl']   = 'PHP-udvidelse: OpenSSL';
$_LANG['admin.configuration.content.step2.hint_ext_openssl']    = 'Påkrævet til at generere CSR\'er og private nøgler. Installer eller aktiver php-openssl.';
$_LANG['admin.configuration.content.step2.check_ext_json']      = 'PHP-udvidelse: JSON';
$_LANG['admin.configuration.content.step2.hint_ext_json']       = 'Påkrævet til API-forespørgsler. Installer eller aktiver php-json.';
$_LANG['admin.configuration.content.step2.check_ext_mbstring']  = 'PHP-udvidelse: mbstring';
$_LANG['admin.configuration.content.step2.hint_ext_mbstring']   = 'Installer eller aktiver php-mbstring.';
$_LANG['admin.configuration.content.step2.check_ext_pdo_mysql'] = 'PHP-udvidelse: PDO MySQL';
$_LANG['admin.configuration.content.step2.hint_ext_pdo_mysql']  = 'Installer eller aktiver php-mysql (pdo_mysql).';
$_LANG['admin.configuration.content.step2.check_storage']   = 'Lagermapper';
$_LANG['admin.configuration.content.step2.hint_storage']    = 'Webserverens bruger skal kunne skrive til modules/addons/TSSGGSModule/storage og dens undermapper app, crons og logs.';
$_LANG['admin.configuration.content.step2.check_gateway']   = 'Forbindelse til API-gateway';
$_LANG['admin.configuration.content.step2.hint_gateway']    = 'Serveren kunne ikke nå :subject via HTTPS. Tillad udgående HTTPS (port 443) til denne adresse; API-forbindelsen testes igen i trin 4.';
$_LANG['admin.configuration.content.step2.next_step_btn']               = 'Næste trin';

$_LANG['admin.configuration.content.step3.step3_title']   = 'TRIN 3. Ældre moduler fundet';
$_LANG['admin.configuration.content.step3.intro_one']     = 'Et ældre :modules-modul er installeret på denne WHMCS. Dets produkter, ordrer, abonnementer og AutoInstall-certifikater kan flyttes til dette modul, så fornyelser, genudstedelser og e-mails fortsat fungerer her.';
$_LANG['admin.configuration.content.step3.intro_many']    = 'Ældre :modules-moduler er installeret på denne WHMCS. Deres produkter, ordrer, abonnementer og AutoInstall-certifikater kan flyttes til dette modul, så fornyelser, genudstedelser og e-mails fortsat fungerer her. Vælg pr. modul:';
$_LANG['admin.configuration.content.step3.and']           = 'og';
$_LANG['admin.configuration.content.step3.version_unknown'] = 'version ukendt';
$_LANG['admin.configuration.content.step3.support_full']    = 'Migrering understøttes';
$_LANG['admin.configuration.content.step3.support_partial'] = 'Migrering understøttes (delvist)';
$_LANG['admin.configuration.content.step3.support_unsupported'] = 'Versionen er for gammel';
$_LANG['admin.configuration.content.step3.fact_products']   = ':n produkter';
$_LANG['admin.configuration.content.step3.fact_services']   = ':active aktive tjenester (:total i alt)';
$_LANG['admin.configuration.content.step3.fact_orders']     = ':n SSL-ordrer, :unconfigured endnu ikke konfigureret, :unpaid ubetalt';
$_LANG['admin.configuration.content.step3.fact_subscriptions'] = ':acme ACME- og :ais AutoInstall SSL-abonnementer, :autoinstall certifikater installeret via cPanel';
$_LANG['admin.configuration.content.step3.unsupported_hint'] = 'Opdater først det ældre modul til den nyeste version, og kør derefter migreringen fra siden Migrering, eller installer dette modul som et nyt selvstændigt modul.';
$_LANG['admin.configuration.content.step3.runs_later']      = 'Intet er ændret endnu. Migreringen starter fra trinnet Afslutning, når dine API-legitimationsoplysninger er på plads.';
$_LANG['admin.configuration.content.step3.next_step_btn'] = 'Næste trin';

//$_LANG['admin.configuration.content.step3.step3_title']   = 'STEP 4. Vendor selection';
//$_LANG['admin.configuration.content.step3.next_step_btn'] = 'Next step';

$_LANG['admin.configuration.content.step4.step4_title']   = 'TRIN 4. Forbundet til API';
$_LANG['admin.configuration.content.step4.next_step_btn'] = 'Næste trin';
$_LANG['admin.configuration.content.step4.credentials_prefilled'] = 'API-legitimationsoplysninger blev kopieret fra modulet :modules. Test forbindelsen og gem, eller erstat dem.';

$_LANG['admin.configuration.content.step5.step5_title']   = 'TRIN 5. Vælg produkter';
$_LANG['admin.configuration.content.step5.next_step_btn'] = 'Næste trin';

$_LANG['admin.configuration.content.step6.step6_title'] = 'TRIN 6. Afslutning';
$_LANG['admin.configuration.content.step6.finalize']    = 'Afslut';


$_LANG['admin.configuration.shared.forms.vendor_selection_form.save']                           = 'Næste trin';
$_LANG['admin.configuration.shared.providers.vendor_selection_provider.error_vendor']           = 'Vælg en af leverandørerne';
$_LANG['admin.configuration.shared.providers.vendor_selection_provider.successVendorSelection'] = 'Konfigurationen blev gemt';
$_LANG['admin.configuration.shared.providers.finalize_provider.successFinalize']                = 'Konfigurationen blev gemt';
$_LANG['admin.configuration.shared.forms.vendor_selection_form.update_success']                 = 'Leverandørkonfigurationen blev gemt';
$_LANG['admin.settings.index.forms.settings_form.next_step']                                    = 'Næste trin';
$_LANG['admin.settings.index.providers.settings_provider.successAPI']                           = 'API-konfigurationen blev gemt';

$_LANG['admin.configuration.shared.forms.finalize_form.finalize'] = 'Afslut';

$_LANG['admin.configuration.content.step6.title_list'] = 'Næste trin';
$_LANG['admin.configuration.content.step6.item_list1'] = '1. Gå til sektionen <b>Priser</b> for at aktivere de produkter, du vil begynde at videresælge';
$_LANG['admin.configuration.content.step6.item_list2'] = '2. Opsæt regler for <b>fornyelse</b> under Indstillinger -> Fornyelser';

$_LANG['admin.configuration.content.step6.title_alert']    = '<b>Tillykke!</b> Du har gennemført modulinstallationen';
$_LANG['admin.configuration.content.step6.step6_api']      = '<b>API-konfiguration</b> gennemført og i drift';
$_LANG['admin.configuration.content.step6.step6_products'] = '<b>%s produkter importeret</b> og klar til endelig opsætning';


//===[Dashboard]========================================================================================================

$_LANG['admin.breadcrumbs.Home'] = 'Dashboard';
//update
//overview


//reporting
$_LANG['admin.home.index.modals.reporting_modal.sendReport']    = 'Send rapport';
$_LANG['admin.home.index.forms.reporting_form.whmcsUrl']        = 'WHMCS URL';
$_LANG['admin.home.index.forms.reporting_form.moduleVersion']   = 'Modulversion';
$_LANG['admin.home.index.forms.reporting_form.tssPartnerId']    = 'TheSSLSTORE Partner-ID';
$_LANG['admin.home.index.forms.reporting_form.ggsPartnerId']    = 'GoGetSSL Partner-ID';
$_LANG['admin.home.index.forms.reporting_form.phpVersion']      = 'PHP-version';
$_LANG['admin.home.index.forms.reporting_form.productionMode']  = 'Produktionstilstand';
$_LANG['admin.home.index.forms.reporting_form.cronStatus']      = 'CRON-status';
$_LANG['admin.home.index.forms.reporting_form.fromDate']        = 'Fra dato';
$_LANG['admin.home.index.forms.reporting_form.toDate']          = 'Til dato';
$_LANG['admin.home.index.forms.reporting_form.noModifiedFiles'] = 'Tillykke! Ingen ændrede filer';
$_LANG['admin.home.index.forms.reporting_form.modifiedFile']    = 'Bemærk! Vi fandt 1 ændret fil';
$_LANG['admin.home.index.forms.reporting_form.modifiedFiles']   = 'Bemærk! Vi fandt :modifiedCount ændrede filer';

$_LANG['admin.home.index.forms.reporting_form.issueDate']           = 'Problemdato';
$_LANG['admin.home.index.forms.reporting_form.modifiedModuleFiles'] = 'Ændrede modulfiler';
$_LANG['admin.home.index.forms.reporting_form.issueDetails']        = 'Problemdetaljer';


$_LANG['admin.home.index.forms.reporting_form.send']     = 'Send';
$_LANG['admin.home.index.forms.reporting_form.download'] = 'Download logfil';
$_LANG['admin.home.index.forms.reporting_form.cancel']   = 'Annuller';

$_LANG['admin.home.index.providers.reporting_provider.logDownloadedSuccessfully'] = 'Gennemført';

$_LANG['admin.home.index.forms.reporting_form.send_success'] = 'Din aktivitetslog blev sendt til vores team! Vi vender tilbage til dig snarest med flere oplysninger om dit problem.';


//system check


//Announcements

//manager

//support


//assistance


//systemcheck

//===[Statistics]=======================================================================================================

$_LANG['admin.breadcrumbs.Statistics']                               = 'Statistik';
$_LANG['admin.statistics.index.widgets.total_income_widget.title']   = 'Samlet indtægt';
$_LANG['admin.statistics.index.widgets.product_income_widget.title'] = 'Produktindtægt (Top 5)';
$_LANG['admin.statistics.index.widgets.resellers_widget.title']      = 'Mest profitable forhandlere';
$_LANG['admin.statistics.index.widgets.invoices_widget.title']       = 'Seneste fakturaer';

$_LANG['admin.statistics.index.graphs.total_income_graph.IncomeGraphSetTitle'] = 'Indtægt :currencySymbol (sidste seks måneder)';

$_LANG['admin.statistics.index.widgets.resellers_widget.clientId'] = 'ID';
$_LANG['admin.statistics.index.widgets.resellers_widget.client']   = 'Kunde';
$_LANG['admin.statistics.index.widgets.resellers_widget.paid']     = 'Betalt';

$_LANG['admin.statistics.index.widgets.invoices_widget.invoiceId'] = 'ID';
$_LANG['admin.statistics.index.widgets.invoices_widget.client']    = 'Kunde';
$_LANG['admin.statistics.index.widgets.invoices_widget.status']    = 'Status';
$_LANG['admin.statistics.index.widgets.invoices_widget.amount']    = 'Beløb';
$_LANG['admin.statistics.index.widgets.invoices_widget.date']      = 'Dato';


//===[Import]===========================================================================================================
$_LANG['admin.breadcrumbs.Import']                   = 'Import';
$_LANG['admin.import.index.import_container.single'] = 'Enkelt ordre';
$_LANG['admin.import.index.import_container.csv']    = 'Fra CSV';


$_LANG['admin.import.index.forms.import_form.remoteOrderId']       = 'Ordre-ID';
$_LANG['admin.import.index.forms.import_form.clientId']            = 'Kunde';
$_LANG['admin.import.index.forms.import_form.payMethod']           = 'Betalingsmetode';
$_LANG['admin.import.index.forms.import_form.vendor']              = 'Udbyder';
$_LANG['admin.import.index.forms.import_form.generateInvoice']     = 'Generer faktura';
$_LANG['admin.import.index.forms.import_form.import']              = 'Importer';
$_LANG['admin.import.index.forms.import_form.create_success']      = 'Importen blev gennemført';
$_LANG['admin.import.index.forms.import_form.productImportErrors'] = 'Der opstod fejl under importen. Se loggene for detaljer.';

$_LANG['admin.import.index.forms.import_form_csv.csv']                 = 'CSV';
$_LANG['admin.import.index.forms.import_form_csv.vendor']              = 'Udbyder';
$_LANG['admin.import.index.forms.import_form_csv.generateInvoice']     = 'Generer faktura';
$_LANG['admin.import.index.forms.import_form_csv.import']              = 'Importer';
$_LANG['admin.import.index.forms.import_form_csv.create_success']      = 'Importen blev gennemført';
$_LANG['admin.import.index.forms.import_form_csv.productImportErrors'] = 'Der opstod fejl under importen. Se loggene for detaljer.';

$_LANG['admin.import.index.forms.template_download_form.download']        = 'Download CSV-skabelon';
$_LANG['admin.import.index.providers.template_download_provider.success'] = 'Gennemført';


//===[Reports]==========================================================================================================

$_LANG['admin.breadcrumbs.Reports'] = 'Rapporter';

//tabs
$_LANG['admin.reports.index.reports_container.renewalReport']       = 'Fornyelsesrapport';
$_LANG['admin.reports.index.reports_container.datewiseSalesReport'] = 'Salgsrapport pr. dato';
$_LANG['admin.reports.index.reports_container.profitLossReport']    = 'Resultatrapport';

//renewal datatable
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.date']           = 'Dato';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.storeId']        = 'Butiks-ID';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.clientDetails']  = 'Kundeoplysninger';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.productDetails'] = 'Produktoplysninger';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.type']           = 'Type';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.brand']          = 'Brand';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.status']         = 'Status';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.issueDate']      = 'Udstedelsesdato';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.expirationDate'] = 'Udløbsdato';
//renewal datatable filters
$_LANG['admin.reports.index.data_tables.renewal.forms.filter_form.renewalPeriod']   = 'Valg';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.all']     = 'Alle';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last30']  = 'Udløbet inden for 30 dage';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last60']  = 'Udløbet inden for 60 dage';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last90']  = 'Udløbet inden for 90 dage';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.lastAll'] = 'Alle udløbne';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next30']  = 'Udløber inden for 30 dage';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next60']  = 'Udløber inden for 60 dage';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next90']  = 'Udløber inden for 90 dage';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.nextAll'] = 'Alle kommende udløb';
$_LANG['admin.reports.index.data_tables.renewal.forms.filter_form.search']          = 'Søg';
//download button
$_LANG['admin.reports.index.data_tables.renewal.forms.download_form.downloadCsv']                     = 'Eksporter til CSV';
$_LANG['admin.reports.index.data_tables.renewal.providers.download_provider.csvExportedSuccessfully'] = 'Eksporteret';

//Datewise Sales datatable
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.date']           = 'Dato';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.storeId']        = 'Butiks-ID';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.clientDetails']  = 'Kundeoplysninger';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.productDetails'] = 'Produktoplysninger';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.type']           = 'Type';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.brand']          = 'Brand';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.status']         = 'Status';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.salesAmount']    = 'Salgsbeløb';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.cost']           = 'Omkostning';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.payment']        = 'Betaling';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.paymentStatus']  = 'Betalingsstatus';
//Datewise Sales datatable filters
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.fromDate']          = 'Fra dato';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.toDate']            = 'Til dato';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.brand']             = 'Brand';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.productId']         = 'Produktnavn';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.sslStatus']         = 'SSL-status';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.vendorOrderId']     = 'Leverandørens ordre-ID';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.all']       = 'Alle';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.active']    = 'Aktiv';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.pending']   = 'Afventer';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.expired']   = 'Udløbet';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.cancelled'] = 'Annulleret';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.search']            = 'Søg';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.advancedSearch']    = 'Avanceret søgning';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.basicSearch']       = 'Enkel søgning';
//download button
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.download_form.downloadCsv']                     = 'Eksporter til CSV';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.download_provider.csvExportedSuccessfully'] = 'Eksporteret';
//header
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.paid']    = 'Betalt:';
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.unpaid']  = 'Ubetalt:';
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.overdue'] = 'Forfalden:';


//ProfitLoss Datatable
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.date']           = 'Dato';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.storeId']        = 'Butiks-ID';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.clientDetails']  = 'Kundeoplysninger';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.productDetails'] = 'Produktoplysninger';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.type']           = 'Type';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.brand']          = 'Brand';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.status']         = 'Status';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.salesAmount']    = 'Salgsbeløb';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.cost']           = 'Omkostning';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.grossProfit']    = 'Bruttofortjeneste';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.payment']        = 'Betaling';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.paymentStatus']  = 'Betalingsstatus';
//ProfitLoss Datatable filters
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.fromDate'] = 'Fra dato';
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.toDate']   = 'Til dato';
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.search']   = 'Søg';
//download button
$_LANG['admin.reports.index.data_tables.profit_loss.forms.download_form.downloadCsv']                     = 'Eksporter til CSV';
$_LANG['admin.reports.index.data_tables.profit_loss.providers.download_provider.csvExportedSuccessfully'] = 'Eksporteret';
//header
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.paid']             = 'Betalt:';
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.totalGrossProfit'] = 'Samlet bruttofortjeneste:';
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.certificateCount'] = 'Antal udstedte certifikater:';

//shared

$_LANG['admin.reports.index.data_tables.shared.records_provider.country'] = 'Land:';
$_LANG['admin.reports.index.data_tables.shared.records_provider.domain']  = 'Domæne:';

//===[Settings]=========================================================================================================

$_LANG['admin.breadcrumbs.Settings'] = 'Indstillinger';
//sidebar

$_LANG['admin.settings.index.widgets.sidebar.title']           = 'Indstillinger';
$_LANG['admin.settings.index.widgets.sidebar.apiSettings']     = 'API-indstillinger';
$_LANG['admin.settings.index.widgets.sidebar.sslSettings']     = 'SSL-indstillinger';
$_LANG['admin.settings.index.widgets.sidebar.financeSettings'] = 'Økonomiindstillinger';
$_LANG['admin.settings.index.widgets.sidebar.cronSettings']    = 'CRON-indstillinger';


//api settings

$_LANG['admin.settings.index.widgets.api_settings_widget.title']               = 'API-indstillinger';
$_LANG['admin.settings.index.forms.api_settings_form.tss']                     = 'The SSL STORE API-indstillinger';
$_LANG['admin.settings.index.forms.api_settings_form.ggs']                     = 'GoGetSSL API-indstillinger';
$_LANG['admin.settings.index.forms.api_settings_form.tssLivePartnerCode']      = 'Live partnerkode';
$_LANG['admin.settings.index.forms.api_settings_form.tssLiveAuthToken']        = 'Live auth-token (lad stå tomt for at beholde det gemte)';
$_LANG['admin.settings.index.forms.api_settings_form.ggsLivePartnerCode']      = 'Partnerkode';
$_LANG['admin.settings.index.forms.api_settings_form.ggsLiveAuthToken']        = 'Auth-token (lad stå tomt for at beholde det gemte)';
$_LANG['admin.settings.index.forms.api_settings_form.tssTestPartnerCode']      = 'Sandbox-partnerkode';
$_LANG['admin.settings.index.forms.api_settings_form.tssTestAuthToken']        = 'Sandbox auth-token (lad stå tomt for at beholde det gemte)';
$_LANG['admin.settings.index.forms.api_settings_form.OperationMode']           = 'Driftstilstand';
$_LANG['admin.settings.index.forms.api_settings_form.save']                    = 'Gem';
$_LANG['admin.settings.index.forms.api_settings_form.next_step']               = 'Næste trin';
$_LANG['admin.settings.index.forms.api_settings_form.testConnection']          = 'Test forbindelse';
$_LANG['admin.settings.index.forms.api_settings_form.update_success']          = 'Indstillingerne blev opdateret';
$_LANG['admin.settings.index.forms.api_settings_form.testconnection_success']  = 'Forbindelsen blev oprettet';
$_LANG['admin.settings.index.providers.api_settings_provider.connectionError'] = 'Forbindelsesfejl';
$_LANG['admin.settings.index.providers.api_settings_provider.successAPI']      = 'Forbindelsen lykkedes';


//ssl settings
//validation
$_LANG['admin.settings.index.widgets.ssl_settings_widget.title']                            = 'SSL-indstillinger';
$_LANG['admin.settings.index.forms.ssl_settings_form.sslValidation']                        = 'SSL-validering';
$_LANG['admin.settings.index.forms.ssl_settings_form.disableEmailValidation']               = 'Deaktiver e-mailvalidering';
$_LANG['admin.settings.index.forms.ssl_settings_form.disableEmailValidation_description']   = 'Ingen e-mailvalidering vil være tilgængelig for nogen SSL-certifikater, kun HTTP/DNS';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoDetailsForDvOrders']               = 'Automatiske admin-/teknikeroplysninger til DV-ordrer';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoDetailsForDvOrders_description']   = 'Modulet bruger kundens profil til admin-/teknikeroplysninger';
$_LANG['admin.settings.index.forms.ssl_settings_form.wildcardDnsTimeoutMinutes']            = 'Timeout for wildcard DNS-validering (minutter)';
$_LANG['admin.settings.index.forms.ssl_settings_form.wildcardDnsTimeoutMinutes_description'] = 'AutoInstall (cPanel): hvis DNS-valideringen af et wildcard-certifikat ikke er gennemført inden for dette antal minutter, skifter systemet automatisk til e-mailvalidering og giver kunden besked. Standard: 65';
$_LANG['admin.settings.index.providers.ssl_settings_provider.wildcardDnsTimeoutMinutesInvalidValue'] = 'Værdien skal være et helt antal minutter større end 0';
$_LANG['admin.settings.index.forms.ssl_settings_form.cpanelTlsVerifyDisabled'] = 'Tillad cPanel-servere med et selvsigneret certifikat';
$_LANG['admin.settings.index.forms.ssl_settings_form.cpanelTlsVerifyDisabled_description'] = 'AutoInstall logger ind på dine cPanel- / WHM-servere for at installere certifikater. Normalt kontrollerer det først, at serveren selv har et gyldigt certifikat, så dine WHM-loginoplysninger ikke kan sendes til en falsk server. Slå kun dette til, hvis AutoInstall fejler med en SSL- eller certifikatfejl, fordi en server bruger et selvsigneret eller udløbet certifikat. Fra er mere sikkert';
$_LANG['admin.settings.index.forms.ssl_settings_form.useFixedTechnicalDetails']             = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.ssl_settings_form.useFixedTechnicalDetails_description'] = '';
$_LANG['admin.settings.index.forms.ssl_settings_form.firstName']                            = 'Fornavn';
$_LANG['admin.settings.index.forms.ssl_settings_form.lastName']                             = 'Efternavn';
$_LANG['admin.settings.index.forms.ssl_settings_form.companyName']                          = 'Organisationsnavn';
$_LANG['admin.settings.index.forms.ssl_settings_form.jobTitle']                             = 'Stillingsbetegnelse';
$_LANG['admin.settings.index.forms.ssl_settings_form.emailAddress']                         = 'E-mailadresse';
$_LANG['admin.settings.index.forms.ssl_settings_form.address']                              = 'Adresse';
$_LANG['admin.settings.index.forms.ssl_settings_form.city']                                 = 'By';
$_LANG['admin.settings.index.forms.ssl_settings_form.zipCode']                              = 'Postnummer';
$_LANG['admin.settings.index.forms.ssl_settings_form.stateRegion']                          = 'Stat/region';
$_LANG['admin.settings.index.forms.ssl_settings_form.postCode']                             = 'Postnummer';
$_LANG['admin.settings.index.forms.ssl_settings_form.country']                              = 'Land';
//validations
$_LANG['admin.settings.index.forms.firstName.customAttributes.useFixedTechnicalDetails']    = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.firstName.customValues.useFixedTechnicalDetails.1']      = 'Aktiveret';
$_LANG['admin.settings.index.forms.lastName.customAttributes.useFixedTechnicalDetails']     = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.lastName.customValues.useFixedTechnicalDetails.1']       = 'Aktiveret';
$_LANG['admin.settings.index.forms.companyName.customAttributes.useFixedTechnicalDetails']  = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.companyName.customValues.useFixedTechnicalDetails.1']    = 'Aktiveret';
$_LANG['admin.settings.index.forms.jobTitle.customAttributes.useFixedTechnicalDetails']     = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.jobTitle.customValues.useFixedTechnicalDetails.1']       = 'Aktiveret';
$_LANG['admin.settings.index.forms.emailAddress.customAttributes.useFixedTechnicalDetails'] = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.emailAddress.customValues.useFixedTechnicalDetails.1']   = 'Aktiveret';
$_LANG['admin.settings.index.forms.address.customAttributes.useFixedTechnicalDetails']      = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.address.customValues.useFixedTechnicalDetails.1']        = 'Aktiveret';
$_LANG['admin.settings.index.forms.city.customAttributes.useFixedTechnicalDetails']         = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.city.customValues.useFixedTechnicalDetails.1']           = 'Aktiveret';
$_LANG['admin.settings.index.forms.zipCode.customAttributes.useFixedTechnicalDetails']      = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.zipCode.customValues.useFixedTechnicalDetails.1']        = 'Aktiveret';
$_LANG['admin.settings.index.forms.stateRegion.customAttributes.useFixedTechnicalDetails']  = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.stateRegion.customValues.useFixedTechnicalDetails.1']    = 'Aktiveret';
$_LANG['admin.settings.index.forms.postCode.customAttributes.useFixedTechnicalDetails']     = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.postCode.customValues.useFixedTechnicalDetails.1']       = 'Aktiveret';
$_LANG['admin.settings.index.forms.country.customAttributes.useFixedTechnicalDetails']      = 'Brug faste tekniske oplysninger';
$_LANG['admin.settings.index.forms.country.customValues.useFixedTechnicalDetails.1']        = 'Aktiveret';

//csr generator
$_LANG['admin.settings.index.forms.ssl_settings_form.csrGenerator']            = 'CSR-generator';
$_LANG['admin.settings.index.forms.ssl_settings_form.enableCsrGenerator']      = 'Aktiver CSR-generator';
$_LANG['admin.settings.index.forms.ssl_settings_form.useProfileDetailsForCsr'] = 'Brug profiloplysninger til CSR';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultCsrCountry']       = 'Standardland for CSR';


$_LANG['admin.settings.index.forms.ssl_settings_form.renewals']                                                 = 'Fornyelser (standardfakturering)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringOrders']                                          = 'Tilbagevendende ordrer';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeOrders']                                            = 'Engangsordrer';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonBeforeExpiration']                       = 'Vis knappen "Forny" før udløb';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonAfterExpiration']                        = 'Vis knappen "Forny" efter udløb';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewOrderViaExistingOrder']                               = 'Forny ordre via eksisterende ordre';
$_LANG['admin.settings.index.forms.ssl_settings_form.automaticProcessingOfRenewalOrders']                       = 'Automatisk behandling af fornyelsesordrer';
$_LANG['admin.settings.index.forms.ssl_settings_form.setTheUnpaidStatusForRenewalInvoices']                     = 'Sæt status ubetalt på fornyelsesfakturaer';
$_LANG['admin.settings.index.forms.ssl_settings_form.additionalDaysForRenewalOrder']                            = 'Ekstra dage til fornyelsesordre (Sectigo)';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoReissueDaysBeforeExpire']                              = 'Automatisk genudstedelse, dage før udløb';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoReissueDaysBeforeExpire_description']                  = 'AutoInstall (cPanel)-certifikater genudstedes automatisk dette antal dage før certifikatet udløber, så længe abonnementet fortsætter. 0 deaktiverer automatisk genudstedelse. Standard: 7';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoice']                   = 'Opret automatisk fornyelsesfaktura';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpiry'] = 'Opret fornyelsesfaktura: dage før ordren udløber';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotifications']                     = 'Send udløbsmeddelelser';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotifications_description']         = 'Dage før udløb, hvor kunden får en påmindelses-e-mail, adskilt af komma, 0-30. Fungerer med eller uden automatiske fornyelsesfakturaer: med fakturakontakten slået fra er dette en ren påmindelsestilstand.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.recurringSendExpirationNotificationsInvalidValue'] = 'Værdierne skal være i intervallet 0-30';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeCreateAutomaticRenewalInvoice']                     = 'Opret automatisk fornyelsesfaktura';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeDaysBeforeExpiry']                                  = 'Dage før udløb';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeSendExpirationNotifications']                       = 'Send udløbsmeddelelser';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeSendExpirationNotifications_description']           = 'Adskilt af komma, fra 0-30';
$_LANG['admin.settings.index.providers.ssl_settings_provider.oneTimeSendExpirationNotificationsInvalidValue']   = 'Værdierne skal være i intervallet 0-30';

$_LANG['admin.settings.index.forms.ssl_settings_form.renewalsSubscription']                                     = 'Fornyelser (abonnementsfakturering)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoiceSubscription']       = 'Opret automatisk fornyelsesfaktura';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpirySubscription'] = 'Opret fornyelsesfaktura: dage før abonnementet fornyes';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotificationsSubscription']         = 'Send udløbsmeddelelser';
$_LANG['admin.settings.index.providers.ssl_settings_provider.recurringSendExpirationNotificationsSubscription'] = 'Værdierne skal være i intervallet 0-30';

$_LANG['admin.settings.index.forms.ssl_settings_form.save']           = 'Gem';
$_LANG['admin.settings.index.forms.ssl_settings_form.update_success'] = 'Indstillingerne blev opdateret';

//finance settings

$_LANG['admin.settings.index.widgets.finance_settings_widget.title']     = 'Økonomiindstillinger';
$_LANG['admin.settings.index.forms.finance_settings_form.majorSettings'] = 'Hovedindstillinger';
$_LANG['admin.settings.index.forms.finance_settings_form.exchangeRate']  = 'Valutakurs';

$_LANG['admin.settings.index.forms.finance_settings_form.profitMargin'] = 'Ønsket fortjenstmargen';
$_LANG['admin.settings.index.forms.finance_settings_form.currency']     = 'Standardvaluta';
$_LANG['admin.settings.index.forms.finance_settings_form.rate']         = 'Angiv kursen';
$_LANG['admin.settings.index.forms.finance_settings_form.info']         = 'Din WHMCS har ikke defineret en USD-valuta. Din standardvaluta er :defaultCurrency. Angiv en omregningskurs fra USD til :defaultCurrency.<br><br>Hvis kursen f.eks. er 1.20, og produktprisen er 1.00 USD, bliver prisen i WHMCS sat til 1.20 :defaultCurrency.';


$_LANG['admin.settings.index.forms.finance_settings_form.save']           = 'Gem';
$_LANG['admin.settings.index.forms.finance_settings_form.update_success'] = 'Indstillingerne blev opdateret';

//cron settings
$_LANG['admin.settings.index.widgets.cron_settings_widget.title'] = 'CRON-indstillinger';

//cron settings titles
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_processing']                = '<b>Ordreinitialisering</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_ProductPricingUpdate']      = '<b>Produktpriser</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLCertificatesProcessing'] = '<b>SSL-behandling</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLCertificates']           = '<b>Daglig ordresynkronisering</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_RenewalNotifyCertificate']  = '<b>Automatisk fornyelsesmeddelelse</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_ReSyncProducts']            = '<b>Gensynkronisering af produkter</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLAutoInstall']            = '<b>SSL AutoInstall (cPanel)</b>';

//cron settings descriptions
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_ProductPricingUpdate']      = 'Opsæt følgende cron-linje for at aktivere automatiske daglige opdateringer af produktpriser.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLCertificates']           = 'Opsæt følgende cron-job for at aktivere automatisk daglig synkronisering af certifikater.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_RenewalNotifyCertificate']  = 'Opsæt følgende cron-linje for at aktivere automatiske fornyelsesmeddelelser.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLCertificatesProcessing'] = 'Opsæt følgende cron-linje for at aktivere opdateringer af ordrer under behandling.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_ReSyncProducts']            = 'Opsæt følgende cron-linje for at gensynkronisere produktkonfigurationer.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLAutoInstall']            = 'Opsæt følgende cron-linje for at behandle køen for SSL AutoInstall (cPanel): validering, installation og automatisk genudstedelse. Anbefalet: hvert 5. minut.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_processing']                = 'Opsæt følgende cron-linje for at ændre ordrernes startstatus. Cron-jobbet kører hvert 10. sekund. Seneste cron-opdatering: ';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron1']                                = 'Kørselsfrekvens for cron-jobbet&nbsp;<b>ProductPricingUpdate</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron2']                                = 'Kørselsfrekvens for cron-jobbet&nbsp;<b>SSLCertificates</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron3']                                = 'Kørselsfrekvens for cron-jobbet&nbsp;<b>RenewalNotifyCertificate</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron4']                                = 'Kørselsfrekvens for cron-jobbet&nbsp;<b>ReSyncProducts</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron5']                                = 'Kørselsfrekvens for cron-jobbet&nbsp;<b>SSLCertificatesProcessing</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.save']                                 = 'Gem';
$_LANG['admin.settings.index.forms.cron_configuration_form.update_success']                       = 'Cron-indstillingerne blev opdateret';



//===[Products]=========================================================================================================

$_LANG['admin.breadcrumbs.Products'] = 'Produkter';

// Automation Queue (AutoInstall cron tasks)
$_LANG['admin.breadcrumbs.AutomationQueue'] = 'Automatiseringskø';
$_LANG['admin.automation_queue.index.automation_queue_container.queueInfo'] = 'AutoInstall-opgaver (validering, installation, genudstedelse) behandlet af cron. En opgave med status Fejl behandles ikke igen, før den nulstilles.';

$_LANG['admin.automation_queue.index.data_tables.queue.data_table.id']       = 'ID';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.service']  = 'Tjeneste';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.domain']   = 'Domæne';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.type']     = 'Type';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.status']   = 'Status';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.attempts'] = 'Forsøg';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.message']  = 'Meddelelse';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.updated']  = 'Seneste opdatering';

$_LANG['admin.automation_queue.index.data_tables.queue.buttons.reset_task_button.resetTask'] = 'Nulstil opgave';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.title']       = 'Nulstil opgave';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.submit']      = 'Send';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.cancel']      = 'Annuller';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.description'] ='Er du sikker på, at du vil nulstille denne opgave? Dens status ændres til Afventer, og forsøgstælleren nulstilles, så cron behandler den igen ved næste kørsel.';
$_LANG['admin.automation_queue.index.data_tables.queue.forms.reset_task_form.update_success'] = 'Opgaven er nulstillet og behandles ved næste cron-kørsel';

//info
$_LANG['admin.products.index.products_container.productsInfo'] = 'Vælg et produkt og klik på knappen Importer for at importere det.';
$_LANG['admin.configuration.content.step5.productsInfo']       = 'Vælg et produkt og klik på knappen Importer for at importere det.';

//Datatable
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.id']          = 'Nr.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.productName'] = 'Produktnavn';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.brand']       = 'Brand';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.validation']  = 'Validering';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.provider']    = 'Udbyder';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.status']      = 'Status';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.imported']    = 'Importeret';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.import']      = 'Importer';


$_LANG['admin.products.index.data_tables.products.modals.re_sync_modal.reSync']           = 'Gensynkroniser produkter';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.reSync'] = 'Gensynkroniser produkter';
$_LANG['admin.products.index.data_tables.products.modals.re_sync_modal.description']      = 'Er du sikker på, at du vil gensynkronisere produkterne?';
$_LANG['admin.products.index.data_tables.products.forms.re_sync_form.create_success']     = 'Produkterne blev synkroniseret';


//import form
$_LANG['admin.products.index.data_tables.products.modals.import_modal.title']               = 'Importer';
$_LANG['admin.products.index.data_tables.products.forms.import_form.pricingTypeIndividual'] = 'Importer produkt med individuel prissætning';
$_LANG['admin.products.index.data_tables.products.forms.import_form.pricingTypePercent']    = 'Importer produkt med % margen';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productGroup']          = 'Produktgruppe';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productGroupName']      = 'Produktgruppenavn';
$_LANG['admin.products.index.data_tables.products.forms.import_form.currency']              = 'Valuta';
$_LANG['admin.products.index.data_tables.products.forms.import_form.rate']                  = 'Angiv kursen';
$_LANG['admin.products.index.data_tables.products.forms.import_form.info']                  = 'Din WHMCS har ikke defineret en USD-valuta. Din standardvaluta er :defaultCurrency. Angiv en omregningskurs fra USD til :defaultCurrency.<br><br>Hvis kursen f.eks. er 1.20, og produktprisen er 1.00 USD, bliver prisen i WHMCS sat til 1.20 :defaultCurrency.';


$_LANG['admin.products.index.data_tables.products.forms.import_form.profitMargin']             = 'Fortjenstmargen';
$_LANG['admin.products.index.data_tables.products.forms.import_form.profitMargin_description'] = '% margen anvendes på prisen for alle produkter. Du kan ændre den senere under Priser';
$_LANG['admin.products.index.data_tables.products.forms.import_form.description']              = 'Bemærk: Prisen på alle produkter fastsættes ud fra "Base Conv. Rate" for den valgte valutakode og USD-valutakoden. Sørg for, at du har angivet den korrekte "Base Conv. Rate" for hver valuta. (Standardprisen for alle produkter fra TheSSLStore API er i USD)';
$_LANG['admin.products.index.data_tables.products.forms.import_form.create_success']           = 'Importen blev gennemført';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productCreationFailed']    = 'Import mislykkedes';


//product configuration form
$_LANG['admin.products.index.data_tables.products.modals.configuration_modal.title']                  = 'Produktkonfiguration';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.remoteProductName']        = 'Produkt';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.productName']              = 'Produktnavn';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.description']              = 'Beskrivelse';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.autoSetup']                = 'Automatisk opsætning';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOrder']   = 'Opsæt produktet automatisk, så snart en ordre er afgivet';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupPayment'] = 'Opsæt produktet automatisk, så snart den første betaling er modtaget';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOn']      = 'Opsæt produktet, når du manuelt accepterer en afventende ordre';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOff']     = 'Opsæt ikke dette produkt automatisk';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.hidden']                   = 'Produkt skjult';

$_LANG['admin.products.index.data_tables.products.forms.configuration_form.enableSan']        = 'Aktiver SAN\'er';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.includedSan']      = 'Inkluderede enkelte SAN\'er';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.enableWildcard']   = 'Aktiver wildcard-SAN\'er';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.includedWildcard'] = 'Inkluderede wildcard-SAN\'er';


$_LANG['admin.products.index.data_tables.products.forms.configuration_form.update_success'] = 'Konfigurationen blev opdateret';

//default option
$_LANG['choseProductGroup']     = '--- Vælg produktgruppe ---';
$_LANG['createNewProductGroup'] = '--- Ny produktgruppe ---';


//===[Pricing]==========================================================================================================

$_LANG['admin.breadcrumbs.Pricing'] = 'Priser';
//Datatable
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.id']          = 'Nr.';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.productName'] = 'Produktnavn';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.brand']       = 'Brand';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.validation']  = 'Validering';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.provider']    = 'Udbyder';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.disabled']    = 'Deaktiveret';

$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.annuallyPriceStrict'] = 'Årligt';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.annuallyPrice']       = 'Årligt/engang';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.bienniallyPrice']     = 'Hvert andet år';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.trienniallyPrice']    = 'Hvert tredje år';

//pricing form
$_LANG['admin.pricing.index.data_tables.pricing.modals.pricing_modal.title']        = 'Priser';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.options']        = 'Indstillinger';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.annuallyStrict'] = 'Årligt';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.annually']       = 'Årligt/engang';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.biennially']     = 'Hvert andet år';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.triennially']    = 'Hvert tredje år';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.update_success'] = 'Priserne blev opdateret';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.autoUpdate']     = 'Automatisk opdatering';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.oneTime']        = 'Engangs';

$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.info'] = 'Oplysninger om automatisk opdatering';

$_LANG['acme_order_requires_at_least_one_san'] = 'For at bestille dette produkt skal du vælge mindst 1 ekstra enkelt domæne-SAN eller ekstra wildcard-SAN.';
$_LANG['csCartTermNeedsHsm'] = 'Kodesigneringscertifikater for 2 eller 3 år kan kun installeres på din egen HSM. Vælg "Min egen HSM" som certifikatlevering eller en betalingsperiode på 1 år.';




$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_ggs']   = 'GoGetSSL-modul (SSLCENTERWHMCS)';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_ggs_description'] = 'Migrer: produkter, ordrer, abonnementer og AutoInstall-certifikater flyttes til dette modul efter de næste trin (først sikkerhedskopier, tilbagerulning i 14 dage). Selvstændigt: det ældre modul kører videre uændret.';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_tss']   = 'TheSSLStore-modul (thesslstorefullv2)';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_tss_description'] = 'Migrer: produkter, ordrer, abonnementer og AutoInstall-certifikater flyttes til dette modul efter de næste trin (først sikkerhedskopier, tilbagerulning i 14 dage). Selvstændigt: det ældre modul kører videre uændret.';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.next_step_btn'] = 'Næste trin';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.option_migrate'] = 'Migrer til dette modul';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.option_skip']    = 'Installer som et nyt selvstændigt modul';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.saved']          = 'Gemt';

//general settings
$_LANG['admin.settings.index.widgets.sidebar.generalSettings']                               = 'Generelle indstillinger';
$_LANG['admin.settings.index.widgets.general_settings_widget.title']                         = 'Generelle indstillinger';
$_LANG['admin.settings.index.forms.general_settings_form.clientArea']                        = 'Kundeområde';
$_LANG['admin.settings.index.forms.general_settings_form.clientSummary']                     = 'SSL-oversigt';
$_LANG['admin.settings.index.forms.general_settings_form.clientSummary_description']         = 'Vis kundens SSL-ordrer (alle, ubetalte, under behandling, udløber snart) på kundeområdets startside og i sidepanelet';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates']                  = 'Sider med SSL-sidepanelet';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates_description']      = 'WHMCS-skabelonnavne adskilt af komma, f.eks. clientareaproducts,clientareaproductdetails. Tom = alle sider undtagen kundens startside';
$_LANG['admin.settings.index.forms.general_settings_form.expiresSoonDays']                   = 'Udløber snart (dage)';
$_LANG['admin.settings.index.forms.general_settings_form.expiresSoonDays_description']       = 'Et certifikat vises som "Udløber snart" dette antal dage før forfaldsdatoen';
$_LANG['admin.settings.index.forms.general_settings_form.certificatesByDomain']              = 'SSL-certifikater pr. domæne';
$_LANG['admin.settings.index.forms.general_settings_form.certificatesByDomain_description']  = 'Side i kundeområdet, der viser hvert domæne med dets nuværende certifikat og historikken for de tidligere';
$_LANG['admin.settings.index.forms.general_settings_form.lifecycle']                         = 'Tjenestens livscyklus';
$_LANG['admin.settings.index.forms.general_settings_form.terminateCancelsOrder']             = 'Annuller leverandørordre ved opsigelse';
$_LANG['admin.settings.index.forms.general_settings_form.terminateCancelsOrder_description'] = 'Når tjenesten opsiges i WHMCS, annulleres også certifikatordren/abonnementet hos leverandøren';
$_LANG['admin.settings.index.forms.general_settings_form.renewReuseOrderDetails'] = 'Forny S/MIME og kodesignering med de samme oplysninger';
$_LANG['admin.settings.index.forms.general_settings_form.renewReuseOrderDetails_description'] = 'En betalt fornyelse af S/MIME eller kodesignering bestiller det næste certifikat med oplysningerne fra den forrige ordre (e-mailadresser, organisation, administrator, hardware-token), medmindre kunden har valgt nye oplysninger i fornyelsesdialogen. Fra: kunden udfylder altid bestillingsformularen efter betaling.';
$_LANG['admin.settings.index.forms.general_settings_form.messages']                          = 'Kundemeddelelser';
$_LANG['admin.settings.index.forms.general_settings_form.processingGuide']                   = 'Behandlingsvejledning';
$_LANG['admin.settings.index.forms.general_settings_form.processingGuide_description']       = 'Vises for kunden, mens certifikatet valideres (HTML tilladt). Kan tilsidesættes pr. produkt';
$_LANG['admin.settings.index.forms.general_settings_form.issuedMessage']                     = 'Meddelelse om udstedt SSL';
$_LANG['admin.settings.index.forms.general_settings_form.issuedMessage_description']         = 'Vises for kunden, når certifikatet er udstedt, f.eks. installationsvejledning (HTML tilladt). Kan tilsidesættes pr. produkt';
$_LANG['admin.settings.index.forms.general_settings_form.certificateEmailTemplate']          = 'E-mailskabelon til certifikat';
$_LANG['admin.settings.index.forms.general_settings_form.certificateEmailTemplate_description'] = 'Skabelon, der bruges, når certifikatet sendes til kunden';
$_LANG['admin.settings.index.forms.general_settings_form.agreement']                         = 'Abonnentaftale';
$_LANG['admin.settings.index.forms.general_settings_form.requireAgreement']                  = 'Kræv accept af aftale';
$_LANG['admin.settings.index.forms.general_settings_form.requireAgreement_description']      = 'Kunden skal acceptere certifikatudstederens abonnentaftale, før ordren afgives';
$_LANG['admin.settings.index.forms.general_settings_form.agreementText']                     = 'Aftaletekst';
$_LANG['admin.settings.index.forms.general_settings_form.agreementText_description']         = 'Valgfri tekst vist over afkrydsningsfeltet (HTML tilladt). Linket til aftalen for produktets certifikatudsteder tilføjes automatisk';
$_LANG['admin.settings.index.forms.general_settings_form.save']                              = 'Gem';
$_LANG['admin.settings.index.providers.general_settings_provider.moduleTemplate']            = 'Modulskabelon (Send certifikat)';

//product configuration: per-product settings
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.payType']                  = 'Betalingstype';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.months']                   = 'Certifikatperiode';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.margin']               = 'Fortjenstmargen (%)';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.margin_placeholder']   = 'Global margen fra Økonomiindstillinger';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.processingGuide']          = 'Behandlingsvejledning';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.issuedMessage']            = 'Meddelelse om udstedt SSL';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeRecurring'] = 'Tilbagevendende';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeOnetime']   = 'Engangs';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeFree']       = 'Gratis';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsBillingCycle'] = 'Fra faktureringsperioden';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsValue']      = ':months måneder';

//certificate log
$_LANG['admin.menu.certificateLog']                   = 'Certifikatlog';
$_LANG['admin.breadcrumbs.CertificateLog_index']      = 'Certifikatlog';
$_LANG['admin.certificate_log.service_id']             = 'Tjeneste-ID';
$_LANG['admin.certificate_log.all_types']              = 'Alle hændelser';
$_LANG['admin.certificate_log.type_success']           = 'Gennemført';
$_LANG['admin.certificate_log.type_info']              = 'Info';
$_LANG['admin.certificate_log.type_error']             = 'Fejl';
$_LANG['admin.certificate_log.search']                 = 'Meddelelse eller domæne';
$_LANG['admin.certificate_log.filter']                 = 'Filtrer';
$_LANG['admin.certificate_log.clear_30']               = 'Ryd ældre end 30 dage';
$_LANG['admin.certificate_log.clear_all']              = 'Ryd alle';
$_LANG['admin.certificate_log.confirm_clear_30']       = 'Slet hændelser ældre end 30 dage?';
$_LANG['admin.certificate_log.confirm_clear_all']      = 'Slet alle certifikathændelser? Kunder vil ikke længere kunne se aktiviteten for deres certifikater.';
$_LANG['admin.certificate_log.cleared']                = ':count hændelser slettet.';
$_LANG['admin.certificate_log.date']                   = 'Dato';
$_LANG['admin.certificate_log.service']                = 'Tjeneste';
$_LANG['admin.certificate_log.client']                 = 'Kunde';
$_LANG['admin.certificate_log.type']                   = 'Hændelse';
$_LANG['admin.certificate_log.message']                = 'Meddelelse';
$_LANG['admin.certificate_log.empty']                  = 'Ingen hændelser fundet.';
$_LANG['admin.certificate_log.page']                   = 'Side :page af :pages (:total hændelser)';

//import: map to an existing service
$_LANG['admin.import.index.forms.import_form.serviceId'] = 'Tilknyt til eksisterende tjeneste-ID (valgfrit)';
$_LANG['admin.settings.index.forms.general_settings_form.update_success']                    = 'Indstillingerne blev opdateret';

//pricing grid
$_LANG['admin.pricing_grid.currency']          = 'Valuta';
$_LANG['admin.pricing_grid.margin']            = 'Margen %';
$_LANG['admin.pricing_grid.scope_currency']    = 'denne valuta';
$_LANG['admin.pricing_grid.scope_all']         = 'alle valutaer';
$_LANG['admin.pricing_grid.apply_margin']      = 'Anvend margen og gem alle';
$_LANG['admin.pricing_grid.confirm_margin']    = 'Sæt hver aktiveret pris til forhandlerens omkostning plus denne margen?';
$_LANG['admin.pricing_grid.export']            = 'Eksporter CSV';
$_LANG['admin.pricing_grid.import']            = 'Importer CSV';
$_LANG['admin.pricing_grid.no_products']       = 'Endnu ingen produkter fra dette modul - importer dem på siden Produkter.';
$_LANG['admin.pricing_grid.grid_help']         = 'Priser i kundens faktureringsvaluta. En tom pris deaktiverer faktureringsperioden. Røde felter ligger under din forhandleromkostning. "Auto"-produkter genberegnes af cron-jobbet til prisopdatering.';
$_LANG['admin.pricing_grid.product']           = 'Produkt';
$_LANG['admin.pricing_grid.auto']              = 'Auto';
$_LANG['admin.pricing_grid.auto_title']        = 'Opdater prisen automatisk ud fra forhandlerens omkostning og margenen';
$_LANG['admin.pricing_grid.term_12']           = '1 år';
$_LANG['admin.pricing_grid.term_24']           = '2 år';
$_LANG['admin.pricing_grid.term_36']           = '3 år';
$_LANG['admin.pricing_grid.term']              = 'Måneder';
$_LANG['admin.pricing_grid.not_in_catalogue']  = 'ikke i gatewayens katalog';
$_LANG['admin.pricing_grid.item_base']         = 'Certifikat';
$_LANG['admin.pricing_grid.item_sans']         = 'Ekstra SAN';
$_LANG['admin.pricing_grid.item_sans_wildcard'] = 'Ekstra wildcard-SAN';
$_LANG['admin.pricing_grid.item']              = 'Vare';
$_LANG['admin.pricing_grid.disabled']          = 'deaktiveret';
$_LANG['admin.pricing_grid.no_cost']           = 'ingen forhandlerpris';
$_LANG['admin.pricing_grid.cost']              = 'omkostning :cost';
$_LANG['admin.pricing_grid.below_cost']        = 'under omkostning';
$_LANG['admin.pricing_grid.save_all']          = 'Gem alle';
$_LANG['admin.pricing_grid.preview_title']     = 'Forhåndsvisning af CSV-import';
$_LANG['admin.pricing_grid.preview_summary']   = ':accepted prisændringer, :rejected afviste rækker, :unchanged uændrede.';
$_LANG['admin.pricing_grid.old_price']         = 'Nuværende';
$_LANG['admin.pricing_grid.new_price']         = 'Ny';
$_LANG['admin.pricing_grid.reseller_cost']     = 'Forhandleromkostning';
$_LANG['admin.pricing_grid.rejected_title']    = 'Afviste rækker';
$_LANG['admin.pricing_grid.line']              = 'Linje';
$_LANG['admin.pricing_grid.data']              = 'Række';
$_LANG['admin.pricing_grid.reason']            = 'Årsag';
$_LANG['admin.pricing_grid.import_confirm']    = 'Importer :count ændringer';
$_LANG['admin.pricing_grid.import_cancel']     = 'Annuller';
$_LANG['admin.pricing_grid.notice_saved']      = ':count priser gemt.';
$_LANG['admin.pricing_grid.notice_invalid'] = ':count værdier blev ikke gemt, fordi de ikke er beløb (brug cifre og decimalpunktum, for eksempel 49.90).';
$_LANG['admin.pricing_grid.notice_margin_applied'] = ':count priser opdateret ud fra forhandlerens omkostning.';
$_LANG['admin.pricing_grid.notice_imported']   = ':count priser importeret.';
$_LANG['admin.settings.index.forms.general_settings_form.pricing']                       = 'Priser';
$_LANG['admin.settings.index.forms.general_settings_form.keepMarginOnSync']              = 'Bevar margen ved synkronisering';
$_LANG['admin.settings.index.forms.general_settings_form.keepMarginOnSync_description']  = 'Produkter med manuelle priser ("Auto" slået fra på siden Priser) følger ændringer i forhandlerens omkostning, så deres margen forbliver den samme';
$_LANG['admin.settings.index.forms.general_settings_form.belowCostEmail']                = 'E-mail ved pris under omkostning';
$_LANG['admin.settings.index.forms.general_settings_form.belowCostEmail_description']    = 'Send e-mail til administratorerne, når priser ligger under forhandlerens omkostning (kontrolleres af cron-jobbet til prisopdatering, sendes én gang pr. ændring)';

//products: lifecycle
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.manage']                  = 'Administrer';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_imported']          = 'Importeret';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_not_imported']      = 'Ikke importeret';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_hidden']            = 'Skjult';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_retired']           = 'Udgået';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_removed']           = 'Fjernet af gatewayen';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_options']           = 'Kontroller indstillinger';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_hide']             = 'Skjul';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_show']             = 'Vis';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_retire']           = 'Udgå';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_unretire']         = 'Genaktiver';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_fixOptions']       = 'Ret indstillinger';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_cancelRenewals']   = 'Annuller alle fornyelser';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_retire']          = 'Lad dette produkt udgå? Det kan ikke længere bestilles; eksisterende tjenester fortsætter med at virke.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_cancel_renewals'] = 'Opret en annulleringsanmodning (ved udgangen af faktureringsperioden) for hver aktiv tjeneste for dette produkt?';
$_LANG['admin.products.index.products_container.notice_hidden']             = ':product er skjult fra bestillingsformularen.';
$_LANG['admin.products.index.products_container.notice_shown']              = ':product er synlig på bestillingsformularen.';
$_LANG['admin.products.index.products_container.notice_retired']            = ':product er udgået.';
$_LANG['admin.products.index.products_container.notice_unretired']          = ':product er aktivt igen (stadig skjult - brug Vis for at tilbyde det).';
$_LANG['admin.products.index.products_container.notice_options_fixed']      = 'Konfigurerbare indstillinger for :product kontrolleret. Ændringer: :detail';
$_LANG['admin.products.index.products_container.notice_renewals_cancelled'] = 'Annulleringsanmodninger oprettet for :detail tjenester for :product.';

//markups
$_LANG['admin.menu.markups']                = 'Tillæg';
$_LANG['admin.breadcrumbs.Markups_index']   = 'Tillæg';
$_LANG['admin.markups.intro']               = 'Hæv eller sænk prisen på SSL-produkter for en kunde: +10 lægger 10 % til produktprisen, -5 er en rabat på 5 %. Den mest specifikke regel gælder: kunde + produkt, derefter kunde + alle produkter, derefter alle kunder + produkt. Gælder for nye ordrer i indkøbskurven, på standard- og Lagom-bestillingsformularen.';
$_LANG['admin.markups.client']              = 'Kunde';
$_LANG['admin.markups.product']             = 'Produkt';
$_LANG['admin.markups.markup']          = 'Tillæg %';
$_LANG['admin.markups.all_clients']         = 'Alle kunder';
$_LANG['admin.markups.all_products']        = 'Alle SSL-produkter';
$_LANG['admin.markups.save']                = 'Tilføj / ændr';
$_LANG['admin.markups.updated']             = 'Opdateret';
$_LANG['admin.markups.delete']              = 'Slet';
$_LANG['admin.markups.confirm_delete']      = 'Slet denne provisionsregel?';
$_LANG['admin.markups.empty']               = 'Ingen provisionsregler.';
$_LANG['admin.markups.notice_saved']        = 'Provisionsregel gemt.';
$_LANG['admin.markups.notice_deleted']      = 'Provisionsregel slettet.';

//emails
$_LANG['admin.menu.emails']                              = 'E-mails';
$_LANG['admin.breadcrumbs.Emails_index']                 = 'E-mails';
$_LANG['admin.emails.intro']                             = 'De e-mails, som dette modul sender til dine kunder. De gemmes som WHMCS-e-mailskabeloner, så afsendelse, e-mailhistorik og dine WHMCS-hooks fungerer som normalt; her kan du kun se og redigere SSL-e-mails, pr. sprog.';
$_LANG['admin.emails.no_permission']                     = 'Din administratorrolle skal have WHMCS-tilladelsen ":permission" for at åbne denne side.';
$_LANG['admin.emails.group_order']                       = 'Ordre';
$_LANG['admin.emails.group_validation']                  = 'Validering';
$_LANG['admin.emails.group_issued']                      = 'Udstedt certifikat';
$_LANG['admin.emails.group_renewals']                    = 'Fornyelser og påmindelser';
$_LANG['admin.emails.group_problems']                    = 'Problemer';
$_LANG['admin.emails.badge_customised']                  = 'redigeret';
$_LANG['admin.emails.badge_disabled']                    = 'deaktiveret';
$_LANG['admin.emails.badge_unused']                      = 'sendes ikke';
$_LANG['admin.emails.badge_languages']                   = 'sprog';
$_LANG['admin.emails.whmcs_name']                        = 'WHMCS-skabelon:';
$_LANG['admin.emails.language']                          = 'Sprog';
$_LANG['admin.emails.language_default']                  = 'Standard (alle sprog uden egen tekst)';
$_LANG['admin.emails.language_default_hint']             = 'Kunder, hvis sprog ikke har en egen tekst, modtager denne.';
$_LANG['admin.emails.language_new']                      = 'Der er endnu ingen :language-tekst - standardteksten vises. Når du gemmer, oprettes :language-versionen; kunder med dette sprog modtager den.';
$_LANG['admin.emails.subject']                           = 'Emne';
$_LANG['admin.emails.message']                           = 'Tekst (HTML, Smarty-flettefelter)';
$_LANG['admin.emails.fields']                            = 'Flettefelter (klik for at indsætte):';
$_LANG['admin.emails.save']                              = 'Gem';
$_LANG['admin.emails.preview']                           = 'Forhåndsvisning';
$_LANG['admin.emails.test']                              = 'Send test til administratorer';
$_LANG['admin.emails.confirm_test']                      = 'Send denne tekst, udfyldt med eksempeldata, til de administratorer, der modtager system-e-mails? Kunder modtager intet.';
$_LANG['admin.emails.reset']                             = 'Nulstil til standard';
$_LANG['admin.emails.confirm_reset']                     = 'Erstat standardteksten med modulets oprindelige tekst? Sprogversioner forbliver, som de er.';
$_LANG['admin.emails.enable']                            = 'Aktiver';
$_LANG['admin.emails.disable']                           = 'Deaktiver';
$_LANG['admin.emails.remove_language']                   = 'Fjern :language-versionen';
$_LANG['admin.emails.confirm_remove_language']           = 'Fjern denne sprogversion? Kunder med dette sprog modtager standardteksten.';
$_LANG['admin.emails.preview_title']                     = 'Forhåndsvisning';
$_LANG['admin.emails.preview_hint']                      = 'Gengivet af WHMCS med data fra den nyeste SSL-tjeneste (kunde, produkt); de øvrige felter er eksempler.';
$_LANG['admin.emails.notice_saved']                      = 'E-mailen blev gemt.';
$_LANG['admin.emails.notice_test_sent']                  = 'Test-e-mailen blev sendt til administratorerne.';
$_LANG['admin.emails.notice_reset']                      = 'Standardteksten blev nulstillet.';
$_LANG['admin.emails.notice_language_removed']           = 'Sprogversionen blev fjernet.';
$_LANG['admin.emails.notice_disabled']                   = 'E-mailen er deaktiveret: WHMCS sender den ikke.';
$_LANG['admin.emails.notice_enabled']                    = 'E-mailen er aktiveret.';
$_LANG['admin.emails.name_configuration_required']       = 'Konfiguration påkrævet';
$_LANG['admin.emails.when_configuration_required']       = 'Sendes, når et certifikat bestilles, og kunden skal konfigurere det (CSR, domæner, validering).';
$_LANG['admin.emails.name_s_mime_configuration_required'] = 'S/MIME-certifikat - ordreoplysninger påkrævet';
$_LANG['admin.emails.when_s_mime_configuration_required'] = 'Sendes, når et S/MIME-certifikat er købt eller fornyet, og kunden skal angive den e-mailadresse, der skal sikres.';
$_LANG['admin.emails.name_s_mime_mailbox_not_confirmed'] = 'S/MIME - postkasse ikke bekræftet';
$_LANG['admin.emails.when_s_mime_mailbox_not_confirmed'] = 'Sendes 2 og 7 dage efter en S/MIME-ordre, så længe kunden ikke har bekræftet e-mailen fra certifikatudstederen.';
$_LANG['admin.emails.name_s_mime_certificate_issued'] = 'S/MIME-certifikat udstedt';
$_LANG['admin.emails.when_s_mime_certificate_issued'] = 'Sendes, når et S/MIME-certifikat er udstedt: hvordan kunden får certifikatet (CSR eller e-mailen fra certifikatudstederen).';
$_LANG['admin.emails.name_s_mime_expiration'] = 'S/MIME-certifikat udløber';
$_LANG['admin.emails.when_s_mime_expiration'] = 'Sendes, før et S/MIME-certifikat udløber, på de dage, der er valgt til udløbsmeddelelser.';
$_LANG['admin.emails.name_code_signing_configuration_required'] = 'Kodesigneringscertifikat - ordreoplysninger påkrævet';
$_LANG['admin.emails.when_code_signing_configuration_required'] = 'Sendes, når et kodesigneringscertifikat er købt eller fornyet, og kunden skal angive organisation, administrator og levering.';
$_LANG['admin.emails.name_code_signing_certificate_issued'] = 'Kodesigneringscertifikat udstedt';
$_LANG['admin.emails.when_code_signing_certificate_issued'] = 'Sendes, når et kodesigneringscertifikat er udstedt: hvordan kunden får det (sendt token, eksisterende token eller HSM).';
$_LANG['admin.emails.name_code_signing_expiration'] = 'Kodesigneringscertifikat udløber';
$_LANG['admin.emails.when_code_signing_expiration'] = 'Sendes, før et kodesigneringscertifikat udløber, på de dage, der er valgt til udløbsmeddelelser.';
$_LANG['admin.emails.name_acme_subscription_configuration'] = 'ACME-abonnement - konfiguration påkrævet';
$_LANG['admin.emails.when_acme_subscription_configuration'] = 'Sendes, når et ACME-abonnement bestilles og skal konfigureres.';
$_LANG['admin.emails.name_dns_validation_switched_to_email'] = 'DNS-validering skiftet til e-mail';
$_LANG['admin.emails.when_dns_validation_switched_to_email'] = 'Sendes, når DNS-valideringen ikke blev gennemført, og AutoInstall skiftede ordren til e-mailvalidering.';
$_LANG['admin.emails.name_validation_warning']           = 'Valideringsproblem (filmetode)';
$_LANG['admin.emails.when_validation_warning']           = 'Sendes, når CA\'en ikke kunne verificere valideringsfilen efter flere kontroller.';
$_LANG['admin.emails.name_send_certificate']             = 'Send certifikat';
$_LANG['admin.emails.when_send_certificate']             = 'Sendes, når certifikatet udstedes, og når kunden eller en administrator sender det igen; certifikatet og CA-bundlen er vedhæftet.';
$_LANG['admin.emails.name_certificate_installed']        = 'Certifikat installeret (AutoInstall)';
$_LANG['admin.emails.when_certificate_installed']        = 'Sendes, efter certifikatet er installeret på kundens cPanel-konto.';
$_LANG['admin.emails.name_service_expiration']           = 'Tjenestens udløb';
$_LANG['admin.emails.when_service_expiration']           = 'Sendes af fornyelses-cron på de dage, der er angivet i SSL-indstillinger (udløbsmeddelelser), før tjenesten udløber.';
$_LANG['admin.emails.name_reissue']                      = 'Påmindelse om genudstedelse';
$_LANG['admin.emails.when_reissue']                      = 'Sendes 30 dage før certifikatet udløber og beder kunden om at genudstede det (ikke for AutoInstall-tjenester, der genudsteder automatisk).';
$_LANG['admin.emails.name_renewal_started']              = 'Fornyelse startet (AutoInstall)';
$_LANG['admin.emails.when_renewal_started']              = 'Sendes, når den automatiske fornyelse af et AutoInstall-certifikat er startet.';
$_LANG['admin.emails.name_renewal_complete']             = 'Fornyelse gennemført (AutoInstall)';
$_LANG['admin.emails.when_renewal_complete']             = 'Sendes, efter et fornyet certifikat er installeret automatisk.';
$_LANG['admin.emails.name_certificate_autorenewed']      = 'Certifikat automatisk fornyet';
$_LANG['admin.emails.when_certificate_autorenewed']      = 'Sendes, når fornyelsesordren for et certifikat er afgivet hos CA\'en.';
$_LANG['admin.emails.name_acme_subscription_expiration'] = 'ACME-abonnement - fornyelsesfaktura';
$_LANG['admin.emails.when_acme_subscription_expiration'] = 'Sendes, når fornyelsesfakturaen for et ACME-abonnement er oprettet (hvis aktiveret i SSL-indstillinger).';
$_LANG['admin.emails.name_acme_subscription_renewal']    = 'ACME-abonnement - fornyelse betalt';
$_LANG['admin.emails.when_acme_subscription_renewal']    = 'Sendes, når fornyelsesfakturaen for et ACME-abonnement er betalt.';
$_LANG['admin.emails.name_renewal']                      = 'Fornyelse (ikke i brug)';
$_LANG['admin.emails.when_renewal']                      = 'Defineret af hensyn til kompatibilitet med GoGetSSL-modulet; denne version sender den ikke.';
$_LANG['admin.emails.name_auto_reissue_failed']          = 'Automatisk genudstedelse mislykkedes';
$_LANG['admin.emails.when_auto_reissue_failed']          = 'Sendes, når den automatiske genudstedelse før udløb mislykkedes efter alle forsøg.';

$_LANG['admin.breadcrumbs.Emails_index']        = 'E-mails';
$_LANG['admin.breadcrumbs.Emails']              = 'E-mails';
$_LANG['admin.emails.language_tab_default']     = 'Standard';
$_LANG['admin.emails.add_language']             = 'Tilføj sprog';
$_LANG['admin.emails.fields_own']               = 'Felter i denne e-mail (klik for at indsætte)';
$_LANG['admin.emails.fields_standard']          = 'Felter i alle e-mails';
$_LANG['admin.emails.opt_from']                 = 'Fra';
$_LANG['admin.emails.opt_from_name']            = 'Navn (tomt: firmanavn)';
$_LANG['admin.emails.opt_from_email']           = 'E-mailadresse (tomt: WHMCS-standarden)';
$_LANG['admin.emails.opt_copy']                 = 'Kopi til';
$_LANG['admin.emails.opt_blind']                = 'Blind kopi til';
$_LANG['admin.emails.opt_list_hint']            = 'E-mailadresser adskilt af komma';
$_LANG['admin.emails.opt_plain']                = 'Almindelig tekst';
$_LANG['admin.emails.opt_plain_hint']           = 'Send denne e-mail kun som almindelig tekst';
$_LANG['admin.emails.opt_disable']              = 'Deaktiver';
$_LANG['admin.emails.opt_disable_hint']         = 'Send ikke denne e-mail';
$_LANG['admin.emails.rich_editor']              = 'Rich text-editor';
$_LANG['admin.emails.rich_failed']              = 'Den visuelle editor kunne ikke indlæses (assets/js/tinymce i WHMCS); teksten redigeres som HTML-kildekode.';
$_LANG['admin.emails.name_renewal_invoice_created']  = 'Fornyelsesfaktura oprettet';
$_LANG['admin.emails.when_renewal_invoice_created']  = 'Sendes, når dette modul opretter en fornyelsesfaktura. Deaktiveret som standard: mens den er deaktiveret, sender WHMCS sin egen faktura-e-mail; aktiver den for at sende denne tekst i stedet.';
$_LANG['admin.breadcrumbs.CertificateLog'] = 'Certifikatlog';
$_LANG['admin.breadcrumbs.Markups'] = 'Tillæg';
$_LANG['admin.breadcrumbs.Migration'] = 'Migrering';

$_LANG['admin.switcher.yes'] = 'Ja';
$_LANG['admin.switcher.no']  = 'Nej';

$_LANG['admin.statistics.index.statistics_container.statisticsInfo'] = 'Indtægtstallene beregnes ud fra betalte WHMCS-fakturaer for de produkter, som dette modul administrerer. Diagrammerne dækker de seneste seks måneder (samlet indtægt) og de fem bedst sælgende produkter; tabellerne viser de mest profitable forhandlere og de seneste fakturaer.';
$_LANG['admin.statistics.index.widgets.expiring_certificates_widget.title'] = 'Certifikater der udløber';
$_LANG['admin.statistics.index.widgets.orders_by_status_widget.title'] = 'Ordrer efter status';
$_LANG['admin.statistics.index.widgets.revenue_by_provider_widget.title'] = 'Omsætning pr. SSL-brand (sidste 6 måneder)';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.expired'] = 'Udløbet';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.days30'] = 'Næste 30 dage';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.days60'] = '31-60 dage';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.days90'] = '61-90 dage';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.certificates'] = 'Aktive certifikater';
$_LANG['admin.import.about_title'] = 'Det finder du her';
$_LANG['admin.import.about_intro'] = 'Hent certifikater, der er bestilt uden for WHMCS, ind i WHMCS. Hver ordre bliver en tjeneste hos den kunde, du vælger, og forbliver knyttet til sit certifikat.';
$_LANG['admin.import.about_single'] = 'Enkelt ordre: indtast ordre-ID, vælg kunden og betalingsmetoden, og importer den.';
$_LANG['admin.import.about_csv'] = 'Fra CSV: download skabelonen, tilføj én række pr. ordre, og upload filen for at importere mange ordrer på én gang.';
$_LANG['admin.import.about_service'] = 'Indtast et tjeneste-ID for at knytte ordren til en eksisterende tjeneste i stedet for at oprette en ny.';
$_LANG['admin.reports.index.reports_container.reportsInfo'] = 'Rapporterne bygger på WHMCS-fakturaer for de produkter, som dette modul administrerer. Fornyelsesrapport viser certifikater efter udløbsdato, Salgsrapport pr. dato viser salget i en valgt periode, og Resultatrapport sammenligner salget med leverandørens pris. Fortjeneste vises kun for ordrer, hvor leverandørens pris er kendt.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_title'] = 'Det finder du her';
$_LANG['admin.automation_queue.index.automation_queue_container.about_intro'] = 'Arbejdslisten for SSL AutoInstall, som automatisk installerer certifikater på cPanel. Cron behandler opgaverne én ad gangen. Denne side viser kun, hvad automatiseringen har gjort; den ændrer ikke priser eller ordrer.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_validate'] = 'Validering: venter, indtil leverandøren har udstedt certifikatet, og opretter derefter en installationsopgave.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_install'] = 'Installation: lægger det udstedte certifikat på hostingkontoen.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_reissue'] = 'Genudstedelse: erstatter et certifikat, der snart udløber.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_status'] = 'Statusser: Afventer venter på næste cron-kørsel, Behandles kører nu, Færdig er afsluttet, Annulleret blev erstattet af en nyere opgave.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_error'] = 'En mislykket opgave forsøges igen op til 5 gange; derefter får den status Fejl og stopper. Læs kolonnen Meddelelse, ret årsagen, og brug Nulstil opgave til at køre den igen.';
$_LANG['admin.products.about_title'] = 'Det finder du her';
$_LANG['admin.products.about_intro'] = 'Gatewayens produkter. Importer et produkt for at sælge det i WHMCS; kolonnen Status viser, hvilken tilstand det er i.';
$_LANG['admin.products.about_import_name'] = 'Importer';
$_LANG['admin.products.about_import'] = 'Vælg produkter, og klik på Importer for at oprette dem i WHMCS.';
$_LANG['admin.products.about_hide_name'] = 'Skjul';
$_LANG['admin.products.about_hide'] = 'Fjerner midlertidigt produktet fra bestillingsformularen. Kunder med et direkte link kan stadig bestille det. Vis sætter det tilbage.';
$_LANG['admin.products.about_retire_name'] = 'Udgå';
$_LANG['admin.products.about_retire'] = 'Produktet er udgået: ingen kan bestille det, og kunder, der har det, får meddelelsen "Produkt udgået" på fornyelsesfakturaer. Genaktiver ophæver det.';
$_LANG['admin.products.about_fix_name'] = 'Ret indstillinger';
$_LANG['admin.products.about_fix'] = 'Vises kun, når status er "Kontroller indstillinger": opdaterer produktets konfigurerbare indstillinger, så de svarer til det, gatewayen tillader.';
$_LANG['admin.products.about_cancel_name'] = 'Annuller alle fornyelser';
$_LANG['admin.products.about_cancel'] = 'Sender en anmodning om annullering ved udgangen af faktureringsperioden for hver aktiv tjeneste med produktet, så ingen fornyes.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing'] = 'Faktureringstype';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_recurring'] = 'Tilbagevendende';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_onetime'] = 'Engangs';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_free'] = 'Gratis';
$_LANG['admin.pricing_grid.markup_note'] = 'Provisionsregler lægges oven i disse basispriser, når en kunde bestiller. Alle kunder: :global; kunde- eller produktspecifikke regler: :count.';
$_LANG['admin.pricing_grid.markup_edit'] = 'Rediger provisioner';
$_LANG['admin.pricing_grid.client_price'] = 'kunden betaler :price (:rate %)';
$_LANG['admin.pricing_grid.client_price_title'] = 'Pris for kunder uden en kundespecifik regel';
$_LANG['admin.settings.index.forms.general_settings_form.interface'] = 'Administrationsområde';
$_LANG['admin.settings.index.forms.general_settings_form.hideInfoMessages'] = 'Skjul infomeddelelser';
$_LANG['admin.settings.index.forms.general_settings_form.hideInfoMessages_description'] = 'Fjerner de store informationsblokke under modulets sidetitler. Advarsler, fejl og bekræftelser bliver stående.';
$_LANG['admin.emails.about_title'] = 'Det finder du her';
$_LANG['admin.emails.about_edit'] = 'Vælg en e-mail på listen, rediger emne og tekst pr. sprog, og gem. Forhåndsvisning viser, hvad en kunde modtager.';
$_LANG['admin.emails.about_fields'] = 'Flettefelter erstattes med kunde- og ordredata, når e-mailen sendes. Klik på et felt for at indsætte det.';
$_LANG['admin.settings.index.forms.general_settings_form.terminateUnpaidOnExpiry'] = 'Opsig ubetalte tjenester ved udløb';
$_LANG['admin.settings.index.forms.general_settings_form.terminateUnpaidOnExpiry_description'] = 'En tjeneste, hvis udløbsdato er passeret uden betaling, opsiges, dens ordre/abonnement annulleres hos leverandøren, og dens ubetalte fornyelsesfaktura annulleres. Hvis leverandøren ikke kan nås, forbliver tjenesten åben, og næste cron-kørsel forsøger igen. Gælder for abonnementstjenester (ikke engangstjenester).';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetRegular'] = 'Almindelige certifikater: forfaldsdato, dage før udløb';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetRegular_description'] = 'Et enkelt certifikat (uden abonnement) forfalder dette antal dage før udløb (0 = på udløbsdatoen). Anvendes, næste gang ordren synkroniseres.';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetSubscription'] = 'Abonnementer: forfaldsdato, dage før ophør';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetSubscription_description'] = 'Et flerårigt abonnement forfalder dette antal dage, før abonnementet ophører (0 = på slutdatoen). Anvendes, næste gang ordren synkroniseres.';
$_LANG['admin.settings.index.forms.general_settings_form.defaultWebServer'] = 'Standardtype for webserver';
$_LANG['admin.settings.index.forms.general_settings_form.defaultWebServer_description'] = 'Sendes med hver ordre som webserver_type, så kunderne aldrig bliver spurgt. Lad feltet være tomt, hvis din gateway ikke har brug for det.';
$_LANG['admin.emails.name_product_discontinued'] = 'Produkt udgået';
$_LANG['admin.emails.when_product_discontinued'] = 'Sendes til kunden sammen med fornyelsesfakturaen, når gatewayen ikke længere sælger tjenestens produkt (udgået eller fjernet), så fornyelse under samme produkt kan blive afvist.';
$_LANG['admin.settings.index.forms.general_settings_form.sslTools'] = 'SSL-værktøjer til kunder';
$_LANG['admin.settings.index.forms.general_settings_form.sslTools_description'] = 'Side i kundeområdet med CSR-/certifikatdekoder, nøglematch, formatkonvertering, CSR-generator og SSL-tjek. De kører på denne server og kalder ingen API.';
$_LANG['admin.settings.index.forms.general_settings_form.downloads'] = 'Download af certifikater';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_zip'] = 'Alle formater (ZIP)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_crt'] = 'Certifikat (.crt)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_ca'] = 'CA-bundle (.ca-bundle)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_fullchain'] = 'Fuld kæde (.crt)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_nginx'] = 'Nginx (kædet .crt)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_apache'] = 'Apache (.crt + .ca-bundle, ZIP)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_pkcs7'] = 'IIS / Tomcat (PKCS#7 .p7b)';
$_LANG['admin.settings.index.forms.ssl_settings_form.ordering'] = 'Bestilling og aftale';
$_LANG['admin.settings.index.forms.ssl_settings_form.delivery'] = 'Levering af certifikater';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDates'] = 'Forfaldsdatoer';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstall'] = 'AutoInstall (cPanel)';
$_LANG['admin.settings.index.providers.ssl_settings_provider.moduleTemplate'] = 'Modulskabelon (Send certifikat)';
$_LANG['admin.settings.index.widgets.automated_settings_widget.title'] = 'Automatiseret SSL (AutoInstall, ACME og planabonnementer)';
$_LANG['admin.settings.index.widgets.sidebar.automatedSettings'] = 'Automatiseret SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.processingGuide'] = 'Behandlingsvejledning';
$_LANG['admin.settings.index.forms.ssl_settings_form.processingGuide_description'] = 'Vises for kunden, mens certifikatet valideres (HTML tilladt). Kan tilsidesættes pr. produkt';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessage'] = 'Meddelelse om udstedt SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessage_description'] = 'Vises for kunden, når certifikatet er udstedt, f.eks. installationsvejledning (HTML tilladt). Kan tilsidesættes pr. produkt';
$_LANG['admin.settings.index.forms.ssl_settings_form.certificateEmailTemplate'] = 'E-mailskabelon til certifikat';
$_LANG['admin.settings.index.forms.ssl_settings_form.certificateEmailTemplate_description'] = 'Skabelon, der bruges, når certifikatet sendes til kunden';
$_LANG['admin.settings.index.forms.ssl_settings_form.requireAgreement'] = 'Kræv aftale';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetRegular'] = 'Almindelige certifikater: forfaldsdato, dage før udløb';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetRegular_description'] = 'Et enkelt certifikat (uden abonnement) forfalder dette antal dage før udløb (0 = på udløbsdatoen). Anvendes, næste gang ordren synkroniseres.';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetSubscription'] = 'Abonnementer: forfaldsdato, dage før ophør';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetSubscription_description'] = 'Et flerårigt abonnement forfalder dette antal dage, før abonnementet ophører (0 = på slutdatoen). Anvendes, næste gang ordren synkroniseres.';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultWebServer'] = 'Standardtype for webserver';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultWebServer_description'] = 'Sendes med hver ordre som webserver_type, så kunderne aldrig bliver spurgt. Lad feltet være tomt, hvis din gateway ikke har brug for det.';
$_LANG['admin.home.index.widgets.documentation_widget.title'] = 'Moduldokumentation';
$_LANG['admin.home.index.widgets.documentation_widget.info'] = 'Installationsvejledninger, konfigurationsreference og fejlfinding samlet ét sted.';
$_LANG['admin.home.index.widgets.documentation_widget.open_thesslstore'] = 'TheSSLStore-dokumentation';
$_LANG['admin.home.index.widgets.documentation_widget.open_gogetssl'] = 'GoGetSSL-dokumentation';
$_LANG['admin.home.index.widgets.next_steps_widget.title'] = 'Næste trin efter modulinstallationen';
$_LANG['admin.home.index.widgets.next_steps_widget.chip'] = 'Afslut opsætningen';
$_LANG['admin.home.index.widgets.next_steps_widget.step_products'] = 'Opsæt produkter';
$_LANG['admin.home.index.widgets.next_steps_widget.step_products_detail'] = 'Opret produkter, og tilføj en kort beskrivelse';
$_LANG['admin.home.index.widgets.next_steps_widget.step_pricing'] = 'Fastsæt priser';
$_LANG['admin.home.index.widgets.next_steps_widget.step_pricing_detail'] = 'Vælg dine salgspriser for certifikater';
$_LANG['admin.home.index.widgets.next_steps_widget.step_renewals'] = 'Kontroller fornyelsesindstillinger';
$_LANG['admin.home.index.widgets.next_steps_widget.step_renewals_detail'] = 'Gennemgå, hvordan fornyelser af certifikater håndteres';
$_LANG['admin.home.index.widgets.system_check_widget.name_ProductPricingUpdate'] = 'Opdatering af priser og katalog';
$_LANG['admin.home.index.widgets.system_check_widget.name_ReSyncProducts'] = 'Opdatering af katalog';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLCertificates'] = 'Synkronisering af udstedte ordrer';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLCertificatesProcessing'] = 'Kontrol af afventende ordrer';
$_LANG['admin.home.index.widgets.system_check_widget.name_RenewalNotifyCertificate'] = 'Fornyelser, påmindelser og udløb';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLAutoInstall'] = 'AutoInstall-kø (cPanel)';
$_LANG['admin.home.index.widgets.system_check_widget.name_cronScript'] = 'Modulets planlægger';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron1_description'] = 'Anbefalet: hver dag.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron2_description'] = 'Anbefalet: hver dag.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron3_description'] = 'Anbefalet: hver dag.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron4_description'] = 'Opdaterer produktkonfigurationen (konfigurerbare indstillinger, SAN-grænser), meddelelserne på dashboardet og oplysningerne om din kontoansvarlige. Anbefalet: én gang om dagen.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron5_description'] = 'Anbefalet: hvert 5. minut.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.oneLineTitle'] = '<b>Én cron-linje til hele modulet</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.oneLineInfo'] = 'Planlæg denne ene linje (hvert 5. minut). Den starter alle modulets job; hvert job kører kun, når det er tid, som angivet nedenfor, så intet andet skal planlægges. Hvis du ikke tilføjer den, starter modulet sine job fra WHMCS-cron (hvor serveren tillader det).';
$_LANG['admin.settings.index.widgets.cron_settings_widget.jobsTitle'] = 'Hvad jobbene gør';
$_LANG['admin.settings.index.widgets.cron_settings_widget.advanced'] = 'For at køre et enkelt job for sig (til en test eller for at planlægge det separat) skal du bruge';
$_LANG['admin.settings.index.widgets.cron_settings_widget.jobWord'] = 'job';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_ProductPricingUpdate'] = 'Opdatering af priser og katalog';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLCertificatesProcessing'] = 'Kontrol af afventende ordrer';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLCertificates'] = 'Synkronisering af udstedte ordrer';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_RenewalNotifyCertificate'] = 'Fornyelser, påmindelser og udløb';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLAutoInstall'] = 'AutoInstall-kø (cPanel)';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lastRun'] = 'Seneste kørsel:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.neverRun'] = 'aldrig';
$_LANG['admin.settings.index.widgets.cron_settings_widget.command'] = 'Kør det nu (til en test):';
$_LANG['admin.settings.index.widgets.cron_settings_widget.frequency'] = 'Hvor ofte det kører:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.everyStart'] = 'hver gang modulets cron-linje ovenfor starter (hvert 5. minut).';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_ProductPricingUpdate'] = 'Opdatering af priser og katalog';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_ProductPricingUpdate'] = 'Læser forhandlerkataloget og priserne fra gatewayen, opdaterer produktkonfigurationen, meddelelserne og din kontoansvarlige og opdaterer salgsprisen for hvert produkt, der har automatisk prissætning slået til (omkostning + din margen). Anbefalet: én gang om dagen.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLCertificatesProcessing'] = 'Kontrol af afventende ordrer';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLCertificatesProcessing'] = 'Følger de ordrer, der er afgivet og venter på certifikatudstederen. Når et certifikat er udstedt, gemmes det, forfaldsdatoen sættes, og certifikat-e-mailen sendes. Udstedte ordrer kontrolleres ikke igen her. Anbefalet: hvert 5. minut, så kunderne hurtigt får deres certifikat.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLCertificates'] = 'Synkronisering af udstedte ordrer';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLCertificates'] = 'Spørger gatewayen, hvilke ordrer der er ændret siden sidste kørsel (fornyet, udløbet, annulleret, valideret igen), og opdaterer de pågældende tjenester; den kontrollerer også op til 50 ordrer, der ikke er opdateret i et døgn. Én gang om dagen er nok: et udstedt certifikat ændrer sig sjældent.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_RenewalNotifyCertificate'] = 'Fornyelser, påmindelser og udløb';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_RenewalNotifyCertificate'] = 'Én gang om dagen: påmindelses-e-mails om udløb og genudstedelse, automatiske fornyelsesfakturaer, abonnementsfakturaer for ACME / AutoInstall og opsigelse (med annullering hos leverandøren) af tjenester, der ikke var betalt på udløbsdatoen. Deaktiver det ikke, hvis du fakturerer fornyelser automatisk.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLAutoInstall'] = 'AutoInstall-kø (cPanel)';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLAutoInstall'] = 'Arbejder sig gennem AutoInstall-køen: domænevalidering, installation på cPanel, fornyelser og automatisk genudstedelse. Det kører, hver gang modulets cron kører (hvert 5. minut).';
$_LANG['admin.settings.index.forms.cron_configuration_form.lastRun'] = 'Seneste kørsel:';
$_LANG['admin.settings.index.forms.cron_configuration_form.neverRun'] = 'aldrig';
$_LANG['admin.settings.index.forms.cron_configuration_form.runNow'] = 'Kør det nu (til en test):';
$_LANG['admin.settings.index.forms.cron_configuration_form.frequency'] = 'Hvor ofte det kører:';
$_LANG['admin.settings.index.forms.cron_configuration_form.everyStart'] = 'hver gang modulets cron-linje ovenfor starter (hvert 5. minut).';$_LANG['admin.settings.index.forms.cron_configuration_form.healthy'] = 'Fungerer';
$_LANG['admin.settings.index.forms.cron_configuration_form.failing'] = 'Fejler';
$_LANG['admin.settings.index.forms.cron_configuration_form.neverRunBadge'] = 'Ikke kørt endnu';
$_LANG['admin.settings.index.forms.cron_configuration_form.seeLog'] = 'detaljer i modulloggen';
$_LANG['admin.settings.index.forms.cron_configuration_form.howOften'] = 'Hvor ofte det kører';
$_LANG['admin.settings.index.widgets.cron_settings_widget.subtitle'] = 'Planlagte opgaver, der holder priser, ordrer og certifikater opdateret';
$_LANG['admin.settings.index.widgets.cron_settings_widget.copy'] = 'Kopiér';
$_LANG['admin.settings.index.widgets.cron_settings_widget.copied'] = 'Kopieret';
$_LANG['admin.settings.index.forms.cron_configuration_form.saveChanges'] = 'Gem ændringer';
$_LANG['admin.settings.index.forms.cron_configuration_form.noUnsaved'] = 'Ingen ændringer, der ikke er gemt';
$_LANG['admin.settings.index.forms.cron_configuration_form.unsaved'] = 'Ændringer, der ikke er gemt';
$_LANG['admin.home.index.widgets.system_check_widget.failing'] = 'Fejler';
$_LANG['admin.home.index.widgets.gateway_widget.title'] = 'Forbindelse til API-gateway';
$_LANG['admin.home.index.widgets.gateway_widget.connected'] = 'Forbundet';
$_LANG['admin.home.index.widgets.gateway_widget.unreachable'] = 'Kan ikke nås';
$_LANG['admin.home.index.widgets.gateway_widget.response'] = 'Svartid';
$_LANG['admin.home.index.widgets.gateway_widget.none'] = 'Ingen gateway konfigureret';
$_LANG['admin.home.index.widgets.gateway_widget.credentials'] = 'API-legitimationsoplysninger';
$_LANG['admin.home.index.widgets.gateway_widget.credentials_detail'] = 'Gemmes krypteret';
$_LANG['admin.home.index.widgets.gateway_widget.manage'] = 'Administrer';
$_LANG['admin.home.index.widgets.gateway_widget.recheck'] = 'Kontroller forbindelsen igen';
$_LANG['admin.home.index.widgets.attention_widget.title'] = 'Kræver din opmærksomhed';
$_LANG['admin.home.index.widgets.attention_widget.count'] = ':n punkter';
$_LANG['admin.home.index.widgets.attention_widget.count_one'] = '1 punkt';
$_LANG['admin.home.index.widgets.attention_widget.none'] = 'Intet kræver din opmærksomhed';
$_LANG['admin.home.index.widgets.attention_widget.none_detail'] = 'Alle planlagte opgaver kørte uden fejl, og gatewayen kan nås.';
$_LANG['admin.home.index.widgets.attention_widget.never_title'] = ':task har aldrig kørt';
$_LANG['admin.home.index.widgets.attention_widget.never_text'] = 'Tilføj modulets cron-linje fra CRON-indstillinger, eller kontroller, at WHMCS-cron kører.';
$_LANG['admin.home.index.widgets.attention_widget.cron'] = 'Cron-indstillinger';
$_LANG['admin.home.index.widgets.attention_widget.failing_title'] = ':task fejler';
$_LANG['admin.home.index.widgets.attention_widget.log'] = 'Åbn log';
$_LANG['admin.home.index.widgets.attention_widget.gateway_title'] = ':gateway kan ikke nås';
$_LANG['admin.home.index.widgets.attention_widget.api'] = 'API-indstillinger';
$_LANG['admin.home.index.widgets.attention_widget.configure_title'] = ':n ordrer venter på kunden';
$_LANG['admin.home.index.widgets.attention_widget.configure_title_one'] = '1 ordre venter på kunden';
$_LANG['admin.home.index.widgets.attention_widget.configure_text'] = 'Kunden har endnu ikke sendt en CSR for disse ordrer.';
$_LANG['admin.home.index.widgets.attention_widget.orders'] = 'Vis ordrer';
$_LANG['admin.home.index.widgets.attention_widget.name_ProductPricingUpdate'] = 'Opdatering af priser og katalog';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLCertificatesProcessing'] = 'Kontrol af afventende ordrer';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLCertificates'] = 'Synkronisering af udstedte ordrer';
$_LANG['admin.home.index.widgets.attention_widget.name_RenewalNotifyCertificate'] = 'Fornyelser, påmindelser og udløb';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLAutoInstall'] = 'AutoInstall-kø (cPanel)';
$_LANG['admin.home.index.widgets.pipeline_widget.title'] = 'Certifikatforløb';
$_LANG['admin.home.index.widgets.pipeline_widget.open'] = 'Åbn certifikatlisten';
$_LANG['admin.home.index.widgets.pipeline_widget.configure'] = 'Afventer konfiguration';
$_LANG['admin.home.index.widgets.pipeline_widget.configure_detail'] = 'Kunden har ikke sendt en CSR';
$_LANG['admin.home.index.widgets.pipeline_widget.validation'] = 'Validering';
$_LANG['admin.home.index.widgets.pipeline_widget.validation_detail'] = 'Hos certifikatudstederen';
$_LANG['admin.home.index.widgets.pipeline_widget.issued'] = 'Udstedt';
$_LANG['admin.home.index.widgets.pipeline_widget.issued_detail'] = 'Klar eller installeret';
$_LANG['admin.home.index.widgets.pipeline_widget.expiring'] = 'Udløber inden for 90 dage';
$_LANG['admin.home.index.widgets.pipeline_widget.expiring_detail'] = 'Fornyelse forfalder snart';
$_LANG['admin.home.index.widgets.pipeline_widget.unpaid'] = ':n venter på betaling';
$_LANG['admin.home.index.widgets.pipeline_widget.unpaid_detail'] = 'Ordrer, der endnu ikke er betalt';
$_LANG['admin.home.index.widgets.pipeline_widget.expired'] = ':n udløbet';
$_LANG['admin.home.index.widgets.pipeline_widget.expired_detail'] = 'Efter slutdatoen';
$_LANG['admin.home.index.widgets.pipeline_widget.renewals'] = 'Fornyelsesrapport';
$_LANG['admin.home.index.widgets.pipeline_widget.renewals_detail'] = 'Kommende fornyelser';
$_LANG['admin.home.index.widgets.overview_widget.invoiced'] = 'Faktureret';
$_LANG['admin.home.index.widgets.overview_widget.cost'] = 'Forhandlerens omkostning';
$_LANG['admin.home.index.widgets.overview_widget.profit'] = 'Bruttofortjeneste';
$_LANG['admin.home.index.widgets.overview_widget.margin'] = 'Margen på omkostning';
$_LANG['admin.home.index.widgets.overview_widget.split'] = 'Sidste måned: betalt :paid, ubetalt :unpaid, forfalden :overdue.';
$_LANG['admin.home.index.widgets.overview_widget.low'] = 'Margenen er under :margin%: hæv den på siden Priser.';
$_LANG['admin.home.index.widgets.overview_widget.pricing'] = 'Juster priser og margen';
$_LANG['admin.home.index.widgets.migration_widget.run_short'] = 'Kørsel #:id :status';
$_LANG['admin.home.index.widgets.support_widget.email'] = 'E-mailsupport';
$_LANG['admin.home.index.widgets.support_widget.write'] = 'Skriv';
$_LANG['admin.home.index.widgets.support_widget.docs'] = 'Dokumentation';
$_LANG['admin.home.index.widgets.support_widget.docs_detail'] = 'Installer, konfigurer, fejlfind';
$_LANG['admin.home.index.widgets.support_widget.open'] = 'Åbn';
$_LANG['admin.home.index.widgets.support_widget.manager'] = 'Din partner growth manager';
$_LANG['admin.home.index.widgets.support_widget.report'] = 'Modullogge til support';
$_LANG['admin.home.index.widgets.support_widget.report_detail'] = 'Send aktivitetsrapporten til support';
$_LANG['admin.home.index.widgets.support_widget.report_button'] = 'Send rapport';
$_LANG['admin.home.index.widgets.next_steps_widget.lead'] = 'Afslut opsætningen';
$_LANG['admin.home.index.widgets.system_check_widget.yesterday'] = 'I går :time';
$_LANG['admin.home.index.widgets.migration_widget.removed_on'] = 'Dette panel fjernes den :date, :days dage efter migreringen.';
$_LANG['admin.home.index.widgets.support_widget.phone_detail'] = 'Telefonsupport';
$_LANG['admin.home.index.widgets.gateway_widget.not_configured'] = 'Ikke konfigureret';
$_LANG['admin.home.index.widgets.gateway_widget.not_configured_detail'] = 'Indtast API-legitimationsoplysningerne';
$_LANG['admin.home.index.widgets.attention_widget.gateway_missing_title'] = 'API-legitimationsoplysninger til :gateway mangler';
$_LANG['admin.home.index.widgets.attention_widget.gateway_missing_text'] = 'Uden dem kan modulet ikke bestille eller kontrollere certifikater. Indtast dem under API-indstillinger.';
$_LANG['admin.home.index.widgets.automated_ssl_banner.label'] = 'Kampagne for Automatiseret SSL';
$_LANG['admin.home.index.widgets.automated_ssl_banner.eyebrow'] = 'Nyhed · Automatiseret SSL · ACME & AIS';
$_LANG['admin.home.index.widgets.automated_ssl_banner.title'] = 'Begynd at tilbyde Automatiseret SSL til dine kunder';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_1'] = 'Certifikater udsteder sig selv på få minutter';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_2'] = 'Fornyelser sker før udløb — hver gang';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_3'] = 'Ikke flere manuelle CSR-anmodninger eller valideringsfiler';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_4'] = 'Ingen nedetid på grund af udløbne certifikater';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_issue'] = 'Automatisk udstedelse';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_renew'] = 'Automatisk fornyelse';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_install'] = 'Automatisk installation';
$_LANG['admin.home.index.widgets.automated_ssl_banner.cta'] = 'Aktivér Automatiseret SSL';
$_LANG['admin.home.index.widgets.automated_ssl_banner.note'] = 'Baseret på protokollerne ACME og AIS — sæt det op én gang, så kører det af sig selv.';
$_LANG['admin.certificate_log.about_title'] = 'Det finder du her';
$_LANG['admin.certificate_log.about_intro'] = 'Hvad modulet har gjort med hver certifikatordre: bestilling, validering, udstedelse, installation på cPanel, genudstedelse, fornyelse, ekstra SAN og ændringer foretaget af administratorer.';
$_LANG['admin.certificate_log.about_service'] = 'Filtrer efter tjeneste-ID, eller søg efter et domæne eller et vilkårligt ord i meddelelsen.';
$_LANG['admin.certificate_log.about_steps'] = 'Mærket i starten af en meddelelse er trinnet: [Order], [Validate], [Install], [Reissue], [AutoReissue], [Renew].';
$_LANG['admin.certificate_log.about_types'] = 'Gennemført: trinnet er udført. Info: fremdrift. Fejl: trinnet mislykkedes, nogle forsøges automatisk igen. Posterne bliver stående, indtil du rydder dem.';
$_LANG['admin.certificate_log.about_other'] = 'Fejl fra gatewayen (API) og cron-fejl registreres ikke her. Se siden :logs.';
$_LANG['admin.certificate_log.logs_page'] = 'Logge';
$_LANG['admin.settings.index.widgets.sidebar.logsSettings'] = 'Logge';
$_LANG['admin.settings.index.widgets.logs_settings_widget.title'] = 'Logge';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLogs'] = 'Modullogge';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLogs_about'] = 'Gemmes i modulet og vises på dets egne sider. Når en log slås fra, stopper nye poster; de poster, der allerede er gemt, bliver stående, indtil du rydder dem.';
$_LANG['admin.settings.index.forms.logs_settings_form.certificateLog'] = 'Certifikatlog';
$_LANG['admin.settings.index.forms.logs_settings_form.certificateLog_description'] = 'Historikken for hver certifikatordre (bestilling, validering, udstedelse, installation, genudstedelse, fornyelse, ekstra SAN, administratorændringer) på siden Certifikatlog';
$_LANG['admin.settings.index.forms.logs_settings_form.internalLog'] = 'Modullog';
$_LANG['admin.settings.index.forms.logs_settings_form.internalLog_description'] = 'Modulets tekniske meddelelser (gateway- og cron-fejl, bemærkninger) på siden Logge. Når den er slået fra, er fejl sværere at spore';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLog'] = 'API-forespørgsler i modulloggen';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLog_description'] = 'Tilføj hver forespørgsel til certifikatgatewayen og dens svar til modulloggen, så en mislykket ordre eller synkronisering kan spores. Kræver, at Modullog er slået til. Nøgler og adgangskoder maskeres. Slå det fra, hvis loggen vokser for hurtigt';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogReads'] = 'Log også vellykkede læseforespørgsler';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogReads_description'] = 'Som standard logges kun mislykkede forespørgsler og forespørgsler, der ændrer noget. Cron-jobbene læser hver afventende ordre med få minutters mellemrum, så logning af hver vellykket læsning fylder hurtigt loggen. Slå det kun til under fejlfinding';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsLogs'] = 'WHMCS-logge';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsLogs_about'] = 'Skrives i selve WHMCS og læses der, ikke i dette modul.';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsActivityLog'] = 'WHMCS-aktivitetslog';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsActivityLog_description'] = 'Skriv certifikathændelser (ordre afgivet, genudstedelse, validering, installation, annullering) til WHMCS-aktivitetsloggen';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLog'] = 'WHMCS-modullog';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLog_description'] = 'Skriv forespørgsler og svar fra gatewayen og cPanel til WHMCS-modulloggen (Utilities > Logs > Module Log). Den skal også være aktiveret i WHMCS. Hvilke forespørgsler der skrives, følger indstillingen API-forespørgsler ovenfor. Adgangskoder, nøgler og private nøgler skrives aldrig';
$_LANG['admin.settings.index.forms.logs_settings_form.save'] = 'Gem';
$_LANG['admin.settings.index.forms.logs_settings_form.update_success'] = 'Indstillingerne blev opdateret';
$_LANG['admin.savebar.saveChanges'] = 'Gem ændringer';
$_LANG['admin.savebar.noUnsaved'] = 'Ingen ændringer, der ikke er gemt';
$_LANG['admin.savebar.unsaved'] = 'Ændringer, der ikke er gemt';
$_LANG['admin.savebar.notSaved'] = 'Ikke gemt. Kontroller de markerede felter, og prøv igen.';
$_LANG['admin.savebar.leaveConfirm'] = 'Du har ændringer, der ikke er gemt. Vil du forlade siden og kassere dem?';
$_LANG['admin.settings.index.widgets.api_settings_widget.subtitle'] = 'Legitimationsoplysninger til de certifikatudstedere, du videresælger';
$_LANG['admin.settings.index.widgets.general_settings_widget.subtitle'] = 'Kundeområde, administrationsområde, tjenesternes livscyklus og prisadfærd';
$_LANG['admin.settings.index.widgets.ssl_settings_widget.subtitle'] = 'Indstillinger for validering, bestilling, levering og fornyelse af certifikater';
$_LANG['admin.settings.index.widgets.automated_settings_widget.subtitle'] = 'AutoInstall (cPanel), ACME og planabonnementer';
$_LANG['admin.settings.index.widgets.logs_settings_widget.subtitle'] = 'Vælg, hvilke logge modulet fører';
$_LANG['admin.settings.index.forms.api_settings_form.tss_desc'] = 'Partnerkode og auth-token til din The SSL Store-konto. Sandbox-værdierne bruges i testtilstand.';
$_LANG['admin.settings.index.forms.api_settings_form.ggs_desc'] = 'Partnerkode og auth-token til din GoGetSSL-konto.';
$_LANG['admin.settings.index.forms.general_settings_form.clientArea_desc'] = 'Hvad kunderne ser på deres certifikatsider.';
$_LANG['admin.settings.index.forms.general_settings_form.interface_desc'] = 'Hvordan modulet ser ud for administratorer.';
$_LANG['admin.settings.index.forms.general_settings_form.lifecycle_desc'] = 'Hvad der sker med en tjeneste, når den opsiges eller ikke betales.';
$_LANG['admin.settings.index.forms.general_settings_form.pricing_desc'] = 'Hvordan priserne holdes på linje med certifikatudstederen.';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLogs_desc'] = 'Gemmes i modulet og vises på dets egne sider. Når en log slås fra, stopper nye poster; gemte poster bliver stående, indtil du rydder dem.';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsLogs_desc'] = 'Skrives i selve WHMCS og læses der, ikke i dette modul.';
$_LANG['admin.settings.index.forms.finance_settings_form.majorSettings_desc'] = 'Din fortjenstmargen oven på certifikatudstederens pris.';
$_LANG['admin.settings.index.forms.finance_settings_form.exchangeRate_desc'] = 'Nødvendig, fordi USD ikke er defineret som valuta i WHMCS.';
$_LANG['admin.settings.index.forms.ssl_settings_form.sslValidation_desc'] = 'Vælg, hvordan kunde- og kontaktoplysninger bruges til validering.';
$_LANG['admin.settings.index.forms.ssl_settings_form.csrGenerator_desc'] = 'Angiv standardværdierne for anmodninger om certifikatsignering (CSR).';
$_LANG['admin.settings.index.forms.ssl_settings_form.clientMessages'] = 'Meddelelser til kunder';
$_LANG['admin.settings.index.forms.ssl_settings_form.clientMessages_desc'] = 'Administrer de vejledninger og meddelelser, der vises for kunderne.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewals_desc'] = 'Styr, hvornår fornyelse er mulig, og hvordan ordrer behandles.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringAndOneTime'] = 'Tilbagevendende ordrer og engangsordrer';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringAndOneTime_desc'] = 'Konfigurer fornyelsesfakturaer og udløbspåmindelser hver for sig.';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDates_desc'] = 'Angiv, hvornår betalingen forfalder, før et certifikat eller abonnement ophører.';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstall_desc'] = 'DNS-ventetid, TLS-kontrol af cPanel-serverne og automatisk genudstedelse.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalsSubscription_desc'] = 'Fornyelsesfakturaer og påmindelser for ACME- og planabonnementer.';
$_LANG['admin.log_policy.apiMode_off'] = 'Log ikke';
$_LANG['admin.log_policy.apiMode_errors'] = 'Kun fejl';
$_LANG['admin.log_policy.apiMode_changes'] = 'Fejl og ændringer (anbefalet)';
$_LANG['admin.log_policy.apiMode_all'] = 'Alt, også læsninger (fejlfinding)';
$_LANG['admin.log_policy.hours_1'] = '1 time';
$_LANG['admin.log_policy.hours_4'] = '4 timer';
$_LANG['admin.log_policy.hours_12'] = '12 timer';
$_LANG['admin.log_policy.hours_24'] = '24 timer';
$_LANG['admin.log_policy.hours_72'] = '3 dage';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogMode'] = 'API-forespørgsler';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogMode_description'] = 'Forespørgsler til certifikatgatewayen og dens svar. Fejl = gatewayen returnerede en fejl eller svarede ikke. Ændringer = ordrer, valideringer, genudstedelser, annulleringer. Læsninger = cron-jobbene, der spørger om status for hver afventende ordre med få minutters mellemrum: de fylder loggen, så brug kun Alt under fejlfinding. Det samme valg afgør, hvad der skrives til WHMCS-modulloggen';
$_LANG['admin.settings.index.forms.logs_settings_form.eventLog'] = 'Hændelser';
$_LANG['admin.settings.index.forms.logs_settings_form.eventLog_description'] = 'Hvad cron-jobbene og ordrehandlingerne har gjort: statusændringer, notifikationer, fornyelsesfakturaer, prisopdateringer. Fejl registreres altid';
$_LANG['admin.settings.index.forms.logs_settings_form.debugLogs'] = 'Fejlfind én ordre';
$_LANG['admin.settings.index.forms.logs_settings_form.debugLogs_desc'] = 'Leder du efter årsagen til et problem med en ordre? Registrer alt for den pågældende tjeneste i en begrænset periode, uanset indstillingerne ovenfor; derefter vender det af sig selv tilbage til det normale.';
$_LANG['admin.settings.index.forms.logs_settings_form.debugServices'] = 'Tjeneste-ID-numre';
$_LANG['admin.settings.index.forms.logs_settings_form.debugServices_description'] = 'Tjenestenumre adskilt af komma, f.eks. 71, 58 (nummeret efter # i Related Item). Hver gateway-forespørgsel, også læsninger, og hver hændelse for disse tjenester registreres. Lad feltet være tomt for at slå fejlfindingstilstanden fra';
$_LANG['admin.settings.index.forms.logs_settings_form.debugHours'] = 'Registrer i';
$_LANG['admin.settings.index.forms.logs_settings_form.debugHours_description'] = 'Når du gemmer, starter perioden forfra. Fejlfindingstilstanden slutter af sig selv bagefter';
$_LANG['admin.settings.index.widgets.finance_settings_widget.subtitle'] = 'Priser, margener, fornyelser, valuta og faktureringsregler';
$_LANG['admin.settings.index.forms.finance_settings_form.pricing'] = 'Prissætning';
$_LANG['admin.settings.index.forms.finance_settings_form.minProfit'] = 'Mindste fortjeneste pr. certifikat';
$_LANG['admin.settings.index.forms.finance_settings_form.minProfit_description'] = 'I USD (eller din standardvaluta, hvis WHMCS ikke har USD). Et certifikat giver altid mindst dette beløb ud over omkostningen, uanset margenen: 30% af et certifikat til 6.00 er kun 1.80. Gælder ikke for SAN-tilvalg. 0 = slået fra';
$_LANG['admin.settings.index.forms.finance_settings_form.termMarginStep'] = 'Lavere margen pr. ekstra år %';
$_LANG['admin.settings.index.forms.finance_settings_form.termMarginStep_description'] = 'Margenen for perioder på 2 og 3 år sænkes med denne værdi pr. ekstra år (margen 30, trin 5: 1 år 30%, 2 år 25%, 3 år 20%), så lange perioder forbliver konkurrencedygtige. Aldrig under 0. 0 = samme margen for alle perioder';
$_LANG['admin.settings.index.forms.finance_settings_form.priceRounding'] = 'Prisafrunding';
$_LANG['admin.settings.index.forms.finance_settings_form.priceRounding_description'] = 'Runder hver beregnet pris op til en pæn slutværdi. Afrunding sænker aldrig en pris';
$_LANG['admin.settings.index.forms.finance_settings_form.keepMarginOnSync'] = 'Bevar margenen, når omkostningerne ændres';
$_LANG['admin.settings.index.forms.finance_settings_form.keepMarginOnSync_description'] = 'Produkter med manuelle priser (Auto slået fra på siden Priser) følger ændringer i forhandlerens omkostning, så deres margen forbliver den samme';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_none'] = 'Ingen afrunding (13.37)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_99'] = 'Op til .99 (13.99)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_95'] = 'Op til .95 (13.95)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_whole'] = 'Op til et helt tal (14.00)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_half'] = 'Op til næste halve (13.50)';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCost'] = 'Beskyttelse mod salg under kostpris';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCost_desc'] = 'En pris kan komme under din forhandleromkostning efter en prisstigning hos leverandøren eller en ændring i valutakursen. Kontrolleres efter hver prisopdatering.';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostAction'] = 'Når en pris er under omkostningen';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostAction_description'] = 'Hæv prisen: prisen sættes til produktets normale pris (omkostning + dets margen, mindste fortjeneste og afrunding). Gælder både manuelle og automatiske priser';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostEmail'] = 'Send e-mail til administratorerne';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostEmail_description'] = 'Liste over priser under omkostningen (eller over de hævede priser), sendt én gang pr. ændring';
$_LANG['admin.settings.index.providers.finance_settings_provider.belowCost_notify'] = 'Giv mig kun besked';
$_LANG['admin.settings.index.providers.finance_settings_provider.belowCost_raise'] = 'Hæv prisen automatisk';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPrices'] = 'Fornyelsespriser for eksisterende tjenester';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceMode'] = 'Eksisterende tjenester fornyes til';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceClientEmail'] = 'Send e-mail til kunden, før en pris stiger';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceClientEmail_description'] = 'Sendes, når fornyelsesprisen for en tjeneste hæves. Sænkede priser meddeles ikke';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceEmailText'] = 'E-mail om prisændring';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceEmailText_description'] = 'Flettefelterne fra WHMCS-produkt-e-mails virker ({$client_first_name}, {$service_product_name}, {$service_domain}, {$service_next_due_date}, {$signature}) samt {$old_price} og {$new_price}';
$_LANG['admin.settings.index.forms.finance_settings_form.currency_desc'] = 'Forhandlerens omkostning er i USD. Andre valutaer omregnes med valutakurserne i WHMCS (Setup > Payments > Currencies).';
$_LANG['admin.settings.index.forms.finance_settings_form.rateSource'] = 'Valutakurs fra USD til din standardvaluta';
$_LANG['admin.settings.index.forms.finance_settings_form.rateSource_description'] = 'WHMCS har ingen USD-valuta, så modulet omregner selv omkostningen. Dagskurs: opdateres fra Den Europæiske Centralbank før hver prisopdatering';
$_LANG['admin.settings.index.forms.finance_settings_form.rate_description'] = '1 USD = dette antal enheder af din standardvaluta. Med dagskursen udfyldes feltet automatisk';
$_LANG['admin.settings.index.forms.finance_settings_form.currencyBuffer'] = 'Sikkerhedsbuffer for valuta %';
$_LANG['admin.settings.index.forms.finance_settings_form.currencyBuffer_description'] = 'Lægges til priser i andre valutaer end USD, så en kursændring mellem to prisopdateringer ikke æder din margen. 0 = slået fra';
$_LANG['admin.settings.index.providers.finance_settings_provider.rateSource_manual'] = 'Fast kurs (angives nedenfor)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rateSource_ecb'] = 'Dagskurs (Den Europæiske Centralbank)';
$_LANG['admin.settings.index.forms.finance_settings_form.billing'] = 'Fakturering';
$_LANG['admin.settings.index.forms.finance_settings_form.billing_desc'] = 'Hvornår fornyelser forfalder, og hvad der sker med ubetalte tjenester. Fornyelsesfakturaer og udløbspåmindelser findes under Indstillinger > SSL-certifikater og Automatiseret SSL.';
$_LANG['admin.settings.index.forms.finance_settings_form.terminateUnpaidOnExpiry'] = 'Opsig ubetalte tjenester ved udløb';
$_LANG['admin.settings.index.forms.finance_settings_form.terminateUnpaidOnExpiry_description'] = 'En tjeneste, hvis udløbsdato er passeret uden betaling, opsiges, dens ordre/abonnement annulleres hos leverandøren, og dens ubetalte fornyelsesfaktura annulleres. Hvis leverandøren ikke kan nås, forbliver tjenesten åben, og næste cron-kørsel forsøger igen. Gælder for abonnementstjenester (ikke engangstjenester)';
$_LANG['admin.settings.index.forms.finance_settings_form.dueDateOffsetRegular'] = 'Certifikater: forfaldsdato, dage før udløb';
$_LANG['admin.settings.index.forms.finance_settings_form.dueDateOffsetRegular_description'] = 'Et enkelt certifikat (uden abonnement) forfalder dette antal dage før udløb (0 = på udløbsdatoen). Anvendes, næste gang ordren synkroniseres';
$_LANG['admin.settings.index.forms.finance_settings_form.dueDateOffsetSubscription'] = 'Abonnementer: forfaldsdato, dage før ophør';
$_LANG['admin.settings.index.forms.finance_settings_form.dueDateOffsetSubscription_description'] = 'Et flerårigt abonnement forfalder dette antal dage, før abonnementet ophører (0 = på slutdatoen). Anvendes, næste gang ordren synkroniseres';
$_LANG['admin.settings.index.forms.finance_settings_form.balance'] = 'Forhandlersaldo';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPrices_desc'] = 'Når du ændrer en pris, fortsætter WHMCS med at opkræve den pris, som eksisterende tjenester blev bestilt til. Vælg, hvad eksisterende tjenester betaler ved næste fornyelse (kontrolleres efter hver prisopdatering; fakturaer, der allerede findes, ændres ikke):';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_never'] = 'Behold den bestilte pris:';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_never_text'] = 'kunderne fornyer altid til den pris, de betalte første gang. Trygt for kunderne, men hvis din omkostning stiger, kan du ende med at sælge under kostpris.';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_belowCost'] = 'Opdater kun, når prisen er under omkostningen (anbefalet):';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_belowCost_text'] = 'en tjeneste beholder sin pris, indtil prisen ikke længere dækker din forhandleromkostning; derefter overgår den til den aktuelle pris. Trofaste kunder beholder deres pris, og du taber aldrig penge.';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_always'] = 'Altid den aktuelle pris:';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_always_text'] = 'hver tjeneste fornyes til dagens prisliste, både op og ned, også tjenester, du har givet en særpris.';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceMode_description'] = 'Den aktuelle pris er produktprisen, dens SAN-tilvalg og kampagne i dag, med kundens provisionsregel';
$_LANG['admin.settings.index.providers.finance_settings_provider.renewal_never'] = 'Behold den bestilte pris';
$_LANG['admin.settings.index.providers.finance_settings_provider.renewal_belowCost'] = 'Opdater kun, når prisen er under omkostningen';
$_LANG['admin.settings.index.providers.finance_settings_provider.renewal_always'] = 'Altid den aktuelle pris';
$_LANG['admin.settings.index.forms.finance_settings_form.balance_desc'] = 'Certifikatordrer betales fra din saldo hos certifikatleverandøren. Hvis saldoen slipper op, kan betalte ordrer ikke udstedes, og dine kunder må vente på deres certifikater.';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceAlert'] = 'Send mig en e-mail, når en ordre mislykkes på grund af manglende dækning';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceAlert_description'] = 'Du får en e-mail, så snart leverandøren afviser en ordre, fordi saldoen er for lav, så du kan indbetale og sende ordren igen. Højst én e-mail hver 6. time';
$_LANG['admin.settings.index.forms.finance_settings_form.pricing_desc'] = 'Standardreglerne for, hvordan din forhandleromkostning omsættes til en salgspris. De gælder for produkter med automatisk prissætning, for Anvend margen på siden Priser, for produktimporten og for nye SAN-tilvalg.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesIntro'] = 'Andre indstillinger påvirker også, hvad kunderne betaler. Det er aktuelt indstillet sådan:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesAuto'] = 'Siden Priser:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesProducts'] = 'Siden Produkter:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkups'] = 'Tillæg:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsGlobal'] = 'Tillæg:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsNone'] = 'Tillæg:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsNone_text'] = 'ingen regler, kunderne betaler de priser, der beregnes her.';
$_LANG['admin.settings.index.forms.finance_settings_form.openPricing'] = 'Åbn Priser';
$_LANG['admin.settings.index.forms.finance_settings_form.openProducts'] = 'Åbn Produkter';
$_LANG['admin.settings.index.forms.finance_settings_form.openMarkups'] = 'Åbn Tillæg';
$_LANG['admin.settings.index.forms.finance_settings_form.profitMargin_description'] = 'Lægges oven i forhandlerens omkostning for produkter med automatisk prissætning, der ikke har deres egen margen. 0 sælger til kostpris';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesAuto_text'] = 'automatisk prissætning (bruger margenen nedenfor): :auto, manuelle priser (ændres ikke af denne margen): :manual.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesProducts_text'] = 'produkter med deres egen margen, som erstatter standardmargenen nedenfor: :count.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkups_text'] = 'regler, der lægger en procentdel oven i prisen for udvalgte kunder eller produkter, ved betaling og ved fornyelser: :count.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsGlobal_text'] = '+:global% for alle kunder på alle produkter (regler i alt: :count). Det lægges oven i hver pris ved betaling og ved fornyelser, så kunderne betaler mere end de priser, der beregnes her.';
$_LANG['admin.settings.index.forms.ssl_settings_form.useDefaultWebServer'] = 'Spring spørgsmålet om webserver over';
$_LANG['admin.settings.index.forms.ssl_settings_form.useDefaultWebServer_description'] = 'Ja: kunderne bliver ikke spurgt, hvilken webserver de bruger, når de konfigurerer certifikatet (Other registreres). Nej: kunderne vælger deres webserver (Apache, IIS, cPanel ...) på WHMCS-listen under konfigurationen; valget vises på tjenesten. Certifikatet er det samme i begge tilfælde';
$_LANG['admin.settings.index.forms.ssl_settings_form.ordering_desc'] = 'Om kunderne bliver spurgt om deres webserver, og den abonnentaftale, de accepterer, før ordren afgives.';
$_LANG['admin.settings.index.forms.ssl_settings_form.requireAgreement_description'] = 'Kunden skal acceptere abonnentaftalen fra produktets certifikatudsteder, før ordren afgives. Aftalen nedenfor vælges automatisk ud fra produktets brand';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_sectigo'] = 'Sectigo-aftale (Sectigo, Comodo, PositiveSSL, InstantSSL, EssentialSSL)';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_digicert'] = 'DigiCert-aftale (DigiCert, GeoTrust, RapidSSL, Thawte)';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_gogetssl'] = 'GoGetSSL-aftale';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_other'] = 'Andre certifikatudstedere';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_sectigo'] = 'Link til aftalen';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_digicert'] = 'Link til aftalen';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_gogetssl'] = 'Link til aftalen';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_sectigo_description'] = 'Åbnes fra det afkrydsningsfelt, kunden markerer. Tom = aftalens navn uden link';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_digicert_description'] = 'Åbnes fra det afkrydsningsfelt, kunden markerer. Tom = aftalens navn uden link';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_gogetssl_description'] = 'Åbnes fra det afkrydsningsfelt, kunden markerer. Tom = aftalens navn uden link';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_sectigo'] = 'Tekst over afkrydsningsfeltet';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_digicert'] = 'Tekst over afkrydsningsfeltet';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_gogetssl'] = 'Tekst over afkrydsningsfeltet';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_sectigo_description'] = 'Valgfri, HTML tilladt. Tom = teksten for andre certifikatudstedere nedenfor';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_digicert_description'] = 'Valgfri, HTML tilladt. Tom = teksten for andre certifikatudstedere nedenfor';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_gogetssl_description'] = 'Valgfri, HTML tilladt. Tom = teksten for andre certifikatudstedere nedenfor';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText'] = 'Tekst over afkrydsningsfeltet';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_description'] = 'Valgfri, HTML tilladt. Vises for produkter fra enhver anden certifikatudsteder og for udstederne ovenfor, der ikke har deres egen tekst';
$_LANG['admin.settings.index.forms.ssl_settings_form.delivery_desc'] = 'De downloadknapper, kunderne ser, når deres certifikat er udstedt. Hver knap giver det samme certifikat, pakket sådan som en bestemt servertype forventer det. Slå dem fra, som dine kunder ikke har brug for; hvis alle er slået fra, tilbydes ZIP-filen med det hele.';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_zip'] = 'Alt i én ZIP';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_zip_description'] = 'Én ZIP med alle filerne nedenfor (certifikat, CA-bundle, kædet certifikat, .p7b). Det sikre valg, når du ikke kender kundens server';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_crt'] = 'Kun certifikatet';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_crt_description'] = 'Kundens certifikat alene (.crt) uden de mellemliggende certifikater. Til kontrolpaneler, der beder om certifikatet og CA-bundlen i separate felter (cPanel, Plesk, DirectAdmin)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_ca'] = 'Mellemliggende certifikater (CA-bundle)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_ca_description'] = 'Kun certifikatudstederens mellemliggende certifikater (.ca-bundle). Installeres sammen med certifikatet; browsere viser fejl, når de mangler';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_fullchain'] = 'Certifikat med mellemliggende certifikater (fuld kæde)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_fullchain_description'] = 'Certifikat og mellemliggende certifikater i én fil (.fullchain.crt). Til de fleste moderne servere og værktøjer: HAProxy, load balancere, Caddy, nyere Apache';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_nginx'] = 'Nginx';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_nginx_description'] = 'Samme indhold som den fulde kæde, navngivet til Nginx (.chained.crt), den fil, som linjen ssl_certificate peger på';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_apache'] = 'Apache';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_apache_description'] = 'En ZIP med certifikatet og CA-bundlen som to filer til SSLCertificateFile og SSLCertificateChainFile i Apache';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_pkcs7'] = 'Windows IIS / Tomcat';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_pkcs7_description'] = 'Certifikat og mellemliggende certifikater i én PKCS#7-fil (.p7b), det format, som Windows (IIS, Exchange) og Java-servere (Tomcat) importerer';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates_placeholder'] = 'Alle sider undtagen kundens startside';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareahome'] = 'Kundens startside';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareaproducts'] = 'Mine tjenester';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareaproductdetails'] = 'Tjenestedetaljer';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareadomains'] = 'Mine domæner';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareadomaindetails'] = 'Domænedetaljer';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareainvoices'] = 'Mine fakturaer';
$_LANG['admin.settings.index.providers.general_settings_provider.page_supportticketslist'] = 'Mine sager';
$_LANG['admin.settings.index.providers.general_settings_provider.page_viewticket'] = 'Sagsdetaljer';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareadetails'] = 'Kontooplysninger';
$_LANG['admin.settings.index.providers.general_settings_provider.page_knowledgebase'] = 'Vidensbase';
$_LANG['admin.settings.index.providers.general_settings_provider.page_announcements'] = 'Meddelelser';
$_LANG['admin.configuration.content.step2.command_hint'] = 'Kør dette på serveren (SSH, som filens ejer eller root), og genindlæs derefter denne side:';

$_LANG['admin.settings.index.forms.ssl_settings_form.terminateUnpaidSubscriptions'] = 'Afslut ubetalte abonnementer';
$_LANG['admin.settings.index.forms.ssl_settings_form.terminateUnpaidSubscriptions_description'] = 'Er fornyelsesfakturaen stadig ubetalt efter forfaldsdatoen, opsiges abonnementet hos leverandøren, før det fornyes, og tjenesten afsluttes. Fornyelsesfakturaen forfalder dagen før abonnementet fornyes.';
$_LANG['admin.settings.index.forms.ssl_settings_form.subscriptionSlotPricing'] = 'Pris for "Køb flere domæner"';
$_LANG['admin.settings.index.forms.ssl_settings_form.subscriptionSlotPricing_description'] = 'Pris for domæner (pladser), som en kunde køber i abonnementsperioden. Forholdsmæssig: årsprisen for de dage, der er tilbage til næste fornyelse. Fuld pris: årsprisen, uanset hvornår de købes. Fornyelsen opkræver altid den fulde årspris for alle domæner.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.subscriptionSlotPricing_prorated'] = 'Forholdsmæssigt til næste fornyelse';
$_LANG['admin.settings.index.providers.ssl_settings_provider.subscriptionSlotPricing_full'] = 'Fuld årspris';

// renewal invoices and renewals (1.5.0)
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpiry_description'] = 'Fornyelsesfakturaen oprettes så mange dage før ordren udløber - aldrig efter certifikatets eget udløb. Højst 30: en CA overfører ikke mere end 30 dage af det gamle certifikat, så en kunde, der fornyede tidligere, ville miste forskellen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTerm'] = 'Betalingsfrist (dage)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTerm_description'] = 'Fornyelsesfakturaen forfalder så mange dage efter, at den er oprettet.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpirySubscription_description'] = 'Fornyelsesfakturaen oprettes så mange dage før leverandøren fornyer abonnementet (højst 30).';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTermSubscription'] = 'Betalingsfrist (dage)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTermSubscription_description'] = 'Fornyelsesfakturaen forfalder så mange dage efter, at den er oprettet. En ubetalt fornyelse annulleres hos leverandøren dagen før den ville blive fornyet, så kunden beholder det betalte år, og du bliver ikke opkrævet for det næste.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewOrderViaExistingOrder_description'] = 'Til: en betalt fornyelse forbliver på den samme WHMCS-tjeneste, som får det nye certifikat (det udløbende kan downloades, indtil det udløber) - én tjeneste pr. domæne. Fra: fornyelsen får en ny WHMCS-tjeneste, og den udløbende lukkes. Faktureringen er den samme i begge tilfælde.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewReuseOrderDetails'] = 'Forny med de samme ordreoplysninger';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewReuseOrderDetails_description'] = 'Til: en betalt fornyelse sendes straks til leverandøren med den tidligere CSR, domæner og kontakter, og kunden får en e-mail om automatisk fornyelse. Fra: kunden bliver bedt om at indsende dem igen via e-mail. Gælder alle produkter. Afviser leverandøren de tidligere oplysninger, bliver kunden bedt om nye.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckCertificateBefore'] = 'Faktura oprettet :lead dage før ordren udløber og forfalder :term dage senere: :days dage før udløb.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckCertificateOn'] = 'Faktura oprettet :lead dage før ordren udløber og forfalder :term dage senere: på dagen, hvor ordren udløber.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckCertificateAfter'] = 'Faktura oprettet :lead dage før ordren udløber og forfalder :term dage senere: :days dage efter udløb.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckSubscriptionBefore'] = 'Faktura oprettet :lead dage før abonnementet fornyes og forfalder :term dage senere: :days dage før fornyelsen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckSubscriptionOn'] = 'Faktura oprettet :lead dage før abonnementet fornyes og forfalder :term dage senere: på fornyelsesdagen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckSubscriptionAfter'] = 'Faktura oprettet :lead dage før abonnementet fornyes og forfalder :term dage senere: :days dage efter fornyelsen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalDueAfterCertificateEnds'] = 'Fakturaen forfalder :days dage efter, at ordren udløber. En kunde, der betaler på forfaldsdagen, fornyer efter certifikatet er udløbet, så webstedet kører uden gyldigt certifikat i op til :days dage. Det koster dig intet: fornyelsen bestilles først, når den er betalt. Anbefalet: en betalingsfrist på højst :safe dage.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalDueDayBeforeRenewal'] = 'Fakturaen forfalder dagen før abonnementet fornyes. En ubetalt fornyelse annulleres så først på selve fornyelsesdagen, hvilket kan være for sent til at forhindre leverandøren i at forny og opkræve dig. Anbefalet: en betalingsfrist på højst :safe dage.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalDueAfterSubscriptionRenews'] = 'Risiko: fakturaen forfalder på eller efter den dag, hvor leverandøren fornyer abonnementet. Du bliver opkrævet for det næste år, før din kunde har betalt, og en ubetalt fornyelse kan først annulleres derefter. Anbefalet: en betalingsfrist på højst :safe dage.';
