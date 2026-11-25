<?php

// main menu
$_LANG['admin.menu.home'] = 'Dashboard';
$_LANG['admin.menu.products'] = 'Produkte';
$_LANG['admin.menu.import'] = 'Import';
$_LANG['admin.menu.statistics'] = 'Statistiken';
$_LANG['admin.menu.reports'] = 'Berichte';
$_LANG['admin.menu.pricing'] = 'Preise';
$_LANG['admin.menu.settings'] = 'Einstellungen';
$_LANG['admin.menu.automationQueue'] = 'Automatisierungswarteschlange';
$_LANG['admin.menu.migration'] = 'Migration';
$_LANG['admin.menu.more'] = 'Mehr';
$_LANG['admin.breadcrumbs.Migration_index'] = 'Migration';

$_LANG['admin.home.index.home_container.title'] = 'Dashboard';
$_LANG['admin.home.index.home_container.attention_title'] = 'Die Automatisierung erfordert Aufmerksamkeit';
$_LANG['admin.home.index.home_container.attention_body'] = 'Die Cron-Jobs laufen nicht. Die Zertifikatsverarbeitung und Preisaktualisierungen können beeinträchtigt sein.';
$_LANG['admin.home.index.home_container.attention_button'] = 'System prüfen';
$_LANG['admin.home.index.home_container.kpi_sales'] = 'Gesamtumsatz';
$_LANG['admin.home.index.home_container.kpi_sales_detail'] = 'Bezahlte Umsätze · :currency';
$_LANG['admin.home.index.home_container.kpi_profit'] = 'Bruttogewinn';
$_LANG['admin.home.index.home_container.kpi_profit_detail'] = ':period · :currency';
$_LANG['admin.home.index.home_container.kpi_issued'] = 'Ausgestellte Zertifikate';
$_LANG['admin.home.index.home_container.kpi_issued_detail'] = ':period';
$_LANG['admin.home.index.home_container.kpi_expiring'] = 'Ablaufende Zertifikate';
$_LANG['admin.home.index.home_container.kpi_expiring_detail'] = 'In den nächsten 90 Tagen';
$_LANG['admin.home.index.home_container.version'] = 'Modulversion :version';

$_LANG['admin.home.index.widgets.overview_widget.title'] = 'Umsatzübersicht';
$_LANG['admin.home.index.widgets.overview_widget.statistics'] = 'Alle Statistiken';
$_LANG['admin.home.index.widgets.overview_widget.paid'] = 'Bezahlt';
$_LANG['admin.home.index.widgets.overview_widget.unpaid'] = 'Unbezahlt';
$_LANG['admin.home.index.widgets.overview_widget.overdue'] = 'Überfällig';
$_LANG['admin.home.index.period_month'] = 'Letzter Monat';
$_LANG['admin.home.index.period_90days'] = 'Letzte 90 Tage';
$_LANG['admin.home.index.period_year'] = 'Letztes Jahr';
$_LANG['admin.home.index.period_total'] = 'Gesamt';

$_LANG['admin.home.index.widgets.announcements_widget.title'] = 'Ankündigungen';
$_LANG['admin.home.index.widgets.announcements_widget.latest'] = 'Neueste Updates';
$_LANG['admin.home.index.widgets.announcements_widget.none'] = 'Keine Ankündigungen.';
$_LANG['admin.home.index.widgets.announcements_widget.unavailable'] = 'Ankündigungen konnten nicht geladen werden: :error';

$_LANG['admin.home.index.widgets.support_widget.title'] = 'Support-Unterstützung';
$_LANG['admin.home.index.widgets.support_widget.phone'] = 'Telefon: :phone';
$_LANG['admin.home.index.widgets.support_widget.contact'] = 'Support kontaktieren';
$_LANG['admin.home.index.widgets.support_widget.contact_vendor'] = ':vendor-Support kontaktieren';

$_LANG['admin.home.index.widgets.system_check_widget.title'] = 'Systemzustand';
$_LANG['admin.home.index.widgets.system_check_widget.ok'] = 'Alles läuft';
$_LANG['admin.home.index.widgets.system_check_widget.attention'] = 'Handlungsbedarf';
$_LANG['admin.home.index.widgets.system_check_widget.tasks'] = 'Geplante Aufgaben';
$_LANG['admin.home.index.widgets.system_check_widget.never'] = 'Nie';
$_LANG['admin.home.index.widgets.system_check_widget.error'] = 'Fehler';
$_LANG['admin.home.index.widgets.system_check_widget.review'] = 'Cron-Konfiguration prüfen';
$_LANG['admin.home.index.widgets.system_check_widget.gateway'] = 'Verbindung zur %%VENDOR%%-API';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_ok'] = 'Verbunden (:ms ms)';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_error'] = 'Nicht erreichbar';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_recheck'] = 'Verbindung erneut prüfen';
$_LANG['admin.home.index.widgets.system_check_widget.moduleLog'] = 'WHMCS Module Log';
$_LANG['admin.home.index.widgets.system_check_widget.moduleLog_off'] = 'In WHMCS aus';
$_LANG['admin.home.index.widgets.system_check_widget.moduleLog_off_title'] = 'Das Modul sendet seine Anfragen an das WHMCS Module Log, aber dieses Protokoll ist in WHMCS ausgeschaltet, daher wird nichts aufgezeichnet. Klicken Sie, um es unter Einstellungen > Protokolle einzuschalten.';

$_LANG['admin.home.index.widgets.migration_widget.title'] = 'Migration des Altmoduls';
$_LANG['admin.home.index.widgets.migration_widget.intro'] = 'Übertragen Sie Ihre bestehenden SSL-Services in dieses Modul.';
$_LANG['admin.home.index.widgets.migration_widget.services'] = ':n aktive Services';
$_LANG['admin.home.index.widgets.migration_widget.choice_migrate'] = 'wird migriert';
$_LANG['admin.home.index.widgets.migration_widget.choice_skip'] = 'bleibt eigenständig';
$_LANG['admin.home.index.widgets.migration_widget.last_run'] = 'Migration #:id · :status';
$_LANG['admin.home.index.widgets.migration_widget.last_run_counts'] = ':done migriert, :failed fehlgeschlagen, :skipped übersprungen';
$_LANG['admin.home.index.widgets.migration_widget.backups_until'] = 'Backups werden bis :date aufbewahrt';
$_LANG['admin.home.index.widgets.migration_widget.open'] = 'Migration öffnen';




$_LANG['admin.migration.title'] = 'Migration des Altmoduls';
$_LANG['admin.migration.none_found'] = 'Auf dieser Installation wurde kein altes GoGetSSL- oder TheSSLStore-Modul gefunden.';
$_LANG['admin.migration.card_products'] = 'Produkte';
$_LANG['admin.migration.card_products_sub'] = ':hidden ausgeblendet, :retired eingestellt';
$_LANG['admin.migration.card_services'] = 'Aktive Services';
$_LANG['admin.migration.card_orders'] = 'SSL-Bestellungen';
$_LANG['admin.migration.card_subscriptions'] = 'Abonnements';
$_LANG['admin.migration.card_autoinstall'] = 'AutoInstall (cPanel)';
$_LANG['admin.migration.card_queue_open'] = ':n offene Aufgaben';
$_LANG['admin.migration.card_unconfigured'] = 'Nicht konfiguriert';
$_LANG['admin.migration.card_unconfigured_sub'] = ':pre beim Anbieter vorbestellt, :unpaid unbezahlt';
$_LANG['admin.migration.support_full'] = 'Migration wird unterstützt.';
$_LANG['admin.migration.support_partial'] = 'Migration wird mit Einschränkungen unterstützt.';
$_LANG['admin.migration.support_unsupported'] = 'Diese Altversion kann nicht migriert werden.';
$_LANG['admin.migration.files_missing'] = 'Die Dateien des Altmoduls befinden sich nicht mehr auf dem Server (nur noch Daten): Ein Rollback stellt die Daten wieder her, aber kein funktionierendes Altmodul.';
$_LANG['admin.migration.unsupported_found'] = 'Von diesem Modul nicht unterstützte Produkttypen (ihre Zertifikate werden als Aufzeichnung behalten):';
$_LANG['admin.migration.other_modules'] = 'Weitere vorhandene SSL-Module (unverändert):';
$_LANG['admin.migration.choice_current'] = 'Entscheidung aus dem Einrichtungsassistenten:';
$_LANG['admin.migration.choice_migrate'] = 'migrieren';
$_LANG['admin.migration.choice_skip'] = 'eigenständig installieren (überspringen)';
$_LANG['admin.migration.choice_none'] = 'noch keine';
$_LANG['admin.migration.btn_report'] = 'Testlauf-Bericht herunterladen (CSV)';
$_LANG['admin.migration.btn_start'] = 'Migration starten';
$_LANG['admin.migration.btn_skip'] = 'Überspringen (eigenständig belassen)';
$_LANG['admin.migration.credentials_already_set'] = 'API-Zugangsdaten: Dieses Modul hat eigene.';
$_LANG['admin.migration.credentials_available'] = 'API-Zugangsdaten: Werden aus dem Altmodul kopiert, falls sie beim Start der Migration noch leer sind.';
$_LANG['admin.migration.credentials_missing'] = 'API-Zugangsdaten: Weder in diesem Modul noch im Altmodul vorhanden; geben Sie sie vor der Migration unter Einstellungen ein.';
$_LANG['admin.migration.start_defaults'] = 'Migration starten: Zuerst werden Backup-Tabellen erstellt; private AutoInstall-Schlüssel werden von cPanel abgerufen (TheSSLStore); das alte Addon wird nach dem Wechsel deaktiviert; alte Protokolltabellen sind im Backup enthalten.';
$_LANG['admin.migration.btn_start_nokeys'] = 'Starten, ohne cPanel-Schlüssel abzurufen';
$_LANG['admin.migration.btn_start_keeplegacy'] = 'Starten und das alte Addon aktiv lassen';
$_LANG['admin.migration.confirm_start'] = 'Migration jetzt starten? Zuerst werden Backup-Tabellen erstellt. Konfigurieren Sie während des Laufs keine SSL-Services.';
$_LANG['admin.migration.unsupported_version_hint'] = 'Aktualisieren Sie das Altmodul auf die neueste Version und laden Sie diese Seite neu, oder belassen Sie es als eigenständiges Modul.';
$_LANG['admin.migration.run_title'] = 'Migration #:id (:source)';
$_LANG['admin.migration.run_stale'] = 'Seit mehr als 10 Minuten kein Fortschritt. Der Lauf wird fortgesetzt, solange diese Seite geöffnet bleibt; oder setzen Sie ihn zurück.';
$_LANG['admin.migration.verify_result'] = 'Überprüfung: :checked migrierte Services geprüft, :active aktive SSL-Services mit vollständigen Daten, :left verbleibende Bestellzeilen des Altmoduls.';
$_LANG['admin.migration.exceptions'] = ':n nicht migrierte Dienste oder Produkte (mit Grund)';
$_LANG['admin.migration.col_object'] = 'Objekt';
$_LANG['admin.migration.col_status'] = 'Status';
$_LANG['admin.migration.col_reason'] = 'Grund';
$_LANG['admin.migration.cron_title'] = 'Cron-Jobs';
$_LANG['admin.migration.cron_remove'] = 'Entfernen Sie diese alten Cron-Zeilen vom Server:';
$_LANG['admin.migration.cron_add'] = 'Stellen Sie sicher, dass diese Cron-Zeilen dieses Moduls vorhanden sind:';
$_LANG['admin.migration.legacy_deactivated'] = 'Altes Addon deaktiviert: :result';
$_LANG['admin.migration.yes'] = 'ja';
$_LANG['admin.migration.no'] = 'nein (deaktivieren Sie es manuell unter Setup > Addon-Module)';
$_LANG['admin.migration.btn_log'] = 'Migrationsprotokoll herunterladen (CSV)';
$_LANG['admin.migration.backups_until'] = 'Backups werden bis :date aufbewahrt';
$_LANG['admin.migration.backups_deleted'] = 'Backups gelöscht; ein Rollback ist nicht mehr möglich.';
$_LANG['admin.migration.btn_extend'] = 'Backups 14 weitere Tage aufbewahren';
$_LANG['admin.migration.btn_delete_backups'] = 'Backups jetzt löschen';
$_LANG['admin.migration.btn_rollback'] = 'Diese Migration zurücksetzen';
$_LANG['admin.migration.confirm_delete'] = 'Backup-Tabellen löschen? Danach ist kein Rollback mehr möglich.';
$_LANG['admin.migration.confirm_rollback'] = 'Zurücksetzen? Produkte, Bestellungen und Einstellungen werden aus den Backups wiederhergestellt und das alte Addon wird reaktiviert. Seit der Migration in diesem Modul verkaufte Services bleiben erhalten.';
$_LANG['admin.migration.runner_done'] = 'migriert';
$_LANG['admin.migration.runner_skipped'] = 'übersprungen';
$_LANG['admin.migration.runner_failed'] = 'fehlgeschlagen';
$_LANG['admin.migration.runner_pending'] = 'ausstehend';
$_LANG['admin.migration.runner_keep_open'] = 'Läuft. Lassen Sie diese Seite geöffnet, bis der Vorgang abgeschlossen ist.';
$_LANG['admin.migration.runner_finished'] = 'Abgeschlossen.';
$_LANG['admin.migration.runner_reload'] = 'Neu laden, um das Ergebnis zu sehen';
$_LANG['admin.migration.state_preparing'] = 'Vorbereitung (Backups)';
$_LANG['admin.migration.state_running'] = 'Services werden migriert';
$_LANG['admin.migration.state_switching'] = 'Produkte und Bestellungen werden umgestellt';
$_LANG['admin.migration.state_completed'] = 'Abgeschlossen';
$_LANG['admin.migration.state_failed'] = 'Fehlgeschlagen';
$_LANG['admin.migration.state_rolled_back'] = 'Zurückgesetzt';

$_LANG['admin.breadcrumbs.Configuration'] = 'Modulinstallation';
$_LANG['admin.menu.configuration'] = 'Modulinstallation';
$_LANG['admin.breadcrumbs.Configuration_step1'] = 'Schritt 1';
$_LANG['admin.breadcrumbs.Configuration_step2'] = 'Schritt 2';
$_LANG['admin.breadcrumbs.Configuration_step3'] = 'Schritt 3';
$_LANG['admin.breadcrumbs.Configuration_step4'] = 'Schritt 4';
$_LANG['admin.breadcrumbs.Configuration_step5'] = 'Schritt 5';
$_LANG['admin.breadcrumbs.Configuration_step6'] = 'Schritt 6';
$_LANG['admin.breadcrumbs.Configuration_step7'] = 'Schritt 7';

$_LANG['instalation.wizard.installation_steps'] = 'Installationsschritte';
$_LANG['instalation.wizard.module_installation'] = 'Modulinstallation';
$_LANG['instalation.wizard.step_of'] = 'Schritt %d von %d';
$_LANG['instalation.wizard.step1_name'] = 'Willkommen';
$_LANG['instalation.wizard.step1_desc'] = 'Einführung';
$_LANG['instalation.wizard.step2_name'] = 'Kompatibilitätsprüfung';
$_LANG['instalation.wizard.step2_desc'] = 'Systemanforderungen';
$_LANG['instalation.wizard.step3_name'] = 'Altmodule';
$_LANG['instalation.wizard.step3_desc'] = 'Bestehende Daten migrieren';
$_LANG['instalation.wizard.step4_name'] = 'API-Verbindung';
$_LANG['instalation.wizard.step4_desc'] = 'Zugangsdaten';
$_LANG['instalation.wizard.step5_name'] = 'Produkte auswählen';
$_LANG['instalation.wizard.step5_desc'] = 'Katalog importieren';
$_LANG['instalation.wizard.step6_name'] = 'Abschluss';
$_LANG['instalation.wizard.step6_desc'] = 'Prüfen und beenden';

$_LANG['admin.configuration.content.step1.feature1_title'] = 'Produkte importieren';
$_LANG['admin.configuration.content.step1.feature1_desc'] = 'SSL-Produkte im Bulk anlegen';
$_LANG['admin.configuration.content.step1.feature2_title'] = 'Automatische Preise';
$_LANG['admin.configuration.content.step1.feature2_desc'] = 'Mit Anbieterpreisen synchronisieren';
$_LANG['admin.configuration.content.step1.feature3_title'] = 'Automatisierte Ausstellung';
$_LANG['admin.configuration.content.step1.feature3_desc'] = 'Bestellungen werden per API verarbeitet';

$_LANG['admin.configuration.content.step1.step1_title'] = 'SCHRITT 1. Willkommen';
$_LANG['admin.configuration.content.step1.first_p'] = 'Vielen Dank für die Installation von <b>:moduleName</b>!';
$_LANG['admin.configuration.content.step1.second_p'] = 'Dieses Addon unterstützt Sie beim Import von Produkten wie SSL-Zertifikaten und weiteren Sicherheitsangeboten in Ihr WHMCS. Sie benötigen lediglich ein Konto und können sofort mit dem Verkauf dieser Produkte beginnen.';
$_LANG['admin.configuration.content.step1.third_p'] = 'Klicken Sie auf die Schaltfläche <b>"Weiter"</b>, damit wir prüfen können, ob Ihre WHMCS-Installation kompatibel ist.';
$_LANG['admin.configuration.content.step1.next_step_btn'] = 'Nächster Schritt';

$_LANG['admin.configuration.content.step2.step2_title'] = 'SCHRITT 2. Systemkompatibilitätsprüfung';
$_LANG['admin.configuration.content.step2.intro_passed'] = 'Ihr Server erfüllt die Anforderungen. Warnungen verhindern die Installation nicht, sollten aber behoben werden.';
$_LANG['admin.configuration.content.step2.intro_failed'] = 'Einige Anforderungen sind nicht erfüllt. Beheben Sie die fehlgeschlagenen Punkte und laden Sie diese Seite neu, um fortzufahren.';
$_LANG['admin.configuration.content.step2.col_check'] = 'Prüfung';
$_LANG['admin.configuration.content.step2.col_required'] = 'Erforderlich';
$_LANG['admin.configuration.content.step2.col_found'] = 'Gefunden';
$_LANG['admin.configuration.content.step2.col_status'] = 'Status';
$_LANG['admin.configuration.content.step2.status_passed'] = 'Bestanden';
$_LANG['admin.configuration.content.step2.status_failed'] = 'Fehlgeschlagen';
$_LANG['admin.configuration.content.step2.status_warning'] = 'Warnung';
$_LANG['admin.configuration.content.step2.check_php'] = 'PHP-Version';
$_LANG['admin.configuration.content.step2.hint_php'] = 'Aktualisieren Sie PHP für WHMCS. Die von den Cron-Jobs verwendete PHP-Version muss die Anforderung ebenfalls erfüllen.';
$_LANG['admin.configuration.content.step2.check_whmcs'] = 'WHMCS-Version';
$_LANG['admin.configuration.content.step2.hint_whmcs'] = 'Aktualisieren Sie WHMCS auf eine unterstützte Version.';
$_LANG['admin.configuration.content.step2.check_ext_curl'] = 'PHP-Erweiterung: cURL';
$_LANG['admin.configuration.content.step2.hint_ext_curl'] = 'Wird für alle API-Anfragen benötigt. Installieren oder aktivieren Sie php-curl.';
$_LANG['admin.configuration.content.step2.check_ext_openssl'] = 'PHP-Erweiterung: OpenSSL';
$_LANG['admin.configuration.content.step2.hint_ext_openssl'] = 'Wird zum Erzeugen von CSRs und privaten Schlüsseln benötigt. Installieren oder aktivieren Sie php-openssl.';
$_LANG['admin.configuration.content.step2.check_ext_json'] = 'PHP-Erweiterung: JSON';
$_LANG['admin.configuration.content.step2.hint_ext_json'] = 'Wird für API-Anfragen benötigt. Installieren oder aktivieren Sie php-json.';
$_LANG['admin.configuration.content.step2.check_ext_mbstring'] = 'PHP-Erweiterung: mbstring';
$_LANG['admin.configuration.content.step2.hint_ext_mbstring'] = 'Installieren oder aktivieren Sie php-mbstring.';
$_LANG['admin.configuration.content.step2.check_ext_pdo_mysql'] = 'PHP-Erweiterung: PDO MySQL';
$_LANG['admin.configuration.content.step2.hint_ext_pdo_mysql'] = 'Installieren oder aktivieren Sie php-mysql (pdo_mysql).';
$_LANG['admin.configuration.content.step2.check_storage'] = 'Speicherordner';
$_LANG['admin.configuration.content.step2.hint_storage'] = 'Der Webserver-Benutzer muss in modules/addons/TSSGGSModule/storage und dessen Unterordnern app, crons und logs schreiben können.';
$_LANG['admin.configuration.content.step2.check_gateway'] = 'Verbindung zur %%VENDOR%%-API';
$_LANG['admin.configuration.content.step2.hint_gateway'] = 'Der Server konnte :subject nicht über HTTPS erreichen. Erlauben Sie ausgehendes HTTPS (Port 443) zu dieser Adresse; die API-Verbindung wird in Schritt 4 erneut getestet.';
$_LANG['admin.configuration.content.step2.next_step_btn'] = 'Nächster Schritt';

$_LANG['admin.configuration.content.step3.step3_title'] = 'SCHRITT 3. Altmodule gefunden';
$_LANG['admin.configuration.content.step3.intro_one'] = 'Auf diesem WHMCS ist ein älteres :modules-Modul installiert. Dessen Produkte, Bestellungen, Abonnements und AutoInstall-Zertifikate können in dieses Modul übertragen werden, damit Verlängerungen, Neuausstellungen und E-Mails hier weiterhin funktionieren.';
$_LANG['admin.configuration.content.step3.intro_many'] = 'Auf diesem WHMCS sind ältere :modules-Module installiert. Deren Produkte, Bestellungen, Abonnements und AutoInstall-Zertifikate können in dieses Modul übertragen werden, damit Verlängerungen, Neuausstellungen und E-Mails hier weiterhin funktionieren. Wählen Sie pro Modul:';
$_LANG['admin.configuration.content.step3.and'] = 'und';
$_LANG['admin.configuration.content.step3.version_unknown'] = 'Version unbekannt';
$_LANG['admin.configuration.content.step3.support_full'] = 'Migration wird unterstützt';
$_LANG['admin.configuration.content.step3.support_partial'] = 'Migration wird (teilweise) unterstützt';
$_LANG['admin.configuration.content.step3.support_unsupported'] = 'Version zu alt';
$_LANG['admin.configuration.content.step3.fact_products'] = ':n Produkte';
$_LANG['admin.configuration.content.step3.fact_services'] = ':active aktive Services (:total insgesamt)';
$_LANG['admin.configuration.content.step3.fact_orders'] = ':n SSL-Bestellungen, :unconfigured noch nicht konfiguriert, :unpaid unbezahlt';
$_LANG['admin.configuration.content.step3.fact_subscriptions'] = ':acme ACME- und :ais AutoInstall-SSL-Abonnements, :autoinstall über cPanel installierte Zertifikate';
$_LANG['admin.configuration.content.step3.unsupported_hint'] = 'Aktualisieren Sie zuerst das Altmodul auf die neueste Version und starten Sie die Migration dann auf der Seite Migration, oder installieren Sie dieses Modul als neues eigenständiges Modul.';
$_LANG['admin.configuration.content.step3.runs_later'] = 'Es wird noch nichts geändert. Die Migration startet im Schritt Abschluss, nachdem Ihre API-Zugangsdaten hinterlegt sind.';
$_LANG['admin.configuration.content.step3.next_step_btn'] = 'Nächster Schritt';

//$_LANG['admin.configuration.content.step3.step3_title']   = 'STEP 4. Vendor selection';
//$_LANG['admin.configuration.content.step3.next_step_btn'] = 'Next step';

$_LANG['admin.configuration.content.step4.step4_title'] = 'SCHRITT 4. Mit der API verbunden';
$_LANG['admin.configuration.content.step4.next_step_btn'] = 'Nächster Schritt';
$_LANG['admin.configuration.content.step4.credentials_prefilled'] = 'Die API-Zugangsdaten wurden aus dem :modules-Modul kopiert. Testen Sie die Verbindung und speichern Sie, oder ersetzen Sie die Zugangsdaten.';

$_LANG['admin.configuration.content.step5.step5_title'] = 'SCHRITT 5. Produkte auswählen';
$_LANG['admin.configuration.content.step5.next_step_btn'] = 'Nächster Schritt';

$_LANG['admin.configuration.content.step6.step6_title'] = 'SCHRITT 6. Abschluss';
$_LANG['admin.configuration.content.step6.finalize'] = 'Abschließen';


$_LANG['admin.configuration.shared.providers.finalize_provider.successFinalize'] = 'Die Konfiguration wurde erfolgreich gespeichert';
$_LANG['admin.settings.index.forms.settings_form.next_step'] = 'Nächster Schritt';
$_LANG['admin.settings.index.providers.settings_provider.successAPI'] = 'Die API-Konfiguration wurde erfolgreich gespeichert';

$_LANG['admin.configuration.shared.forms.finalize_form.finalize'] = 'Abschließen';

$_LANG['admin.configuration.content.step6.title_list'] = 'Nächste Schritte';
$_LANG['admin.configuration.content.step6.item_list1'] = '1. Öffnen Sie den Bereich <b>Preise</b>, um die Produkte zu aktivieren, die Sie weiterverkaufen möchten';
$_LANG['admin.configuration.content.step6.item_list2'] = '2. Richten Sie die <b>Verlängerungen</b> unter Einstellungen > SSL-Einstellungen (Verlängerungen) und Einstellungen > Automatisiertes SSL (Abonnements) ein';

$_LANG['admin.configuration.content.step6.title_alert'] = '<b>Glückwunsch!</b> Sie haben die Modulinstallation erfolgreich abgeschlossen';
$_LANG['admin.configuration.content.step6.step6_api'] = '<b>API-Konfiguration</b> abgeschlossen und in Betrieb';
$_LANG['admin.configuration.content.step6.step6_products'] = '<b>%s Produkte importiert</b> und bereit für die finale Einrichtung';


//===[Dashboard]========================================================================================================

$_LANG['admin.breadcrumbs.Home'] = 'Dashboard';
//update
//overview


//reporting
$_LANG['admin.home.index.modals.reporting_modal.sendReport'] = 'Bericht senden';
$_LANG['admin.home.index.forms.reporting_form.sendTo'] = 'Gesendet an';
$_LANG['admin.home.index.forms.reporting_form.sendTo_description'] = 'Der Bericht wird per E-Mail an diese Support-Adresse gesendet. Antworten gehen an Ihre Administrator-E-Mail.';
$_LANG['admin.home.index.providers.reporting_provider.reportSent'] = 'Der Bericht wurde an :address gesendet. Der Support antwortet an Ihre Administrator-E-Mail.';
$_LANG['admin.home.index.forms.reporting_form.whmcsUrl'] = 'WHMCS-URL';
$_LANG['admin.home.index.forms.reporting_form.moduleVersion'] = 'Modulversion';
$_LANG['admin.home.index.forms.reporting_form.tssPartnerId'] = 'TheSSLSTORE-Partner-ID';
$_LANG['admin.home.index.forms.reporting_form.ggsPartnerId'] = 'GoGetSSL-Partner-ID';
$_LANG['admin.home.index.forms.reporting_form.phpVersion'] = 'PHP-Version';
$_LANG['admin.home.index.forms.reporting_form.productionMode'] = 'Produktivmodus';
$_LANG['admin.home.index.forms.reporting_form.cronStatus'] = 'CRON-Status';
$_LANG['admin.home.index.forms.reporting_form.fromDate'] = 'Von Datum';
$_LANG['admin.home.index.forms.reporting_form.toDate'] = 'Bis Datum';
$_LANG['admin.home.index.forms.reporting_form.noModifiedFiles'] = 'Glückwunsch! Keine geänderten Dateien';
$_LANG['admin.home.index.forms.reporting_form.modifiedFile'] = 'Achtung! Wir haben 1 geänderte Datei gefunden';
$_LANG['admin.home.index.forms.reporting_form.modifiedFiles'] = 'Achtung! Wir haben :modifiedCount geänderte Dateien gefunden';

$_LANG['admin.home.index.forms.reporting_form.issueDate'] = 'Datum des Problems';
$_LANG['admin.home.index.forms.reporting_form.modifiedModuleFiles'] = 'Geänderte Moduldateien';
$_LANG['admin.home.index.forms.reporting_form.issueDetails'] = 'Problemdetails';


$_LANG['admin.home.index.forms.reporting_form.send'] = 'Senden';
$_LANG['admin.home.index.forms.reporting_form.download'] = 'Protokolldatei herunterladen';
$_LANG['admin.home.index.forms.reporting_form.cancel'] = 'Abbrechen';

$_LANG['admin.home.index.providers.reporting_provider.logDownloadedSuccessfully'] = 'Erfolgreich';

$_LANG['admin.home.index.forms.reporting_form.send_success'] = 'Ihr Aktivitätsprotokoll wurde erfolgreich an unser Team übermittelt! Wir melden uns in Kürze mit weiteren Informationen zu Ihrem Problem bei Ihnen.';


//system check

//Announcements

//manager

//support


//assistance


//systemcheck

//===[Statistics]=======================================================================================================

$_LANG['admin.breadcrumbs.Statistics'] = 'Statistiken';
$_LANG['admin.statistics.index.widgets.total_income_widget.title'] = 'Gesamteinnahmen';
$_LANG['admin.statistics.index.widgets.product_income_widget.title'] = 'Produkteinnahmen (Top 5)';
$_LANG['admin.statistics.index.widgets.resellers_widget.title'] = 'Profitabelste Reseller';
$_LANG['admin.statistics.index.widgets.invoices_widget.title'] = 'Neueste Rechnungen';

$_LANG['admin.statistics.index.graphs.total_income_graph.IncomeGraphSetTitle'] = 'Einnahmen :currencySymbol (letzte sechs Monate)';

$_LANG['admin.statistics.index.widgets.resellers_widget.clientId'] = 'ID';
$_LANG['admin.statistics.index.widgets.resellers_widget.client'] = 'Kunde';
$_LANG['admin.statistics.index.widgets.resellers_widget.paid'] = 'Bezahlt';

$_LANG['admin.statistics.index.widgets.invoices_widget.invoiceId'] = 'ID';
$_LANG['admin.statistics.index.widgets.invoices_widget.client'] = 'Kunde';
$_LANG['admin.statistics.index.widgets.invoices_widget.status'] = 'Status';
$_LANG['admin.statistics.index.widgets.invoices_widget.amount'] = 'Betrag';
$_LANG['admin.statistics.index.widgets.invoices_widget.date'] = 'Datum';


//===[Import]===========================================================================================================
$_LANG['admin.breadcrumbs.Import'] = 'Import';
$_LANG['admin.import.index.import_container.single'] = 'Einzelne Bestellung';
$_LANG['admin.import.index.import_container.csv'] = 'Aus CSV';


$_LANG['admin.import.index.forms.import_form.remoteOrderId'] = 'Bestell-ID';
$_LANG['admin.import.index.forms.import_form.clientId'] = 'Kunde';
$_LANG['admin.import.index.forms.import_form.payMethod'] = 'Zahlungsart';
$_LANG['admin.import.index.forms.import_form.vendor'] = 'Anbieter';
$_LANG['admin.import.index.forms.import_form.generateInvoice'] = 'Rechnung erstellen';
$_LANG['admin.import.index.forms.import_form.import'] = 'Importieren';
$_LANG['admin.import.index.forms.import_form.create_success'] = 'Import erfolgreich abgeschlossen';
$_LANG['admin.import.index.forms.import_form.productImportErrors'] = 'Beim Import sind Fehler aufgetreten, Details finden Sie in den Protokollen.';

$_LANG['admin.import.index.forms.import_form_csv.csv'] = 'CSV';
$_LANG['admin.import.index.forms.import_form_csv.vendor'] = 'Anbieter';
$_LANG['admin.import.index.forms.import_form_csv.generateInvoice'] = 'Rechnung erstellen';
$_LANG['admin.import.index.forms.import_form_csv.import'] = 'Importieren';
$_LANG['admin.import.index.forms.import_form_csv.create_success'] = 'Import erfolgreich abgeschlossen';
$_LANG['admin.import.index.forms.import_form_csv.productImportErrors'] = 'Beim Import sind Fehler aufgetreten, Details finden Sie in den Protokollen.';

$_LANG['admin.import.index.forms.template_download_form.download'] = 'CSV-Vorlage herunterladen';
$_LANG['admin.import.index.providers.template_download_provider.success'] = 'Erfolgreich';


//===[Reports]==========================================================================================================

$_LANG['admin.breadcrumbs.Reports'] = 'Berichte';

//tabs
$_LANG['admin.reports.index.reports_container.renewalReport'] = 'Verlängerungsbericht';
$_LANG['admin.reports.index.reports_container.datewiseSalesReport'] = 'Umsatzbericht nach Datum';
$_LANG['admin.reports.index.reports_container.profitLossReport'] = 'Gewinn- und Verlustbericht';

//renewal datatable
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.date'] = 'Datum';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.storeId'] = 'Shop-ID';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.clientDetails'] = 'Kundendetails';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.productDetails'] = 'Produktdetails';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.type'] = 'Typ';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.brand'] = 'Marke';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.status'] = 'Status';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.issueDate'] = 'Ausstellungsdatum';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.expirationDate'] = 'Ablaufdatum';
//renewal datatable filters
$_LANG['admin.reports.index.data_tables.renewal.forms.filter_form.renewalPeriod'] = 'Auswahl';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.all'] = 'Alle';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last30'] = 'Abgelaufen in den letzten 30 Tagen';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last60'] = 'Abgelaufen in den letzten 60 Tagen';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last90'] = 'Abgelaufen in den letzten 90 Tagen';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.lastAll'] = 'Alle abgelaufenen';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next30'] = 'Läuft in 30 Tagen ab';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next60'] = 'Läuft in 60 Tagen ab';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next90'] = 'Läuft in 90 Tagen ab';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.nextAll'] = 'Alle anstehenden Abläufe';
$_LANG['admin.reports.index.data_tables.renewal.forms.filter_form.search'] = 'Suchen';
//download button
$_LANG['admin.reports.index.data_tables.renewal.forms.download_form.downloadCsv'] = 'Als CSV exportieren';
$_LANG['admin.reports.index.data_tables.renewal.providers.download_provider.csvExportedSuccessfully'] = 'Erfolgreich exportiert';

//Datewise Sales datatable
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.date'] = 'Datum';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.storeId'] = 'Shop-ID';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.clientDetails'] = 'Kundendetails';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.productDetails'] = 'Produktdetails';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.type'] = 'Typ';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.brand'] = 'Marke';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.status'] = 'Status';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.salesAmount'] = 'Umsatzbetrag';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.cost'] = 'Kosten';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.payment'] = 'Zahlung';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.paymentStatus'] = 'Zahlungsstatus';
//Datewise Sales datatable filters
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.fromDate'] = 'Von Datum';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.toDate'] = 'Bis Datum';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.brand'] = 'Marke';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.productId'] = 'Produktname';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.sslStatus'] = 'SSL-Status';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.vendorOrderId'] = 'Anbieter-Bestell-ID';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.all'] = 'Alle';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.active'] = 'Aktiv';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.pending'] = 'Ausstehend';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.expired'] = 'Abgelaufen';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.cancelled'] = 'Storniert';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.search'] = 'Suchen';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.advancedSearch'] = 'Erweiterte Suche';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.basicSearch'] = 'Einfache Suche';
//download button
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.download_form.downloadCsv'] = 'Als CSV exportieren';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.download_provider.csvExportedSuccessfully'] = 'Erfolgreich exportiert';
//header
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.paid'] = 'Bezahlt:';
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.unpaid'] = 'Unbezahlt:';
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.overdue'] = 'Überfällig:';


//ProfitLoss Datatable
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.date'] = 'Datum';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.storeId'] = 'Shop-ID';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.clientDetails'] = 'Kundendetails';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.productDetails'] = 'Produktdetails';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.type'] = 'Typ';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.brand'] = 'Marke';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.status'] = 'Status';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.salesAmount'] = 'Umsatzbetrag';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.cost'] = 'Kosten';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.grossProfit'] = 'Bruttogewinn';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.payment'] = 'Zahlung';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.paymentStatus'] = 'Zahlungsstatus';
//ProfitLoss Datatable filters
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.fromDate'] = 'Von Datum';
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.toDate'] = 'Bis Datum';
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.search'] = 'Suchen';
//download button
$_LANG['admin.reports.index.data_tables.profit_loss.forms.download_form.downloadCsv'] = 'Als CSV exportieren';
$_LANG['admin.reports.index.data_tables.profit_loss.providers.download_provider.csvExportedSuccessfully'] = 'Erfolgreich exportiert';
//header
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.paid'] = 'Bezahlt:';
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.totalGrossProfit'] = 'Gesamter Bruttogewinn:';
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.certificateCount'] = 'Anzahl ausgestellter Zertifikate:';

//shared

$_LANG['admin.reports.index.data_tables.shared.records_provider.country'] = 'Land:';
$_LANG['admin.reports.index.data_tables.shared.records_provider.domain'] = 'Domain:';

//===[Settings]=========================================================================================================

$_LANG['admin.breadcrumbs.Settings'] = 'Einstellungen';
//sidebar

$_LANG['admin.settings.index.widgets.sidebar.title'] = 'Einstellungen';
$_LANG['admin.settings.index.widgets.sidebar.apiSettings'] = 'API-Einstellungen';
$_LANG['admin.settings.index.widgets.sidebar.sslSettings'] = 'SSL-Einstellungen';
$_LANG['admin.settings.index.widgets.sidebar.financeSettings'] = 'Finanzeinstellungen';
$_LANG['admin.settings.index.widgets.sidebar.cronSettings'] = 'CRON-Einstellungen';


//api settings

$_LANG['admin.settings.index.widgets.api_settings_widget.title'] = 'API-Einstellungen';
$_LANG['admin.settings.index.forms.api_settings_form.tss'] = 'API-Einstellungen von The SSL STORE';
$_LANG['admin.settings.index.forms.api_settings_form.ggs'] = 'API-Einstellungen von GoGetSSL';
$_LANG['admin.settings.index.forms.api_settings_form.tssLivePartnerCode'] = 'Live-Partnercode';
$_LANG['admin.settings.index.forms.api_settings_form.tssLiveAuthToken'] = 'Live-Auth-Token (leer lassen, um das gespeicherte zu behalten)';
$_LANG['admin.settings.index.forms.api_settings_form.ggsLivePartnerCode'] = 'Partnercode';
$_LANG['admin.settings.index.forms.api_settings_form.ggsLiveAuthToken'] = 'Auth-Token (leer lassen, um das gespeicherte zu behalten)';
$_LANG['admin.settings.index.forms.api_settings_form.tssTestPartnerCode'] = 'Sandbox-Partnercode';
$_LANG['admin.settings.index.forms.api_settings_form.tssTestAuthToken'] = 'Sandbox-Auth-Token (leer lassen, um das gespeicherte zu behalten)';
$_LANG['admin.settings.index.forms.api_settings_form.OperationMode'] = 'Betriebsmodus';
$_LANG['admin.settings.index.forms.api_settings_form.save'] = 'Speichern';
$_LANG['admin.settings.index.forms.api_settings_form.next_step'] = 'Nächster Schritt';
$_LANG['admin.settings.index.forms.api_settings_form.testConnection'] = 'Verbindung testen';
$_LANG['admin.settings.index.forms.api_settings_form.update_success'] = 'Einstellungen erfolgreich aktualisiert';
$_LANG['admin.settings.index.forms.api_settings_form.testconnection_success'] = 'Erfolgreich verbunden';
$_LANG['admin.settings.index.providers.api_settings_provider.connectionError'] = 'Verbindungsfehler';
$_LANG['admin.settings.index.providers.api_settings_provider.testUnsaved'] = 'Noch nichts gespeichert: Klicken Sie auf Änderungen speichern, um die Einstellungen zu speichern.';
$_LANG['admin.settings.index.providers.api_settings_provider.apiConnected'] = 'verbunden';
$_LANG['admin.settings.index.providers.api_settings_provider.apiFailed'] = 'Verbindung fehlgeschlagen, prüfen Sie Partnercode und Auth-Token';
$_LANG['admin.settings.index.providers.api_settings_provider.apiSandbox'] = 'Sandbox';
$_LANG['admin.settings.index.providers.api_settings_provider.savedAPI'] = 'Einstellungen gespeichert.';
$_LANG['admin.settings.index.providers.api_settings_provider.notSavedAPI'] = 'Nichts wurde gespeichert: Korrigieren Sie das fehlgeschlagene Konto und speichern Sie erneut.';
$_LANG['admin.settings.index.providers.api_settings_provider.successAPI'] = 'Die Verbindung war erfolgreich';


//ssl settings
//validation
$_LANG['admin.settings.index.widgets.ssl_settings_widget.title'] = 'SSL-Einstellungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.sslValidation'] = 'SSL-Validierung';
$_LANG['admin.settings.index.forms.ssl_settings_form.disableEmailValidation'] = 'E-Mail-Validierung deaktivieren';
$_LANG['admin.settings.index.forms.ssl_settings_form.disableEmailValidation_description'] = 'Für alle SSL-Zertifikate ist keine E-Mail-Validierung verfügbar, nur HTTP/DNS';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoDetailsForDvOrders'] = 'Automatische Admin-/Technik-Daten für DV-Bestellungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoDetailsForDvOrders_description'] = 'Das Modul verwendet das Kundenprofil für die Admin-/Technik-Daten';
$_LANG['admin.settings.index.forms.ssl_settings_form.wildcardDnsTimeoutMinutes'] = 'Timeout der Wildcard-DNS-Validierung (Minuten)';
$_LANG['admin.settings.index.forms.ssl_settings_form.wildcardDnsTimeoutMinutes_description'] = 'AutoInstall (cPanel): Wenn die DNS-Validierung eines Wildcard-Zertifikats nicht innerhalb dieser Anzahl von Minuten abgeschlossen wird, wechselt das System automatisch zur E-Mail-Validierung und benachrichtigt den Kunden. Standard: 65';
$_LANG['admin.settings.index.providers.ssl_settings_provider.wildcardDnsTimeoutMinutesInvalidValue'] = 'Der Wert muss eine ganze Zahl von Minuten größer als 0 sein';
$_LANG['admin.settings.index.forms.ssl_settings_form.cpanelTlsVerifyDisabled'] = 'cPanel-Server mit selbstsigniertem Zertifikat erlauben';
$_LANG['admin.settings.index.forms.ssl_settings_form.cpanelTlsVerifyDisabled_description'] = 'AutoInstall verbindet sich mit der WHM-API Ihrer cPanel-Server, mit den in WHMCS gespeicherten Serverzugangsdaten (API-Token oder Passwort). Aus (sicherer): Es verbindet sich nur mit einem Server, dessen eigenes WHM-Zertifikat für seinen Namen gültig ist; ein Server mit selbstsigniertem oder abgelaufenem Zertifikat oder ein per IP-Adresse in WHMCS eingetragener Server ist nicht erreichbar, und AutoInstall schlägt mit einem Zertifikatsfehler fehl. Ein: Auch solche Server werden erreicht, aber die Zugangsdaten sind nicht mehr gegen einen gefälschten Server geschützt. Es geht nicht um die Zertifikate der Websites Ihrer Kunden.';
$_LANG['admin.settings.index.forms.ssl_settings_form.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.ssl_settings_form.useFixedTechnicalDetails_description'] = '';
$_LANG['admin.settings.index.forms.ssl_settings_form.firstName'] = 'Vorname';
$_LANG['admin.settings.index.forms.ssl_settings_form.lastName'] = 'Nachname';
$_LANG['admin.settings.index.forms.ssl_settings_form.companyName'] = 'Name der Organisation';
$_LANG['admin.settings.index.forms.ssl_settings_form.jobTitle'] = 'Berufsbezeichnung';
$_LANG['admin.settings.index.forms.ssl_settings_form.emailAddress'] = 'E-Mail-Adresse';
$_LANG['admin.settings.index.forms.ssl_settings_form.address'] = 'Adresse';
$_LANG['admin.settings.index.forms.ssl_settings_form.city'] = 'Stadt';
$_LANG['admin.settings.index.forms.ssl_settings_form.zipCode'] = 'Postleitzahl';
$_LANG['admin.settings.index.forms.ssl_settings_form.stateRegion'] = 'Bundesland/Region';
$_LANG['admin.settings.index.forms.ssl_settings_form.postCode'] = 'Postleitzahl';
$_LANG['admin.settings.index.forms.ssl_settings_form.country'] = 'Land';
//validations
$_LANG['admin.settings.index.forms.firstName.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.firstName.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.lastName.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.lastName.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.companyName.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.companyName.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.jobTitle.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.jobTitle.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.emailAddress.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.emailAddress.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.address.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.address.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.city.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.city.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.zipCode.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.zipCode.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.stateRegion.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.stateRegion.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.postCode.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.postCode.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';
$_LANG['admin.settings.index.forms.country.customAttributes.useFixedTechnicalDetails'] = 'Feste technische Daten verwenden';
$_LANG['admin.settings.index.forms.country.customValues.useFixedTechnicalDetails.1'] = 'Aktiviert';

//csr generator
$_LANG['admin.settings.index.forms.ssl_settings_form.csrGenerator'] = 'CSR-Generator';
$_LANG['admin.settings.index.forms.ssl_settings_form.enableCsrGenerator'] = 'CSR-Generator aktivieren';
$_LANG['admin.settings.index.forms.ssl_settings_form.useProfileDetailsForCsr'] = 'Profildaten für CSR verwenden';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultCsrCountry'] = 'Standardland für CSR';
$_LANG['admin.settings.index.forms.ssl_settings_form.useProfileDetailsForCsr_description'] = 'Ein: Der CSR-Generator im Kundenbereich ist mit den Profildaten des Kunden vorausgefüllt (Land, Bundesland, Stadt, Firma und E-Mail), mit der Domain der Bestellung als Common Name und "IT" als Abteilung. Der Kunde kann jedes Feld vor dem Erzeugen des CSR ändern. Aus: Das Formular ist leer, nur das Standardland für CSR ist ausgewählt.';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultCsrCountry_description'] = 'Das Land, das der CSR-Generator im Kundenbereich zuerst auswählt, solange Profildaten für CSR verwenden aus ist (wenn es an ist, wird das Land des Kunden verwendet).';


$_LANG['admin.settings.index.forms.ssl_settings_form.renewals'] = 'Verlängerungen (Standardabrechnung)';
$_LANG['admin.settings.index.forms.ssl_settings_form.allOrders'] = 'Alle Bestellungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringOrders'] = 'Wiederkehrende Bestellungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeOrders'] = 'Einmalige Bestellungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonBeforeExpiration'] = 'Schaltfläche "Verlängern" vor Ablauf sichtbar';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonBeforeExpiration_description'] = 'Kunden sehen eine Schaltfläche Verlängern, solange die Bestellung läuft.';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonAfterExpiration'] = 'Schaltfläche "Verlängern" nach Ablauf sichtbar';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonAfterExpiration_description'] = 'Kunden sehen eine Schaltfläche Verlängern, nachdem die Bestellung abgelaufen ist.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewOrderViaExistingOrder'] = 'Bestellung über bestehende Bestellung verlängern';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalsWiki'] = 'Ausführliche Beschreibungen der Verlängerungseinstellungen mit Beispielen finden Sie in unserem Wiki auf GitHub.';
$_LANG['admin.settings.index.forms.ssl_settings_form.wikiLink'] = 'Wiki öffnen';
$_LANG['admin.settings.index.forms.ssl_settings_form.additionalDaysForRenewalOrder'] = 'Zusätzliche Tage für Verlängerungsbestellung (Sectigo)';
$_LANG['admin.settings.index.forms.ssl_settings_form.additionalDaysForRenewalOrder_description'] = 'Tage des alten Sectigo-Zertifikats, die dem verlängerten hinzugefügt werden (Sectigo erlaubt bis zu 30). Nicht gesetzt: die tatsächlich verbleibenden Tage, bis zu 30.';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoReissueDaysBeforeExpire'] = 'Automatische Neuausstellung (Tage vor Ablauf)';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoReissueDaysBeforeExpire_description'] = 'AutoInstall-Zertifikate (cPanel) werden automatisch so viele Tage vor Ablauf des Zertifikats neu ausgestellt, solange das Abonnement fortgeführt wird. 0 deaktiviert die automatische Neuausstellung. Standard: 7';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoice'] = 'Verlängerungsrechnung automatisch erstellen';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpiry'] = 'Verlängerungsrechnung erstellen: Tage vor Ende der Bestellung';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotifications'] = 'Ablaufbenachrichtigungen senden';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotifications_description'] = 'Tage vor Ablauf, an denen der Kunde eine Erinnerungs-E-Mail erhält, durch Komma getrennt, 0-30. Funktioniert mit oder ohne automatische Verlängerungsrechnungen: Bei ausgeschaltetem Rechnungsschalter ist dies der reine Erinnerungsmodus.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.recurringSendExpirationNotificationsInvalidValue'] = 'Die Werte müssen im Bereich von 0-30 liegen';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeCreateAutomaticRenewalInvoice'] = 'Verlängerungsrechnung automatisch erstellen';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeDaysBeforeExpiry'] = 'Tage vor Ablauf';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeSendExpirationNotifications'] = 'Ablaufbenachrichtigungen senden';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeSendExpirationNotifications_description'] = 'Durch Komma getrennt, von 0-30';
$_LANG['admin.settings.index.providers.ssl_settings_provider.oneTimeSendExpirationNotificationsInvalidValue'] = 'Die Werte müssen im Bereich von 0-30 liegen';

$_LANG['admin.settings.index.forms.ssl_settings_form.renewalsSubscription'] = 'Verlängerungen (Abonnementabrechnung)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoiceSubscription'] = 'Verlängerungsrechnung automatisch erstellen';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpirySubscription'] = 'Verlängerungsrechnung erstellen: Tage vor der Verlängerung des Abonnements';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotificationsSubscription'] = 'Ablaufbenachrichtigungen senden';
$_LANG['admin.settings.index.providers.ssl_settings_provider.recurringSendExpirationNotificationsSubscription'] = 'Die Werte müssen im Bereich von 0-30 liegen';

$_LANG['admin.settings.index.forms.ssl_settings_form.save'] = 'Speichern';
$_LANG['admin.settings.index.forms.ssl_settings_form.update_success'] = 'Einstellungen erfolgreich aktualisiert';

//finance settings

$_LANG['admin.settings.index.widgets.finance_settings_widget.title'] = 'Finanzeinstellungen';

$_LANG['admin.settings.index.forms.finance_settings_form.profitMargin'] = 'Gewünschte Gewinnmarge';
$_LANG['admin.settings.index.forms.finance_settings_form.currency'] = 'Standardwährung';
$_LANG['admin.settings.index.forms.finance_settings_form.rate'] = 'Kurs festlegen';
$_LANG['admin.settings.index.forms.finance_settings_form.info'] = 'In Ihrem WHMCS ist keine USD-Währung definiert. Ihre Standardwährung ist :defaultCurrency. Bitte geben Sie einen Umrechnungskurs von USD in :defaultCurrency an.<br><br>Beispiel: Bei einem Kurs von 1.20 und einem Produktpreis von 1.00 USD wird der Preis in WHMCS auf 1.20 :defaultCurrency festgelegt.';


$_LANG['admin.settings.index.forms.finance_settings_form.save'] = 'Speichern';
$_LANG['admin.settings.index.forms.finance_settings_form.update_success'] = 'Einstellungen erfolgreich aktualisiert';

//cron settings
$_LANG['admin.settings.index.widgets.cron_settings_widget.title'] = 'CRON-Einstellungen';

//cron settings titles
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_processing'] = '<b>Bestellinitialisierung</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_ProductPricingUpdate'] = '<b>Produktpreise</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLCertificatesProcessing'] = '<b>SSL-Verarbeitung</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLCertificates'] = '<b>Synchronisierung des Bestellstatus</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_RenewalNotifyCertificate'] = '<b>Automatische Verlängerungsbenachrichtigung</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_ReSyncProducts'] = '<b>Produkt-Neusynchronisierung</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLAutoInstall'] = '<b>SSL AutoInstall (cPanel)</b>';

//cron settings descriptions
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_ProductPricingUpdate'] = 'Richten Sie die folgende Cron-Zeile ein, um die automatische tägliche Aktualisierung der Produktpreise zu aktivieren.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLCertificates'] = 'Die wichtigste Synchronisierung des Bestellstatus. Fragt %%VENDOR%%, welche Bestellungen sich seit dem letzten Lauf geändert haben, und aktualisiert nur diese Services. Empfohlen: alle 5 Minuten.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_RenewalNotifyCertificate'] = 'Richten Sie die folgende Cron-Zeile ein, um automatische Verlängerungsbenachrichtigungen zu aktivieren.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLCertificatesProcessing'] = 'Richten Sie die folgende Cron-Zeile ein, um Aktualisierungen für Bestellungen in Bearbeitung zu aktivieren.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_ReSyncProducts'] = 'Richten Sie die folgende Cron-Zeile ein, um die Produktkonfigurationen neu zu synchronisieren.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLAutoInstall'] = 'Richten Sie die folgende Cron-Zeile ein, um die Warteschlange von SSL AutoInstall (cPanel) zu verarbeiten: Validierung, Installation und automatische Neuausstellung. Empfohlen: alle 5 Minuten.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_processing'] = 'Richten Sie die folgende Cron-Zeile ein, um den Anfangsstatus von Bestellungen zu ändern. Der Cron-Job läuft alle 10 Sekunden. Letzte Cron-Aktualisierung: ';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron1'] = 'Ausführungshäufigkeit des Cron-Jobs&nbsp;<b>ProductPricingUpdate</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron2'] = 'Ausführungshäufigkeit des Cron-Jobs&nbsp;<b>SSLCertificates</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron3'] = 'Ausführungshäufigkeit des Cron-Jobs&nbsp;<b>RenewalNotifyCertificate</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron4'] = 'Ausführungshäufigkeit des Cron-Jobs&nbsp;<b>ReSyncProducts</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron5'] = 'Ausführungshäufigkeit des Cron-Jobs&nbsp;<b>SSLCertificatesProcessing</b>';
$_LANG['admin.settings.index.forms.cron_configuration_form.save'] = 'Speichern';
$_LANG['admin.settings.index.forms.cron_configuration_form.update_success'] = 'Die Cron-Einstellungen wurden erfolgreich aktualisiert';


//===[Products]=========================================================================================================

$_LANG['admin.breadcrumbs.Products'] = 'Produkte';

// Automation Queue (AutoInstall cron tasks)
$_LANG['admin.breadcrumbs.AutomationQueue'] = 'Automatisierungswarteschlange';
$_LANG['admin.automation_queue.index.automation_queue_container.queueInfo'] = 'AutoInstall-Aufgaben (Validierung, Installation, Neuausstellung), die vom Cron verarbeitet werden. Eine Aufgabe im Status Fehler wird erst wieder verarbeitet, wenn sie zurückgesetzt wird.';

$_LANG['admin.automation_queue.index.data_tables.queue.data_table.id'] = 'ID';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.service'] = 'Service';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.domain'] = 'Domain';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.type'] = 'Typ';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.status'] = 'Status';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.attempts'] = 'Versuche';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.message'] = 'Meldung';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.updated'] = 'Letzte Aktualisierung';

$_LANG['admin.automation_queue.index.data_tables.queue.buttons.reset_task_button.resetTask'] = 'Aufgabe zurücksetzen';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.title'] = 'Aufgabe zurücksetzen';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.submit'] = 'Absenden';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.cancel'] = 'Abbrechen';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.description'] = 'Möchten Sie diese Aufgabe wirklich zurücksetzen? Ihr Status wird auf Ausstehend gesetzt und der Versuchszähler zurückgesetzt, sodass der Cron sie beim nächsten Lauf erneut verarbeitet.';
$_LANG['admin.automation_queue.index.data_tables.queue.forms.reset_task_form.update_success'] = 'Die Aufgabe wurde zurückgesetzt und wird beim nächsten Cron-Lauf verarbeitet';

//info
$_LANG['admin.products.index.products_container.productsInfo'] = 'Um ein Produkt zu importieren, wählen Sie es aus und klicken Sie auf die Schaltfläche Importieren.';
$_LANG['admin.configuration.content.step5.productsInfo'] = 'Um ein Produkt zu importieren, wählen Sie es aus und klicken Sie auf die Schaltfläche Importieren.';

//Datatable
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.id'] = 'Nr.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.productName'] = 'Produktname';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.brand'] = 'Marke';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.validation'] = 'Validierung';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.provider'] = 'Anbieter';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.status'] = 'Status';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.imported'] = 'Importiert';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.import'] = 'Importieren';


$_LANG['admin.products.index.data_tables.products.modals.re_sync_modal.reSync'] = 'Produkte neu synchronisieren';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.reSync'] = 'Produkte neu synchronisieren';
$_LANG['admin.products.index.data_tables.products.modals.re_sync_modal.description'] = 'Möchten Sie die Produkte wirklich neu synchronisieren?';
$_LANG['admin.products.index.data_tables.products.forms.re_sync_form.create_success'] = 'Produkte erfolgreich synchronisiert';


//import form
$_LANG['admin.products.index.data_tables.products.modals.import_modal.title'] = 'Importieren';
$_LANG['admin.products.index.data_tables.products.forms.import_form.pricingTypeIndividual'] = 'Produkt mit individueller Preisgestaltung importieren';
$_LANG['admin.products.index.data_tables.products.forms.import_form.pricingTypePercent'] = 'Produkt mit % Marge importieren';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productGroup'] = 'Produktgruppe';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productGroupName'] = 'Name der Produktgruppe';
$_LANG['admin.products.index.data_tables.products.forms.import_form.currency'] = 'Währung';
$_LANG['admin.products.index.data_tables.products.forms.import_form.rate'] = 'Kurs festlegen';
$_LANG['admin.products.index.data_tables.products.forms.import_form.info'] = 'In Ihrem WHMCS ist keine USD-Währung definiert. Ihre Standardwährung ist :defaultCurrency. Bitte geben Sie einen Umrechnungskurs von USD in :defaultCurrency an.<br><br>Beispiel: Bei einem Kurs von 1.20 und einem Produktpreis von 1.00 USD wird der Preis in WHMCS auf 1.20 :defaultCurrency festgelegt.';


$_LANG['admin.products.index.data_tables.products.forms.import_form.profitMargin'] = 'Gewinnmarge';
$_LANG['admin.products.index.data_tables.products.forms.import_form.profitMargin_description'] = 'Die % Marge wird auf den Preis aller Produkte angewendet. Sie können sie später unter Preise ändern';
$_LANG['admin.products.index.data_tables.products.forms.import_form.description'] = 'Hinweis: Die Preise aller Produkte werden auf Basis des "Base Conv. Rate" des ausgewählten Währungscodes und des USD-Währungscodes festgelegt. Stellen Sie sicher, dass Sie für jede Währung den richtigen "Base Conv. Rate" eingerichtet haben. (Der Standardpreis aller Produkte aus der TheSSLStore-API ist in USD)';
$_LANG['admin.products.index.data_tables.products.forms.import_form.create_success'] = 'Import erfolgreich abgeschlossen';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productCreationFailed'] = 'Import fehlgeschlagen';


//product configuration form
$_LANG['admin.products.index.data_tables.products.modals.configuration_modal.title'] = 'Produktkonfiguration';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.remoteProductName'] = 'Produkt';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.productName'] = 'Produktname';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.description'] = 'Beschreibung';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.autoSetup'] = 'Automatische Einrichtung';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOrder'] = 'Produkt automatisch einrichten, sobald eine Bestellung aufgegeben wird';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupPayment'] = 'Produkt automatisch einrichten, sobald die erste Zahlung eingegangen ist';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOn'] = 'Produkt automatisch einrichten, wenn Sie eine ausstehende Bestellung manuell akzeptieren';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOff'] = 'Dieses Produkt nicht automatisch einrichten';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.hidden'] = 'Produkt ausgeblendet';

$_LANG['admin.products.index.data_tables.products.forms.configuration_form.enableSan'] = 'SANs aktivieren';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.includedSan'] = 'Enthaltene Einzel-SANs';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.enableWildcard'] = 'Wildcard-SANs aktivieren';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.includedWildcard'] = 'Enthaltene Wildcard-SANs';


$_LANG['admin.products.index.data_tables.products.forms.configuration_form.update_success'] = 'Konfiguration erfolgreich aktualisiert';

//default option
$_LANG['choseProductGroup'] = '--- Produktgruppe wählen ---';
$_LANG['createNewProductGroup'] = '--- Neue Produktgruppe ---';


//===[Pricing]==========================================================================================================

$_LANG['admin.breadcrumbs.Pricing'] = 'Preise';
//Datatable
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.id'] = 'Nr.';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.productName'] = 'Produktname';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.brand'] = 'Marke';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.validation'] = 'Validierung';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.provider'] = 'Anbieter';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.disabled'] = 'Deaktiviert';

$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.annuallyPriceStrict'] = 'Jährlich';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.annuallyPrice'] = 'Jährlich/Einmalig';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.bienniallyPrice'] = 'Alle 2 Jahre';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.trienniallyPrice'] = 'Alle 3 Jahre';

//pricing form


$_LANG['acme_order_requires_at_least_one_san'] = 'Um dieses Produkt zu bestellen, müssen Sie mindestens 1 zusätzliche Einzeldomain-SAN oder zusätzliche Wildcard-SAN auswählen.';
$_LANG['csCartTermNeedsHsm'] = 'Code-Signing-Zertifikate für 2 oder 3 Jahre können nur auf Ihrem eigenen HSM installiert werden. Wählen Sie "Mein eigenes HSM" als Zertifikatszustellung oder einen Abrechnungszeitraum von 1 Jahr.';




$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_ggs'] = 'GoGetSSL-Modul (SSLCENTERWHMCS)';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_ggs_description'] = 'Migrieren: Produkte, Bestellungen, Abonnements und AutoInstall-Zertifikate werden nach den nächsten Schritten in dieses Modul übertragen (zuerst Backups, Rollback 14 Tage lang möglich). Eigenständig: Das Altmodul läuft unverändert weiter.';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_tss'] = 'TheSSLStore-Modul (thesslstorefullv2)';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_tss_description'] = 'Migrieren: Produkte, Bestellungen, Abonnements und AutoInstall-Zertifikate werden nach den nächsten Schritten in dieses Modul übertragen (zuerst Backups, Rollback 14 Tage lang möglich). Eigenständig: Das Altmodul läuft unverändert weiter.';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.next_step_btn'] = 'Nächster Schritt';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.option_migrate'] = 'In dieses Modul migrieren';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.option_skip'] = 'Als neues eigenständiges Modul installieren';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.saved'] = 'Gespeichert';

//general settings
$_LANG['admin.settings.index.widgets.sidebar.generalSettings'] = 'Allgemeine Einstellungen';
$_LANG['admin.settings.index.widgets.general_settings_widget.title'] = 'Allgemeine Einstellungen';
$_LANG['admin.settings.index.forms.general_settings_form.clientArea'] = 'Kundenbereich';
$_LANG['admin.settings.index.forms.general_settings_form.clientSummary'] = 'SSL-Übersicht';
$_LANG['admin.settings.index.forms.general_settings_form.clientSummary_description'] = 'Die SSL-Bestellungen des Kunden (alle, unbezahlt, in Bearbeitung, bald ablaufend) auf der Startseite des Kundenbereichs und in der Seitenleiste anzeigen';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates'] = 'Seiten mit SSL-Seitenleiste';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates_description'] = 'WHMCS-Template-Namen durch Komma getrennt, z. B. clientareaproducts,clientareaproductdetails. Leer = jede Seite außer der Startseite des Kundenbereichs';
$_LANG['admin.settings.index.forms.general_settings_form.expiresSoonDays'] = 'Läuft bald ab (Tage)';
$_LANG['admin.settings.index.forms.general_settings_form.expiresSoonDays_description'] = 'Ein Zertifikat wird so viele Tage vor seinem Fälligkeitsdatum als "Läuft bald ab" angezeigt';
$_LANG['admin.settings.index.forms.general_settings_form.certificatesByDomain'] = 'SSL-Zertifikate nach Domain';
$_LANG['admin.settings.index.forms.general_settings_form.certificatesByDomain_description'] = 'Seite im Kundenbereich, die jede Domain mit ihrem aktuellen Zertifikat und dem Verlauf der früheren Zertifikate auflistet';
$_LANG['admin.settings.index.forms.general_settings_form.lifecycle'] = 'Service-Lebenszyklus';
$_LANG['admin.settings.index.forms.general_settings_form.terminateCancelsOrder'] = 'Anbieterbestellung beim Kündigen stornieren';
$_LANG['admin.settings.index.forms.general_settings_form.terminateCancelsOrder_description'] = 'Das Kündigen des Services in WHMCS storniert auch die Zertifikatsbestellung bzw. das Abonnement beim Anbieter';
$_LANG['admin.settings.index.forms.general_settings_form.save'] = 'Speichern';
$_LANG['admin.settings.index.providers.general_settings_provider.moduleTemplate'] = 'Modulvorlage (Zertifikat senden)';

//product configuration: per-product settings
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.payType'] = 'Zahlungsart';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.months'] = 'Zertifikatslaufzeit';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.margin'] = 'Gewinnmarge (%)';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.margin_placeholder'] = 'Globale Marge aus den Finanzeinstellungen';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.processingGuide'] = 'Bearbeitungshinweis';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.issuedMessage'] = 'Meldung für ausgestelltes SSL';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.processingGuide_description'] = 'Leer lassen, um den Bearbeitungshinweis aus Einstellungen > SSL-Einstellungen zu verwenden. Ein hier eingegebener Text ersetzt ihn für dieses Produkt.';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.issuedMessage_description'] = 'Leer lassen, um die Nachricht aus den Einstellungen zu verwenden (die Nachricht der Zertifizierungsstelle, wenn sie aktiviert ist, sonst die allgemeine). Ein hier eingegebener Text ersetzt sie für dieses Produkt.';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeRecurring'] = 'Wiederkehrend';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeOnetime'] = 'Einmalig';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeFree'] = 'Kostenlos';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsBillingCycle'] = 'Aus dem Abrechnungszyklus';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsValue'] = ':months Monate';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsValueOneTime'] = ':months Monate (nur Einmalzahlung)';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsRecurringTooLong'] = 'Wiederkehrende Produkte werden höchstens alle :max Monate abgerechnet (dreijährlich), daher kann die Zertifikatslaufzeit höchstens :max Monate betragen. Wählen Sie eine kürzere Laufzeit oder stellen Sie die Zahlungsart auf Einmalig.';

//certificate log
$_LANG['admin.menu.certificateLog'] = 'Zertifikatsprotokoll';
$_LANG['admin.breadcrumbs.CertificateLog_index'] = 'Zertifikatsprotokoll';
$_LANG['admin.certificate_log.service_id'] = 'Service-ID';
$_LANG['admin.certificate_log.all_types'] = 'Alle Ereignisse';
$_LANG['admin.certificate_log.type_success'] = 'Erfolg';
$_LANG['admin.certificate_log.type_info'] = 'Info';
$_LANG['admin.certificate_log.type_error'] = 'Fehler';
$_LANG['admin.certificate_log.search'] = 'Meldung oder Domain';
$_LANG['admin.certificate_log.filter'] = 'Filtern';
$_LANG['admin.certificate_log.clear_30'] = 'Einträge älter als 30 Tage löschen';
$_LANG['admin.certificate_log.clear_all'] = 'Alle löschen';
$_LANG['admin.certificate_log.confirm_clear_30'] = 'Ereignisse älter als 30 Tage löschen?';
$_LANG['admin.certificate_log.confirm_clear_all'] = 'Alle Zertifikatsereignisse löschen? Kunden sehen dann keine Aktivitäten ihrer Zertifikate mehr.';
$_LANG['admin.certificate_log.cleared'] = ':count Ereignisse gelöscht.';
$_LANG['admin.certificate_log.date'] = 'Datum';
$_LANG['admin.certificate_log.service'] = 'Service';
$_LANG['admin.certificate_log.client'] = 'Kunde';
$_LANG['admin.certificate_log.type'] = 'Ereignis';
$_LANG['admin.certificate_log.message'] = 'Meldung';
$_LANG['admin.certificate_log.empty'] = 'Keine Ereignisse gefunden.';
$_LANG['admin.certificate_log.page'] = 'Seite :page von :pages (:total Ereignisse)';

//import: map to an existing service
$_LANG['admin.import.index.forms.import_form.serviceId'] = 'An bestehende Service-ID anhängen (optional)';
$_LANG['admin.settings.index.forms.general_settings_form.update_success'] = 'Einstellungen erfolgreich aktualisiert';

//pricing grid
$_LANG['admin.pricing_grid.currency'] = 'Währung';
$_LANG['admin.pricing_grid.margin'] = 'Marge %';
$_LANG['admin.pricing_grid.scope_currency'] = 'diese Währung';
$_LANG['admin.pricing_grid.scope_all'] = 'alle Währungen';
$_LANG['admin.pricing_grid.apply_margin'] = 'Marge anwenden und alles speichern';
$_LANG['admin.pricing_grid.confirm_margin'] = 'Jeden aktivierten Preis auf den Reseller-Einkaufspreis plus diese Marge setzen?';
$_LANG['admin.pricing_grid.export'] = 'CSV exportieren';
$_LANG['admin.pricing_grid.import'] = 'CSV importieren';
$_LANG['admin.pricing_grid.no_products'] = 'Noch keine Produkte dieses Moduls - importieren Sie sie auf der Seite Produkte.';
$_LANG['admin.pricing_grid.grid_help'] = 'Preise in der Abrechnungswährung des Kunden. Ein leerer Preis deaktiviert den Abrechnungszyklus. Rote Felder liegen unter Ihrem Reseller-Einkaufspreis. Produkte mit "Auto" werden vom Preisaktualisierungs-Cron neu bepreist.';
$_LANG['admin.pricing_grid.product'] = 'Produkt';
$_LANG['admin.pricing_grid.auto'] = 'Auto';
$_LANG['admin.pricing_grid.auto_title'] = 'Preis automatisch aus dem Reseller-Einkaufspreis und der Marge aktualisieren';
$_LANG['admin.pricing_grid.term_12'] = '1 Jahr';
$_LANG['admin.pricing_grid.term_24'] = '2 Jahre';
$_LANG['admin.pricing_grid.term_36'] = '3 Jahre';
$_LANG['admin.pricing_grid.term'] = 'Monate';
$_LANG['admin.pricing_grid.not_in_catalogue'] = 'nicht im %%VENDOR%%-Katalog';
$_LANG['admin.pricing_grid.item_base'] = 'Zertifikat';
$_LANG['admin.pricing_grid.item_sans'] = 'Zusätzliche SAN';
$_LANG['admin.pricing_grid.item_sans_wildcard'] = 'Zusätzliche Wildcard-SAN';
$_LANG['admin.pricing_grid.item'] = 'Position';
$_LANG['admin.pricing_grid.disabled'] = 'deaktiviert';
$_LANG['admin.pricing_grid.no_cost'] = 'kein Reseller-Preis';
$_LANG['admin.pricing_grid.cost'] = 'Einkaufspreis :cost';
$_LANG['admin.pricing_grid.below_cost'] = 'unter Einkaufspreis';
$_LANG['admin.pricing_grid.save_all'] = 'Alle speichern';
$_LANG['admin.pricing_grid.preview_title'] = 'Vorschau des CSV-Imports';
$_LANG['admin.pricing_grid.preview_summary'] = ':accepted Preisänderungen, :rejected abgelehnte Zeilen, :unchanged unverändert.';
$_LANG['admin.pricing_grid.old_price'] = 'Aktuell';
$_LANG['admin.pricing_grid.new_price'] = 'Neu';
$_LANG['admin.pricing_grid.reseller_cost'] = 'Reseller-Einkaufspreis';
$_LANG['admin.pricing_grid.rejected_title'] = 'Abgelehnte Zeilen';
$_LANG['admin.pricing_grid.line'] = 'Zeile';
$_LANG['admin.pricing_grid.data'] = 'Daten';
$_LANG['admin.pricing_grid.reason'] = 'Grund';
$_LANG['admin.pricing_grid.import_confirm'] = ':count Änderungen importieren';
$_LANG['admin.pricing_grid.import_cancel'] = 'Abbrechen';
$_LANG['admin.pricing_grid.notice_saved'] = ':count Preise gespeichert.';
$_LANG['admin.pricing_grid.notice_invalid'] = ':count Werte wurden nicht gespeichert, weil sie keine Beträge sind (Ziffern und Dezimalpunkt verwenden, zum Beispiel 49.90).';
$_LANG['admin.pricing_grid.notice_margin_applied'] = ':count Preise aus dem Reseller-Einkaufspreis aktualisiert.';
$_LANG['admin.pricing_grid.notice_imported'] = ':count Preise importiert.';

//products: lifecycle
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.manage'] = 'Verwalten';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_imported'] = 'Importiert';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_not_imported'] = 'Nicht importiert';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_hidden'] = 'Ausgeblendet';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_retired'] = 'Eingestellt';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_removed'] = 'Von %%VENDOR%% entfernt';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_options'] = 'Optionen prüfen';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_hide'] = 'Ausblenden';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_show'] = 'Einblenden';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_retire'] = 'Einstellen';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_unretire'] = 'Wieder aufnehmen';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_fixOptions'] = 'Optionen korrigieren';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_cancelRenewals'] = 'Alle Verlängerungen stornieren';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_retire'] = 'Dieses Produkt einstellen? Es kann nicht mehr bestellt werden; bestehende Services funktionieren weiter.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_cancel_renewals'] = 'Für jeden aktiven Service dieses Produkts eine Kündigungsanfrage (zum Ende des Abrechnungszeitraums) erstellen?';
$_LANG['admin.products.index.products_container.notice_hidden'] = ':product ist im Bestellformular ausgeblendet.';
$_LANG['admin.products.index.products_container.notice_shown'] = ':product ist im Bestellformular sichtbar.';
$_LANG['admin.products.index.products_container.notice_retired'] = ':product ist eingestellt.';
$_LANG['admin.products.index.products_container.notice_unretired'] = ':product ist wieder aktiv (weiterhin ausgeblendet - verwenden Sie Einblenden, um es anzubieten).';
$_LANG['admin.products.index.products_container.notice_options_fixed'] = 'Konfigurierbare Optionen von :product geprüft. Änderungen: :detail';
$_LANG['admin.products.index.products_container.notice_renewals_cancelled'] = 'Kündigungsanfragen für :detail Services von :product erstellt.';

//markups
$_LANG['admin.menu.markups'] = 'Aufschläge';
$_LANG['admin.breadcrumbs.Markups_index'] = 'Aufschläge';
$_LANG['admin.markups.intro'] = 'Erhöhen oder senken Sie den Preis von SSL-Produkten für einen Kunden: +10 addiert 10 % zum Produktpreis, -5 ist ein Rabatt von 5 %. Die spezifischste Regel gewinnt: Kunde + Produkt, dann Kunde + alle Produkte, dann alle Kunden + Produkt. Gilt für neue Bestellungen im Warenkorb, im Standard- und im Lagom-Bestellformular.';
$_LANG['admin.markups.client'] = 'Kunde';
$_LANG['admin.markups.product'] = 'Produkt';
$_LANG['admin.markups.markup'] = 'Aufschlag %';
$_LANG['admin.markups.all_clients'] = 'Alle Kunden';
$_LANG['admin.markups.all_products'] = 'Alle SSL-Produkte';
$_LANG['admin.markups.save'] = 'Hinzufügen / ändern';
$_LANG['admin.markups.updated'] = 'Aktualisiert';
$_LANG['admin.markups.delete'] = 'Löschen';
$_LANG['admin.markups.confirm_delete'] = 'Diesen Aufschlag löschen?';
$_LANG['admin.markups.empty'] = 'Keine Aufschläge.';
$_LANG['admin.markups.notice_saved'] = 'Aufschlag gespeichert.';
$_LANG['admin.markups.notice_deleted'] = 'Aufschlag gelöscht.';

//emails
$_LANG['admin.menu.emails'] = 'E-Mails';
$_LANG['admin.breadcrumbs.Emails_index'] = 'E-Mails';
$_LANG['admin.emails.intro'] = 'Die E-Mails, die dieses Modul an Ihre Kunden sendet. Sie werden als WHMCS-E-Mail-Vorlagen gespeichert, sodass der Versand, der E-Mail-Verlauf und Ihre WHMCS-Hooks wie gewohnt funktionieren; hier sehen und bearbeiten Sie nur die SSL-E-Mails, pro Sprache.';
$_LANG['admin.emails.no_permission'] = 'Ihre Administratorrolle benötigt die WHMCS-Berechtigung ":permission", um diese Seite zu öffnen.';
$_LANG['admin.emails.group_order'] = 'Bestellung';
$_LANG['admin.emails.group_validation'] = 'Validierung';
$_LANG['admin.emails.group_issued'] = 'Ausgestelltes Zertifikat';
$_LANG['admin.emails.group_renewals'] = 'Verlängerungen und Erinnerungen';
$_LANG['admin.emails.group_problems'] = 'Probleme';
$_LANG['admin.emails.group_order_desc'] = 'Bestellung und Konfiguration - gesendet, wenn der Kunde Angaben machen muss';
$_LANG['admin.emails.group_validation_desc'] = 'Domain- und Organisationsprüfung durch die Zertifizierungsstelle';
$_LANG['admin.emails.group_issued_desc'] = 'Zustellung nach erfolgreicher Ausstellung';
$_LANG['admin.emails.group_renewals_desc'] = 'Ablaufhinweise, Verlängerungsrechnungen und Erinnerungen zur Neuausstellung';
$_LANG['admin.emails.group_problems_desc'] = 'Fehler, um die sich der Kunde kümmern muss';
$_LANG['admin.emails.group_count_one'] = '1 E-Mail';
$_LANG['admin.emails.group_count'] = ':count E-Mails';
$_LANG['admin.emails.badge_customised'] = 'bearbeitet';
$_LANG['admin.emails.badge_disabled'] = 'deaktiviert';
$_LANG['admin.emails.badge_unused'] = 'wird nicht gesendet';
$_LANG['admin.emails.badge_languages'] = 'Spr.';
$_LANG['admin.emails.whmcs_name'] = 'WHMCS-Vorlage:';
$_LANG['admin.emails.language'] = 'Sprache';
$_LANG['admin.emails.language_default'] = 'Standard (jede Sprache ohne eigenen Text)';
$_LANG['admin.emails.language_default_hint'] = 'Kunden, deren Sprache keinen eigenen Text hat, erhalten diesen.';
$_LANG['admin.emails.language_new'] = 'Für :language gibt es noch keinen Text - der Standardtext wird angezeigt. Beim Speichern wird die Version für :language erstellt; Kunden mit dieser Sprache erhalten sie.';
$_LANG['admin.emails.subject'] = 'Betreff';
$_LANG['admin.emails.message'] = 'Text (HTML, Smarty-Merge-Felder)';
$_LANG['admin.emails.fields'] = 'Merge-Felder (zum Einfügen anklicken):';
$_LANG['admin.emails.save'] = 'Speichern';
$_LANG['admin.emails.preview'] = 'Vorschau';
$_LANG['admin.emails.test'] = 'Test an Administratoren senden';
$_LANG['admin.emails.confirm_test'] = 'Diesen Text, gefüllt mit Beispieldaten, an die Administratoren senden, die System-E-Mails erhalten? Kunden erhalten nichts.';
$_LANG['admin.emails.reset'] = 'Auf Standard zurücksetzen';
$_LANG['admin.emails.confirm_reset'] = 'Den Standardtext durch den Originaltext des Moduls ersetzen? Sprachversionen bleiben unverändert.';
$_LANG['admin.emails.enable'] = 'Aktivieren';
$_LANG['admin.emails.disable'] = 'Deaktivieren';
$_LANG['admin.emails.remove_language'] = 'Version für :language entfernen';
$_LANG['admin.emails.confirm_remove_language'] = 'Diese Sprachversion entfernen? Kunden mit dieser Sprache erhalten den Standardtext.';
$_LANG['admin.emails.preview_title'] = 'Vorschau';
$_LANG['admin.emails.preview_hint'] = 'Von WHMCS mit den Daten des neuesten SSL-Services (Kunde, Produkt) dargestellt; die übrigen Felder sind Beispiele.';
$_LANG['admin.emails.notice_saved'] = 'Die E-Mail wurde gespeichert.';
$_LANG['admin.emails.notice_test_sent'] = 'Die Test-E-Mail wurde an die Administratoren gesendet.';
$_LANG['admin.emails.notice_reset'] = 'Der Standardtext wurde zurückgesetzt.';
$_LANG['admin.emails.notice_language_removed'] = 'Die Sprachversion wurde entfernt.';
$_LANG['admin.emails.notice_disabled'] = 'Die E-Mail ist deaktiviert: WHMCS sendet sie nicht.';
$_LANG['admin.emails.notice_enabled'] = 'Die E-Mail ist aktiviert.';
$_LANG['admin.emails.name_configuration_required'] = 'Konfiguration erforderlich';
$_LANG['admin.emails.when_configuration_required'] = 'Wird gesendet, wenn ein Zertifikat bestellt wurde und der Kunde es konfigurieren muss (CSR, Domains, Validierung).';
$_LANG['admin.emails.name_s_mime_configuration_required'] = 'S/MIME-Zertifikat - Bestelldaten erforderlich';
$_LANG['admin.emails.when_s_mime_configuration_required'] = 'Wird gesendet, wenn ein S/MIME-Zertifikat gekauft oder verlängert wurde und der Kunde die zu schützende E-Mail-Adresse eingeben muss.';
$_LANG['admin.emails.name_s_mime_mailbox_not_confirmed'] = 'S/MIME - Postfach nicht bestätigt';
$_LANG['admin.emails.when_s_mime_mailbox_not_confirmed'] = 'Wird 2 und 7 Tage nach einer S/MIME-Bestellung gesendet, solange der Kunde die E-Mail der Zertifizierungsstelle nicht bestätigt hat.';
$_LANG['admin.emails.name_s_mime_certificate_issued'] = 'S/MIME-Zertifikat ausgestellt';
$_LANG['admin.emails.when_s_mime_certificate_issued'] = 'Wird gesendet, wenn ein S/MIME-Zertifikat ausgestellt ist: wie der Kunde das Zertifikat erhält (CSR oder E-Mail der Zertifizierungsstelle).';
$_LANG['admin.emails.name_s_mime_expiration'] = 'S/MIME-Zertifikat läuft ab';
$_LANG['admin.emails.when_s_mime_expiration'] = 'Wird vor dem Ablauf eines S/MIME-Zertifikats gesendet, an den für Ablaufhinweise eingestellten Tagen.';
$_LANG['admin.emails.name_code_signing_configuration_required'] = 'Code-Signing-Zertifikat - Bestelldaten erforderlich';
$_LANG['admin.emails.when_code_signing_configuration_required'] = 'Wird gesendet, wenn ein Code-Signing-Zertifikat gekauft oder verlängert wurde und der Kunde Organisation, Administrator und Zustellung angeben muss.';
$_LANG['admin.emails.name_code_signing_certificate_issued'] = 'Code-Signing-Zertifikat ausgestellt';
$_LANG['admin.emails.when_code_signing_certificate_issued'] = 'Wird gesendet, wenn ein Code-Signing-Zertifikat ausgestellt ist: wie der Kunde es erhält (versendeter Token, vorhandener Token oder HSM).';
$_LANG['admin.emails.name_code_signing_expiration'] = 'Code-Signing-Zertifikat läuft ab';
$_LANG['admin.emails.when_code_signing_expiration'] = 'Wird vor dem Ablauf eines Code-Signing-Zertifikats gesendet, an den für Ablaufhinweise eingestellten Tagen.';
$_LANG['admin.emails.name_acme_subscription_configuration'] = 'ACME-Abonnement - Konfiguration erforderlich';
$_LANG['admin.emails.when_acme_subscription_configuration'] = 'Wird gesendet, wenn ein ACME-Abonnement bestellt wurde und konfiguriert werden muss.';
$_LANG['admin.emails.name_dns_validation_switched_to_email'] = 'DNS-Validierung auf E-Mail umgestellt';
$_LANG['admin.emails.when_dns_validation_switched_to_email'] = 'Wird gesendet, wenn die DNS-Validierung nicht abgeschlossen wurde und AutoInstall die Bestellung auf E-Mail-Validierung umgestellt hat.';
$_LANG['admin.emails.name_validation_warning'] = 'Validierungsproblem (Dateimethode)';
$_LANG['admin.emails.when_validation_warning'] = 'Wird gesendet, wenn die CA die Validierungsdatei nach mehreren Prüfungen nicht verifizieren konnte.';
$_LANG['admin.emails.name_organization_validation_required'] = 'Organisationsvalidierung erforderlich';
$_LANG['admin.emails.when_organization_validation_required'] = 'Wird gesendet, wenn eine OV- oder EV-Bestellung (SSL, S/MIME, Code Signing, Document Signing) aufgegeben oder verlängert wird: Die CA prüft die Organisation und kontaktiert den Administrator.';
$_LANG['admin.emails.name_send_certificate'] = 'Zertifikat senden';
$_LANG['admin.emails.when_send_certificate'] = 'Wird gesendet, wenn das Zertifikat ausgestellt wird und wenn der Kunde oder ein Administrator es erneut sendet; das Zertifikat und das CA-Bundle sind angehängt.';
$_LANG['admin.emails.name_certificate_installed'] = 'Zertifikat installiert (AutoInstall)';
$_LANG['admin.emails.when_certificate_installed'] = 'Wird gesendet, nachdem das Zertifikat im cPanel-Konto des Kunden installiert wurde.';
$_LANG['admin.emails.name_service_expiration'] = 'Service-Ablauf';
$_LANG['admin.emails.when_service_expiration'] = 'Wird vom Verlängerungs-Cron an den in den SSL-Einstellungen (Ablaufbenachrichtigungen) aufgeführten Tagen vor Ablauf des Services gesendet.';
$_LANG['admin.emails.name_reissue'] = 'Erinnerung an Neuausstellung';
$_LANG['admin.emails.when_reissue'] = 'Wird 30 Tage vor Ablauf des Zertifikats gesendet und bittet den Kunden, es neu auszustellen (nicht für AutoInstall-Services, die automatisch neu ausgestellt werden).';
$_LANG['admin.emails.name_renewal_started'] = 'Verlängerung gestartet (AutoInstall)';
$_LANG['admin.emails.when_renewal_started'] = 'Wird gesendet, wenn die automatische Verlängerung eines AutoInstall-Zertifikats gestartet wurde.';
$_LANG['admin.emails.name_renewal_complete'] = 'Verlängerung abgeschlossen (AutoInstall)';
$_LANG['admin.emails.when_renewal_complete'] = 'Wird gesendet, nachdem ein verlängertes Zertifikat automatisch installiert wurde.';
$_LANG['admin.emails.name_certificate_autorenewed'] = 'Zertifikat automatisch verlängert';
$_LANG['admin.emails.when_certificate_autorenewed'] = 'Wird gesendet, wenn die Verlängerungsbestellung eines Zertifikats bei der CA aufgegeben wurde.';
$_LANG['admin.emails.name_acme_subscription_expiration'] = 'ACME-Abonnement - Verlängerungsrechnung';
$_LANG['admin.emails.when_acme_subscription_expiration'] = 'Wird gesendet, wenn die Verlängerungsrechnung eines ACME-Abonnements erstellt wurde (sofern in den SSL-Einstellungen aktiviert).';
$_LANG['admin.emails.name_acme_subscription_renewal'] = 'ACME-Abonnement - Verlängerung bezahlt';
$_LANG['admin.emails.when_acme_subscription_renewal'] = 'Wird gesendet, wenn die Verlängerungsrechnung eines ACME-Abonnements bezahlt wurde.';
$_LANG['admin.emails.name_auto_reissue_failed'] = 'Automatische Neuausstellung fehlgeschlagen';
$_LANG['admin.emails.when_auto_reissue_failed'] = 'Wird gesendet, wenn die automatische Neuausstellung vor Ablauf nach allen Versuchen fehlgeschlagen ist.';

$_LANG['admin.breadcrumbs.Emails_index'] = 'E-Mails';
$_LANG['admin.breadcrumbs.Emails'] = 'E-Mails';
$_LANG['admin.emails.language_tab_default'] = 'Standard';
$_LANG['admin.emails.add_language'] = 'Sprache hinzufügen';
$_LANG['admin.emails.fields_own'] = 'Felder dieser E-Mail (zum Einfügen anklicken)';
$_LANG['admin.emails.fields_standard'] = 'Felder jeder E-Mail';
$_LANG['admin.emails.opt_from'] = 'Absender';
$_LANG['admin.emails.opt_from_name'] = 'Name (leer: Firmenname)';
$_LANG['admin.emails.opt_from_email'] = 'E-Mail-Adresse (leer: WHMCS-Standard)';
$_LANG['admin.emails.opt_copy'] = 'Kopie an';
$_LANG['admin.emails.opt_blind'] = 'Blindkopie an';
$_LANG['admin.emails.opt_list_hint'] = 'E-Mail-Adressen durch Komma getrennt';
$_LANG['admin.emails.opt_plain'] = 'Nur Text';
$_LANG['admin.emails.opt_plain_hint'] = 'Diese E-Mail nur als reinen Text senden';
$_LANG['admin.emails.opt_disable'] = 'Deaktivieren';
$_LANG['admin.emails.opt_disable_hint'] = 'Diese E-Mail nicht senden';
$_LANG['admin.emails.rich_editor'] = 'Rich-Text-Editor';
$_LANG['admin.emails.rich_failed'] = 'Der visuelle Editor konnte nicht geladen werden (assets/js/tinymce von WHMCS); der Text wird als HTML-Quelltext bearbeitet.';
$_LANG['admin.emails.name_renewal_invoice_created'] = 'Verlängerungsrechnung erstellt';
$_LANG['admin.emails.when_renewal_invoice_created'] = 'Wird gesendet, wenn dieses Modul eine Verlängerungsrechnung erstellt. Standardmäßig deaktiviert: Solange sie deaktiviert ist, sendet WHMCS seine eigene Rechnungs-E-Mail; aktivieren Sie sie, um stattdessen diesen Text zu senden.';
$_LANG['admin.breadcrumbs.CertificateLog'] = 'Zertifikatsprotokoll';
$_LANG['admin.breadcrumbs.Markups'] = 'Aufschläge';
$_LANG['admin.breadcrumbs.Migration'] = 'Migration';

$_LANG['admin.switcher.yes'] = 'Ja';
$_LANG['admin.switcher.no']  = 'Nein';

$_LANG['admin.statistics.index.statistics_container.statisticsInfo'] = 'Die Einnahmen werden aus den bezahlten WHMCS-Rechnungen der von diesem Modul verwalteten Produkte berechnet. Die Diagramme zeigen die letzten sechs Monate (Gesamteinnahmen) und die fünf meistverkauften Produkte; die Tabellen führen die profitabelsten Reseller und die neuesten Rechnungen auf.';
$_LANG['admin.statistics.index.widgets.expiring_certificates_widget.title'] = 'Ablaufende Zertifikate';
$_LANG['admin.statistics.index.widgets.orders_by_status_widget.title'] = 'Bestellungen nach Status';
$_LANG['admin.statistics.index.widgets.revenue_by_provider_widget.title'] = 'Umsatz nach SSL-Marke (letzte 6 Monate)';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.expired'] = 'Abgelaufen';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.days30'] = 'Nächste 30 Tage';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.days60'] = '31-60 Tage';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.days90'] = '61-90 Tage';
$_LANG['admin.statistics.index.graphs.expiring_certificates_graph.certificates'] = 'Aktive Zertifikate';
$_LANG['admin.import.about_title'] = 'Was Sie hier finden';
$_LANG['admin.import.about_intro'] = 'Übernehmen Sie Zertifikate, die außerhalb von WHMCS bestellt wurden, in WHMCS. Jede Bestellung wird zu einem Service des von Ihnen gewählten Kunden und bleibt mit ihrem Zertifikat verknüpft.';
$_LANG['admin.import.about_single'] = 'Einzelne Bestellung: Geben Sie die Bestell-ID ein, wählen Sie den Kunden und die Zahlungsmethode und importieren Sie die Bestellung.';
$_LANG['admin.import.about_csv'] = 'Aus CSV: Laden Sie die Vorlage herunter, fügen Sie eine Zeile pro Bestellung hinzu und laden Sie die Datei hoch, um viele Bestellungen auf einmal zu importieren.';
$_LANG['admin.import.about_service'] = 'Geben Sie eine Service-ID ein, um die Bestellung an einen bestehenden Service anzuhängen, statt einen neuen zu erstellen.';
$_LANG['admin.reports.index.reports_container.reportsInfo'] = 'Die Berichte basieren auf den WHMCS-Rechnungen der von diesem Modul verwalteten Produkte. Der Verlängerungsbericht listet Zertifikate nach Ablaufdatum auf, der Umsatzbericht nach Datum zeigt die Umsätze eines gewählten Zeitraums, und der Gewinn- und Verlustbericht vergleicht die Umsätze mit den Anbieterkosten. Der Gewinn wird nur für Bestellungen angezeigt, bei denen der Anbieterpreis bekannt ist.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_title'] = 'Was Sie hier finden';
$_LANG['admin.automation_queue.index.automation_queue_container.about_intro'] = 'Die Aufgabenliste von SSL AutoInstall, das Zertifikate automatisch auf cPanel installiert. Der Cron verarbeitet die Aufgaben nacheinander. Diese Seite zeigt nur, was die Automatisierung getan hat; sie ändert keine Preise oder Bestellungen.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_validate'] = 'Validierung: wartet, bis der Anbieter das Zertifikat ausgestellt hat, und erstellt dann eine Installationsaufgabe.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_install'] = 'Installation: richtet das ausgestellte Zertifikat auf dem Hosting-Konto ein.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_reissue'] = 'Neuausstellung: ersetzt ein Zertifikat, das bald abläuft.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_status'] = 'Status: Ausstehend wartet auf den nächsten Cron-Lauf, In Bearbeitung läuft gerade, Erledigt ist abgeschlossen, Abgebrochen wurde durch eine neuere Aufgabe ersetzt.';
$_LANG['admin.automation_queue.index.automation_queue_container.about_error'] = 'Eine fehlgeschlagene Aufgabe wird bis zu 5-mal wiederholt, danach erhält sie den Status Fehler und wird angehalten. Lesen Sie die Spalte Meldung, beheben Sie die Ursache und verwenden Sie Aufgabe zurücksetzen, um sie erneut auszuführen.';
$_LANG['admin.products.about_title'] = 'Was Sie hier finden';
$_LANG['admin.products.about_intro'] = 'Die Produkte Ihres %%VENDOR%%-Kontos. Importieren Sie ein Produkt, um es in WHMCS zu verkaufen; die Spalte Status zeigt, in welchem Zustand es sich befindet.';
$_LANG['admin.products.connect_title_ggs'] = 'Auch GoGetSSL-Produkte verkaufen';
$_LANG['admin.products.connect_text_ggs'] = 'Verbinden Sie Ihr GoGetSSL-Konto, um GoGetSSL-Zertifikate (DV, OV, EV), GoGetSSL-Code-Signing und LEI-Codes anzubieten. Produkte, die auch TheSSLStore verkauft, bleiben ausgeblendet, damit nichts doppelt importiert wird.';
$_LANG['admin.products.connect_title_tss'] = 'Auch TheSSLStore-Produkte verkaufen';
$_LANG['admin.products.connect_text_tss'] = 'Verbinden Sie Ihr TheSSLStore-Konto, um SiteLock, cWatch und CodeGuard anzubieten. Produkte, die auch GoGetSSL verkauft, bleiben ausgeblendet, damit nichts doppelt importiert wird.';
$_LANG['admin.products.connect_link'] = 'Unter Einstellungen > API verbinden';
$_LANG['admin.products.about_import_name'] = 'Importieren';
$_LANG['admin.products.about_import'] = 'Wählen Sie Produkte aus und klicken Sie auf Importieren, um sie in WHMCS anzulegen.';
$_LANG['admin.products.about_hide_name'] = 'Ausblenden';
$_LANG['admin.products.about_hide'] = 'Entfernt das Produkt vorübergehend aus dem Bestellformular. Kunden mit einem direkten Link können es weiterhin bestellen. Einblenden macht es wieder sichtbar.';
$_LANG['admin.products.about_retire_name'] = 'Einstellen';
$_LANG['admin.products.about_retire'] = 'Das Produkt wird nicht mehr angeboten: Niemand kann es bestellen, und Kunden, die es nutzen, erhalten auf Verlängerungsrechnungen den Hinweis "Produkt eingestellt". Wieder aufnehmen macht dies rückgängig.';
$_LANG['admin.products.about_fix_name'] = 'Optionen korrigieren';
$_LANG['admin.products.about_fix'] = 'Wird nur angezeigt, wenn der Status "Optionen prüfen" lautet: Aktualisiert die konfigurierbaren Optionen des Produkts so, dass sie dem entsprechen, was %%VENDOR%% zulässt.';
$_LANG['admin.products.about_intro_connected'] = 'Die Produkte Ihres %%VENDOR%%-Kontos sowie die :brand-Produkte, die %%VENDOR%% nicht verkauft (verbunden unter Einstellungen > API). Importieren Sie ein Produkt, um es in WHMCS zu verkaufen; die Spalte Status zeigt, in welchem Zustand es sich befindet.';
$_LANG['admin.products.about_fix_connected'] = 'Wird nur angezeigt, wenn der Status "Optionen prüfen" lautet: Aktualisiert die konfigurierbaren Optionen des Produkts so, dass sie dem entsprechen, was sein Anbieter zulässt.';
$_LANG['admin.products.about_cancel_name'] = 'Alle Verlängerungen stornieren';
$_LANG['admin.products.about_cancel'] = 'Erstellt für jeden aktiven Service des Produkts eine Kündigungsanfrage zum Ende des Abrechnungszeitraums, sodass keiner verlängert wird.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing'] = 'Abrechnungsart';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_recurring'] = 'Wiederkehrend';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_onetime'] = 'Einmalig';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_free'] = 'Kostenlos';
$_LANG['admin.pricing_grid.markup_note'] = 'Aufschläge werden bei der Bestellung eines Kunden auf diese Basispreise aufgeschlagen. Alle Kunden: :global; kunden- oder produktspezifische Regeln: :count.';
$_LANG['admin.pricing_grid.markup_edit'] = 'Aufschläge bearbeiten';
$_LANG['admin.pricing_grid.client_price'] = 'Kunde zahlt :price (:rate %)';
$_LANG['admin.pricing_grid.client_price_title'] = 'Preis für Kunden ohne kundenspezifische Regel';
$_LANG['admin.settings.index.forms.general_settings_form.interface'] = 'Adminbereich';
$_LANG['admin.settings.index.forms.general_settings_form.hideInfoMessages'] = 'Infomeldungen ausblenden';
$_LANG['admin.settings.index.forms.general_settings_form.hideInfoMessages_description'] = 'Entfernt die großen Informationsblöcke unter den Seitentiteln des Moduls. Warnungen, Fehler und Bestätigungen bleiben erhalten.';
$_LANG['admin.settings.index.forms.general_settings_form.dashboardPeriod'] = 'Dashboard-Zeitraum';
$_LANG['admin.settings.index.forms.general_settings_form.dashboardPeriod_description'] = 'Der Zeitraum für Umsatz, Gewinn und ausgestellte Zertifikate auf dem Dashboard.';
$_LANG['admin.emails.about_title'] = 'Was Sie hier finden';
$_LANG['admin.emails.about_edit'] = 'Wählen Sie eine E-Mail in der Liste aus, bearbeiten Sie Betreff und Nachricht je Sprache und speichern Sie. Die Vorschau zeigt, was ein Kunde erhält.';
$_LANG['admin.emails.about_fields'] = 'Platzhalterfelder werden beim Versand der E-Mail durch Kunden- und Bestelldaten ersetzt. Klicken Sie auf ein Feld, um es einzufügen.';
$_LANG['admin.emails.name_product_discontinued'] = 'Produkt eingestellt';
$_LANG['admin.emails.when_product_discontinued'] = 'Wird mit der Verlängerungsrechnung an den Kunden gesendet, wenn %%VENDOR%% das Produkt des Services nicht mehr verkauft (eingestellt oder entfernt), sodass die Verlängerung unter demselben Produkt abgelehnt werden kann.';
$_LANG['admin.settings.index.forms.general_settings_form.sslTools'] = 'SSL-Tools für Kunden';
$_LANG['admin.settings.index.forms.general_settings_form.sslTools_description'] = 'Seite im Kundenbereich mit CSR- / Zertifikats-Decoder, Schlüsselabgleich, Formatkonverter, CSR-Generator und SSL-Checker. Die Tools laufen auf diesem Server und rufen keine API auf.';
$_LANG['admin.settings.index.forms.general_settings_form.clientAreaWiki'] = 'Beispiele dieser Seiten im Kundenbereich und weitere Details finden Sie in unserem Wiki auf GitHub.';
$_LANG['admin.settings.index.forms.general_settings_form.clientAreaWikiLink'] = 'Wiki öffnen';
$_LANG['admin.settings.index.forms.ssl_settings_form.ordering'] = 'Bestellung und Vereinbarung';
$_LANG['admin.settings.index.forms.ssl_settings_form.delivery'] = 'Zertifikatsbereitstellung';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstall'] = 'AutoInstall (cPanel)';
$_LANG['admin.settings.index.providers.ssl_settings_provider.moduleTemplate'] = 'Modulvorlage (Zertifikat senden)';
$_LANG['admin.settings.index.widgets.automated_settings_widget.title'] = 'Automatisiertes SSL (AutoInstall, ACME und Plan-Abonnements)';
$_LANG['admin.settings.index.widgets.sidebar.automatedSettings'] = 'Automatisiertes SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.processingGuide'] = 'Bearbeitungshinweis';
$_LANG['admin.settings.index.forms.ssl_settings_form.processingGuide_description'] = 'Wird dem Kunden angezeigt, während das Zertifikat validiert wird (HTML erlaubt). Kann pro Produkt überschrieben werden';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessage'] = 'Meldung für ausgestelltes SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessage_description']           = 'Wird dem Kunden nach der Ausstellung angezeigt (HTML erlaubt), wenn keine Marken-Nachricht gilt: S/MIME, Code Signing, andere Marken oder eine Marke, bei der "Diese Nachricht anzeigen" aus ist. Kann pro Produkt überschrieben werden';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageFor']                    = 'Nachricht bei ausgestelltem SSL für';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageForGeneral']             = 'Allgemein (andere Zertifikate)';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageFor_sectigo']            = 'Sectigo SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageFor_digicert']           = 'DigiCert SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageLanguage']               = 'Sprache';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageOn_sectigo']             = 'Diese Nachricht anzeigen';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageOn_sectigo_description'] = 'An: Kunden erhalten die Sectigo-SSL-Nachricht unten in ihrer Sprache. Aus: die allgemeine Nachricht';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageOn_digicert']            = 'Diese Nachricht anzeigen';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageOn_digicert_description'] = 'An: Kunden erhalten die DigiCert-SSL-Nachricht unten in ihrer Sprache. Aus: die allgemeine Nachricht';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageText']                   = 'Nachricht bei ausgestelltem SSL - {brand} ({language})';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageText_description']       = 'Integrierter Text, ändern Sie ihn nach Belieben (HTML erlaubt). Unverändert folgt er der Formulierung des Moduls in künftigen Updates';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessageTextEnglish_description'] = 'Das Modul hat keine Übersetzung für diese Sprache: Übersetzen Sie den englischen Text für Ihre Kunden (HTML erlaubt). Unverändert erhalten Kunden Ihren englischen Text';
$_LANG['admin.settings.index.forms.ssl_settings_form.certificateEmailTemplate'] = 'E-Mail-Vorlage für Zertifikat';
$_LANG['admin.settings.index.forms.ssl_settings_form.certificateEmailTemplate_description'] = 'Die E-Mail, die das ausgestellte Zertifikat sendet (als Dateien angehängt). Neben der Modulvorlage können Sie eine eigene wählen: Erstellen Sie in WHMCS eine E-Mail-Vorlage vom Typ Produkt/Service (Systemeinstellungen > E-Mail-Vorlagen) und verwenden Sie {$crt_code} für das Zertifikat und {$ssl_certyficate} für das CA-Bundle. Eine Vorlage eines anderen Typs wird nicht verwendet.';
$_LANG['admin.settings.index.forms.ssl_settings_form.requireAgreement'] = 'Zustimmung erforderlich';
$_LANG['admin.home.index.widgets.next_steps_widget.title'] = 'Nächste Schritte nach der Modulinstallation';
$_LANG['admin.home.index.widgets.next_steps_widget.chip'] = 'Einrichtung abschließen';
$_LANG['admin.home.index.widgets.next_steps_widget.step_products'] = 'Produkte einrichten';
$_LANG['admin.home.index.widgets.next_steps_widget.step_products_detail'] = 'Produkte anlegen und eine kurze Beschreibung hinzufügen';
$_LANG['admin.home.index.widgets.next_steps_widget.step_pricing'] = 'Preise festlegen';
$_LANG['admin.home.index.widgets.next_steps_widget.step_pricing_detail'] = 'Wählen Sie Ihre Verkaufspreise für Zertifikate';
$_LANG['admin.home.index.widgets.next_steps_widget.step_renewals'] = 'Verlängerungseinstellungen prüfen';
$_LANG['admin.home.index.widgets.next_steps_widget.step_renewals_detail'] = 'Prüfen Sie, wie Zertifikatsverlängerungen behandelt werden';
$_LANG['admin.home.index.widgets.system_check_widget.name_ProductPricingUpdate'] = 'Preis- und Katalogaktualisierung';
$_LANG['admin.home.index.widgets.system_check_widget.name_ReSyncProducts'] = 'Katalogaktualisierung';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLCertificates'] = 'Synchronisierung des Bestellstatus';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLCertificatesProcessing'] = 'Prüfung ausstehender Bestellungen';
$_LANG['admin.home.index.widgets.system_check_widget.name_RenewalNotifyCertificate'] = 'Verlängerungen, Erinnerungen und Ablauf';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLAutoInstall'] = 'AutoInstall-Warteschlange (cPanel)';
$_LANG['admin.home.index.widgets.system_check_widget.name_cronScript'] = 'Modul-Scheduler';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron1_description'] = 'Empfohlen: täglich.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron2_description'] = 'Empfohlen: alle 5 Minuten.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron3_description'] = 'Empfohlen: täglich.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron4_description'] = 'Aktualisiert die Produktkonfiguration (konfigurierbare Optionen, SAN-Limits), die im Dashboard angezeigten Ankündigungen und die Angaben zu Ihrem Account Manager. Empfohlen: einmal täglich.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron5_description'] = 'Empfohlen: alle 5 Minuten.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.oneLineTitle'] = '<b>Eine Cron-Zeile für das gesamte Modul</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.oneLineInfo'] = 'Richten Sie diese eine Zeile ein (alle 5 Minuten). Sie startet jeden Job des Moduls; jeder Job läuft nur, wenn er gemäß den Einstellungen unten fällig ist, sodass nichts weiter eingeplant werden muss. Wenn Sie die Zeile nicht hinzufügen, startet das Modul seine Jobs über den WHMCS-Cron (sofern der Server dies zulässt).';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveRunning'] = 'Cron läuft';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveDelayed'] = 'Cron läuft verzögert';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveStopped'] = 'Cron läuft nicht';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveByWhmcs'] = 'Gestartet vom WHMCS-Cron · letzter Start :time';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveByOwn'] = 'Gestartet von Ihrer eigenen Cron-Zeile · letzter Lauf :time';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveNextRun'] = 'Nächster Lauf in';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveDueNow'] = 'jeden Moment';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveHealthy'] = ':count in Ordnung';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveAttentionOne'] = '1 Job braucht Aufmerksamkeit';
$_LANG['admin.settings.index.widgets.cron_settings_widget.liveAttention'] = ':count Jobs brauchen Aufmerksamkeit';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lamp_ProductPricingUpdate'] = 'Preise';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lamp_SSLCertificates'] = 'Bestellsync';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lamp_RenewalNotifyCertificate'] = 'Verlängerungen';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lamp_SSLAutoInstall'] = 'AutoInstall';
$_LANG['admin.settings.index.widgets.cron_settings_widget.statusWhmcsRare'] = 'Der WHMCS-Cron hat die Jobs zuletzt am :time gestartet und läuft offenbar nicht alle 5 Minuten, daher werden Bestellungen und Zertifikate spät aktualisiert. Fügen Sie die Zeile unten hinzu.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.statusNoExec'] = 'Der WHMCS-Cron kann die Jobs nicht starten, weil die PHP-Funktion exec() auf diesem Server deaktiviert ist. Fügen Sie die Zeile unten hinzu.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.statusWindows'] = 'Der WHMCS-Cron kann die Jobs auf einem Windows-Server nicht starten. Planen Sie den Befehl unten in der Windows-Aufgabenplanung.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.statusStopped'] = 'Die Jobs liefen zuletzt am :time. Prüfen Sie, ob der WHMCS-Cron läuft (Setup > Automation Settings), oder fügen Sie die Zeile unten hinzu.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.statusNever'] = 'Noch kein Job des Moduls ist gelaufen. Prüfen Sie, ob der WHMCS-Cron läuft (Setup > Automation Settings), oder fügen Sie die Zeile unten hinzu.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.jobsTitle'] = 'Was die Jobs tun';
$_LANG['admin.settings.index.widgets.cron_settings_widget.advanced'] = 'Um einen einzelnen Job separat auszuführen (für einen Test oder um ihn getrennt einzuplanen), verwenden Sie';
$_LANG['admin.settings.index.widgets.cron_settings_widget.jobWord'] = 'Job';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_ProductPricingUpdate'] = 'Preis- und Katalogaktualisierung';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLCertificatesProcessing'] = 'Prüfung ausstehender Bestellungen';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLCertificates'] = 'Synchronisierung des Bestellstatus';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_RenewalNotifyCertificate'] = 'Verlängerungen, Erinnerungen und Ablauf';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLAutoInstall'] = 'AutoInstall-Warteschlange (cPanel)';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lastRun'] = 'Letzter Lauf:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.neverRun'] = 'nie';
$_LANG['admin.settings.index.widgets.cron_settings_widget.command'] = 'Jetzt ausführen (für einen Test):';
$_LANG['admin.settings.index.widgets.cron_settings_widget.frequency'] = 'Ausführungshäufigkeit:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.everyStart'] = 'bei jedem Start der obigen Cron-Zeile des Moduls (alle 5 Minuten).';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_ProductPricingUpdate'] = 'Preis- und Katalogaktualisierung';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_ProductPricingUpdate'] = 'Liest den Reseller-Katalog und die Preise von %%VENDOR%%, aktualisiert die Produktkonfiguration, die Ankündigungen und Ihren Account Manager und aktualisiert den Verkaufspreis jedes Produkts mit aktivierter automatischer Preisgestaltung (Einkaufspreis + Ihre Marge). Empfohlen: einmal täglich.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLCertificatesProcessing'] = 'Prüfung ausstehender Bestellungen';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLCertificatesProcessing'] = 'Verfolgt die Bestellungen, die übermittelt wurden und auf die Zertifizierungsstelle warten. Sobald ein Zertifikat ausgestellt ist, wird es gespeichert, das Fälligkeitsdatum gesetzt und die Zertifikats-E-Mail gesendet. Ausgestellte Bestellungen werden hier nicht erneut geprüft. Empfohlen: alle 5 Minuten, damit Kunden ihr Zertifikat schnell erhalten.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLCertificates'] = 'Synchronisierung des Bestellstatus';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLCertificates'] = 'Die wichtigste Synchronisierung des Bestellstatus. Fragt %%VENDOR%%, welche Bestellungen sich seit dem letzten Lauf geändert haben (ausgestellt, verlängert, neu ausgestellt, abgelaufen, storniert, widerrufen), und aktualisiert nur diese Services. Bestellungen, die noch die alte Bestell-ID enthalten, werden einmal gelesen, um ihre Zertifikats-ID zu speichern. Empfohlen: alle 5 Minuten.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_RenewalNotifyCertificate'] = 'Verlängerungen, Erinnerungen und Ablauf';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_RenewalNotifyCertificate'] = 'Einmal täglich: Erinnerungs-E-Mails zu Ablauf und Neuausstellung, automatische Verlängerungsrechnungen, Rechnungen für ACME- / AutoInstall-Abonnements sowie die Kündigung (mit Stornierung beim Anbieter) von Services, die bis zu ihrem Ablaufdatum nicht bezahlt wurden. Deaktivieren Sie diesen Job nicht, wenn Sie Verlängerungen automatisch abrechnen.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLAutoInstall'] = 'AutoInstall-Warteschlange (cPanel)';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLAutoInstall'] = 'Arbeitet die AutoInstall-Warteschlange ab: Domain-Validierung, Installation auf cPanel, Verlängerungen und automatische Neuausstellung. Läuft immer dann, wenn der Modul-Cron läuft (alle 5 Minuten).';
$_LANG['admin.settings.index.forms.cron_configuration_form.lastRun'] = 'Letzter Lauf:';
$_LANG['admin.settings.index.forms.cron_configuration_form.neverRun'] = 'nie';
$_LANG['admin.settings.index.forms.cron_configuration_form.runNow'] = 'Jetzt ausführen (für einen Test):';
$_LANG['admin.settings.index.forms.cron_configuration_form.frequency'] = 'Ausführungshäufigkeit:';
$_LANG['admin.settings.index.forms.cron_configuration_form.everyStart'] = 'bei jedem Start der obigen Cron-Zeile des Moduls (alle 5 Minuten).';$_LANG['admin.settings.index.forms.cron_configuration_form.healthy'] = 'Fehlerfrei';
$_LANG['admin.settings.index.forms.cron_configuration_form.failing'] = 'Fehlerhaft';
$_LANG['admin.settings.index.forms.cron_configuration_form.neverRunBadge'] = 'Noch nicht ausgeführt';
$_LANG['admin.settings.index.forms.cron_configuration_form.seeLog'] = 'Details im Modulprotokoll';
$_LANG['admin.settings.index.forms.cron_configuration_form.howOften'] = 'Ausführungshäufigkeit';
$_LANG['admin.settings.index.widgets.cron_settings_widget.subtitle'] = 'Geplante Aufgaben, die Preise, Bestellungen und Zertifikate aktuell halten';
$_LANG['admin.settings.index.widgets.cron_settings_widget.copy'] = 'Kopieren';
$_LANG['admin.settings.index.widgets.cron_settings_widget.copied'] = 'Kopiert';
$_LANG['admin.settings.index.forms.cron_configuration_form.saveChanges'] = 'Änderungen speichern';
$_LANG['admin.settings.index.forms.cron_configuration_form.noUnsaved'] = 'Keine ungespeicherten Änderungen';
$_LANG['admin.settings.index.forms.cron_configuration_form.unsaved'] = 'Ungespeicherte Änderungen';
$_LANG['admin.home.index.widgets.system_check_widget.failing'] = 'Fehlerhaft';
$_LANG['admin.home.index.widgets.gateway_widget.title'] = 'Verbindung zur %%VENDOR%%-API';
$_LANG['admin.home.index.widgets.gateway_widget.connected'] = 'Verbunden';
$_LANG['admin.home.index.widgets.gateway_widget.unreachable'] = 'Nicht erreichbar';
$_LANG['admin.home.index.widgets.gateway_widget.response'] = 'Antwortzeit';
$_LANG['admin.home.index.widgets.gateway_widget.none'] = 'Kein API-Konto konfiguriert';
$_LANG['admin.home.index.widgets.gateway_widget.credentials'] = 'API-Zugangsdaten';
$_LANG['admin.home.index.widgets.gateway_widget.credentials_detail'] = 'Verschlüsselt gespeichert';
$_LANG['admin.home.index.widgets.gateway_widget.manage'] = 'Verwalten';
$_LANG['admin.home.index.widgets.gateway_widget.recheck'] = 'Verbindung erneut prüfen';
$_LANG['admin.home.index.widgets.attention_widget.title'] = 'Erfordert Ihre Aufmerksamkeit';
$_LANG['admin.home.index.widgets.attention_widget.count'] = ':n Einträge';
$_LANG['admin.home.index.widgets.attention_widget.count_one'] = '1 Eintrag';
$_LANG['admin.home.index.widgets.attention_widget.none'] = 'Nichts erfordert Ihre Aufmerksamkeit';
$_LANG['admin.home.index.widgets.attention_widget.none_detail'] = 'Alle geplanten Aufgaben liefen ohne Fehler und %%VENDOR%% ist erreichbar.';
$_LANG['admin.home.index.widgets.attention_widget.never_title'] = ':task wurde noch nie ausgeführt';
$_LANG['admin.home.index.widgets.attention_widget.never_text'] = 'Fügen Sie die Cron-Zeile des Moduls aus den CRON-Einstellungen hinzu oder prüfen Sie, ob der WHMCS-Cron läuft.';
$_LANG['admin.home.index.widgets.attention_widget.cron'] = 'Cron-Einstellungen';
$_LANG['admin.home.index.widgets.attention_widget.failing_title'] = ':task schlägt fehl';
$_LANG['admin.home.index.widgets.attention_widget.log'] = 'Protokoll öffnen';
$_LANG['admin.home.index.widgets.attention_widget.gateway_title'] = ':gateway ist nicht erreichbar';
$_LANG['admin.home.index.widgets.attention_widget.api'] = 'API-Einstellungen';
$_LANG['admin.home.index.widgets.attention_widget.configure_title'] = ':n Bestellungen warten auf den Kunden';
$_LANG['admin.home.index.widgets.attention_widget.configure_title_one'] = '1 Bestellung wartet auf den Kunden';
$_LANG['admin.home.index.widgets.attention_widget.configure_text'] = 'Der Kunde hat für diese Bestellungen noch keinen CSR gesendet.';
$_LANG['admin.home.index.widgets.attention_widget.orders'] = 'Bestellungen anzeigen';
$_LANG['admin.home.index.widgets.attention_widget.name_ProductPricingUpdate'] = 'Preis- und Katalogaktualisierung';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLCertificatesProcessing'] = 'Prüfung ausstehender Bestellungen';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLCertificates'] = 'Synchronisierung des Bestellstatus';
$_LANG['admin.home.index.widgets.attention_widget.name_RenewalNotifyCertificate'] = 'Verlängerungen, Erinnerungen und Ablauf';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLAutoInstall'] = 'AutoInstall-Warteschlange (cPanel)';
$_LANG['admin.home.index.widgets.pipeline_widget.title'] = 'Zertifikats-Pipeline';
$_LANG['admin.home.index.widgets.pipeline_widget.open'] = 'Zertifikatsliste öffnen';
$_LANG['admin.home.index.widgets.pipeline_widget.configure'] = 'Wartet auf Konfiguration';
$_LANG['admin.home.index.widgets.pipeline_widget.configure_detail'] = 'Der Kunde hat keinen CSR gesendet';
$_LANG['admin.home.index.widgets.pipeline_widget.validation'] = 'Validierung';
$_LANG['admin.home.index.widgets.pipeline_widget.validation_detail'] = 'Bei der Zertifizierungsstelle';
$_LANG['admin.home.index.widgets.pipeline_widget.issued'] = 'Ausgestellt';
$_LANG['admin.home.index.widgets.pipeline_widget.issued_detail'] = 'Bereit oder installiert';
$_LANG['admin.home.index.widgets.pipeline_widget.expiring'] = 'Läuft in 90 Tagen ab';
$_LANG['admin.home.index.widgets.pipeline_widget.expiring_detail'] = 'Verlängerung bald fällig';
$_LANG['admin.home.index.widgets.pipeline_widget.unpaid'] = ':n warten auf Zahlung';
$_LANG['admin.home.index.widgets.pipeline_widget.unpaid_detail'] = 'Noch nicht bezahlte Bestellungen';
$_LANG['admin.home.index.widgets.pipeline_widget.expired'] = ':n abgelaufen';
$_LANG['admin.home.index.widgets.pipeline_widget.expired_detail'] = 'Enddatum überschritten';
$_LANG['admin.home.index.widgets.pipeline_widget.renewals'] = 'Verlängerungsbericht';
$_LANG['admin.home.index.widgets.pipeline_widget.renewals_detail'] = 'Anstehende Verlängerungen';
$_LANG['admin.home.index.widgets.overview_widget.invoiced'] = 'In Rechnung gestellt';
$_LANG['admin.home.index.widgets.overview_widget.cost'] = 'Reseller-Einkaufspreis';
$_LANG['admin.home.index.widgets.overview_widget.profit'] = 'Bruttogewinn';
$_LANG['admin.home.index.widgets.overview_widget.margin'] = 'Marge auf den Einkaufspreis';
$_LANG['admin.home.index.widgets.overview_widget.split'] = ':period: bezahlt :paid, unbezahlt :unpaid, überfällig :overdue.';
$_LANG['admin.home.index.widgets.overview_widget.low'] = 'Die Marge liegt unter :margin%: Erhöhen Sie sie auf der Seite Preise.';
$_LANG['admin.home.index.widgets.overview_widget.pricing'] = 'Preise und Marge anpassen';
$_LANG['admin.home.index.widgets.migration_widget.run_short'] = 'Lauf #:id :status';
$_LANG['admin.home.index.widgets.support_widget.email'] = 'E-Mail-Support';
$_LANG['admin.home.index.widgets.support_widget.write'] = 'Schreiben';
$_LANG['admin.home.index.widgets.support_widget.docs'] = 'Dokumentation';
$_LANG['admin.home.index.widgets.support_widget.docs_detail'] = 'Installieren, konfigurieren, Fehler beheben';
$_LANG['admin.home.index.widgets.support_widget.open'] = 'Öffnen';
$_LANG['admin.home.index.widgets.support_widget.manager'] = 'Ihr Partner Growth Manager';
$_LANG['admin.home.index.widgets.support_widget.report'] = 'Modulprotokolle für den Support';
$_LANG['admin.home.index.widgets.support_widget.report_detail'] = 'Aktivitätsbericht an den Support senden';
$_LANG['admin.home.index.widgets.support_widget.report_button'] = 'Bericht senden';
$_LANG['admin.home.index.widgets.next_steps_widget.lead'] = 'Einrichtung abschließen';
$_LANG['admin.home.index.widgets.system_check_widget.yesterday'] = 'Gestern :time';
$_LANG['admin.home.index.widgets.migration_widget.removed_on'] = 'Dieser Bereich wird am :date entfernt, :days Tage nach der Migration.';
$_LANG['admin.home.index.widgets.support_widget.phone_detail'] = 'Telefonischer Support';
$_LANG['admin.home.index.widgets.gateway_widget.not_configured'] = 'Nicht konfiguriert';
$_LANG['admin.home.index.widgets.gateway_widget.not_configured_detail'] = 'API-Zugangsdaten eingeben';
$_LANG['admin.home.index.widgets.attention_widget.gateway_missing_title'] = 'API-Zugangsdaten für :gateway fehlen';
$_LANG['admin.home.index.widgets.attention_widget.gateway_missing_text'] = 'Ohne sie kann das Modul keine Zertifikate bestellen oder prüfen. Geben Sie sie in den API-Einstellungen ein.';
$_LANG['admin.home.index.widgets.automated_ssl_banner.label'] = 'Hinweis auf Automatisiertes SSL';
$_LANG['admin.home.index.widgets.automated_ssl_banner.eyebrow'] = 'Neu · Automatisiertes SSL · ACME & AIS';
$_LANG['admin.home.index.widgets.automated_ssl_banner.title'] = 'Bieten Sie Ihren Kunden jetzt Automatisiertes SSL an';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_1'] = 'Zertifikate stellen sich in wenigen Minuten selbst aus';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_2'] = 'Verlängerungen erfolgen vor dem Ablauf — jedes Mal';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_3'] = 'Keine manuellen CSRs oder Validierungsdateien mehr';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_4'] = 'Keine Ausfälle durch abgelaufene Zertifikate';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_issue'] = 'Automatische Ausstellung';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_renew'] = 'Automatische Verlängerung';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_install'] = 'Automatische Installation';
$_LANG['admin.home.index.widgets.automated_ssl_banner.cta'] = 'Automatisiertes SSL aktivieren';
$_LANG['admin.home.index.widgets.automated_ssl_banner.note'] = 'Basiert auf den Protokollen ACME und AIS — einmal einrichten, läuft dauerhaft.';
$_LANG['admin.certificate_log.about_title'] = 'Was Sie hier finden';
$_LANG['admin.certificate_log.about_intro'] = 'Was das Modul mit jeder Zertifikatsbestellung getan hat: Bestellung, Validierung, Ausstellung, cPanel-Installation, Neuausstellung, Verlängerung, zusätzliche SANs und Änderungen durch Administratoren.';
$_LANG['admin.certificate_log.about_service'] = 'Filtern Sie nach Service-ID oder suchen Sie nach einer Domain oder einem beliebigen Wort der Meldung.';
$_LANG['admin.certificate_log.about_steps'] = 'Das Kürzel am Anfang einer Meldung ist der Schritt: [Order], [Validate], [Install], [Reissue], [AutoReissue], [Renew].';
$_LANG['admin.certificate_log.about_types'] = 'Erfolg: Schritt abgeschlossen. Info: Fortschritt. Fehler: Schritt fehlgeschlagen, einige werden automatisch wiederholt. Einträge bleiben erhalten, bis Sie sie löschen.';
$_LANG['admin.certificate_log.about_other'] = 'Fehler der %%VENDOR%%-API und Cron-Fehler werden hier nicht erfasst. Siehe die Seite :logs.';
$_LANG['admin.certificate_log.logs_page'] = 'Protokolle';
$_LANG['admin.settings.index.widgets.sidebar.logsSettings'] = 'Protokolle';
$_LANG['admin.settings.index.widgets.logs_settings_widget.title'] = 'Protokolle';
$_LANG['admin.settings.index.forms.logs_settings_form.logs'] = 'Protokolle';
$_LANG['admin.settings.index.forms.logs_settings_form.logs_desc'] = 'Was das Modul aufzeichnet. Zertifikatsprotokoll, technisches Protokoll und Ereignisse bleiben im Modul; die beiden WHMCS-Protokolle werden in WHMCS geschrieben und dort gelesen. Ein ausgeschaltetes Protokoll erhält keine neuen Einträge; gespeicherte bleiben.';
$_LANG['admin.settings.index.providers.general_settings_provider.whmcsModuleLogNoPermission'] = 'Das Module Log ist in WHMCS ausgeschaltet und Ihre Administratorrolle kann es nicht einschalten (WHMCS-Berechtigung ":permission"). Bitten Sie einen Administrator mit dieser Berechtigung oder lassen Sie WHMCS Module Log auf Nein.';
$_LANG['admin.settings.index.forms.logs_settings_form.certificateLog'] = 'Zertifikatsprotokoll';
$_LANG['admin.settings.index.forms.logs_settings_form.certificateLog_description'] = 'Der Verlauf jeder Zertifikatsbestellung (Bestellung, Validierung, Ausstellung, Installation, Neuausstellung, Verlängerung, zusätzliche SANs, Änderungen durch Administratoren) auf der Seite Zertifikatsprotokoll';
$_LANG['admin.settings.index.forms.logs_settings_form.internalLog'] = 'Technisches Protokoll (Seite Logs)';
$_LANG['admin.settings.index.forms.logs_settings_form.internalLog_description'] = 'Technische Meldungen des Moduls (%%VENDOR%%-API- und Cron-Fehler, Hinweise) auf der Seite Protokolle. Ist es ausgeschaltet, lassen sich Fehler schwerer nachvollziehen';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLog'] = 'API-Anfragen im Modulprotokoll';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLog_description'] = 'Jede Anfrage an %%VENDOR%% und deren Antwort in das Modulprotokoll schreiben, damit eine fehlgeschlagene Bestellung oder Synchronisierung nachvollzogen werden kann. Erfordert ein eingeschaltetes Modulprotokoll. Schlüssel und Passwörter werden maskiert. Schalten Sie die Option aus, wenn das Protokoll zu schnell wächst';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogReads'] = 'Auch erfolgreiche Leseanfragen protokollieren';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogReads_description'] = 'Standardmäßig werden nur fehlgeschlagene Anfragen und Anfragen, die etwas ändern, protokolliert. Die Crons lesen alle paar Minuten jede ausstehende Bestellung, sodass das Protokollieren jeder erfolgreichen Leseanfrage das Protokoll schnell füllt. Schalten Sie die Option nur zur Fehlersuche ein';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsActivityLog'] = 'WHMCS-Aktivitätsprotokoll';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsActivityLog_description'] = 'Zertifikatsereignisse (Bestellung, Neuausstellung, Validierung, Installation, Stornierung) in das WHMCS Activity Log schreiben. WHMCS führt dieses Protokoll immer; hier wird nur entschieden, ob das Modul hineinschreibt';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLog'] = 'WHMCS-Modulprotokoll';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLog_description'] = 'Die Anfragen an %%VENDOR%% und cPanel und ihre Antworten in das WHMCS Module Log schreiben (Logs > Module Log). Zeigt Nein, solange das Module Log in WHMCS ausgeschaltet ist. Ja schaltet es auch in WHMCS ein, wo es alle Module aufzeichnet; Nein stoppt nur dieses Modul und lässt die WHMCS-Einstellung unverändert. Welche Anfragen geschrieben werden, folgt API-Anfragen. Passwörter und Schlüssel werden nie geschrieben';
$_LANG['admin.settings.index.forms.logs_settings_form.save'] = 'Speichern';
$_LANG['admin.settings.index.forms.logs_settings_form.update_success'] = 'Einstellungen erfolgreich aktualisiert';
$_LANG['admin.savebar.saveChanges'] = 'Änderungen speichern';
$_LANG['admin.savebar.noUnsaved'] = 'Keine ungespeicherten Änderungen';
$_LANG['admin.savebar.unsaved'] = 'Ungespeicherte Änderungen';
$_LANG['admin.savebar.notSaved'] = 'Nicht gespeichert. Prüfen Sie die markierten Felder und versuchen Sie es erneut.';
$_LANG['admin.savebar.leaveConfirm'] = 'Sie haben ungespeicherte Änderungen. Diese Seite verlassen und die Änderungen verwerfen?';
$_LANG['admin.settings.index.widgets.api_settings_widget.subtitle'] = 'Zugangsdaten der Zertifizierungsstellen, deren Produkte Sie weiterverkaufen';
$_LANG['admin.settings.index.widgets.general_settings_widget.subtitle'] = 'Kundenbereich, Adminbereich, Service-Lebenszyklus und Preisverhalten';
$_LANG['admin.settings.index.widgets.ssl_settings_widget.subtitle'] = 'Einstellungen für Zertifikatsvalidierung, Bestellung, Bereitstellung und Verlängerung';
$_LANG['admin.settings.index.widgets.automated_settings_widget.subtitle'] = 'AutoInstall (cPanel), ACME und Plan-Abonnements';
$_LANG['admin.settings.index.widgets.logs_settings_widget.subtitle'] = 'Wählen Sie, welche Protokolle das Modul führt';
$_LANG['admin.settings.index.forms.api_settings_form.tss_desc'] = 'Partnercode und Auth-Token Ihres The SSL Store-Kontos. Im Testmodus werden die Sandbox-Werte verwendet.';
$_LANG['admin.settings.index.forms.api_settings_form.ggs_desc'] = 'Partnercode und Auth-Token Ihres GoGetSSL-Kontos.';
$_LANG['admin.settings.index.forms.api_settings_form.connect_ggs'] = 'GoGetSSL-Produkte verbinden';
$_LANG['admin.settings.index.forms.api_settings_form.connect_ggs_desc'] = 'Verkaufen Sie zusätzlich GoGetSSL-Zertifikate (DV, OV, EV), GoGetSSL-Code-Signing und LEI-Codes über Ihr GoGetSSL-Konto. Produkte, die auch TheSSLStore verkauft, werden nicht angeboten, damit nichts doppelt importiert wird.';
$_LANG['admin.settings.index.forms.api_settings_form.connect_tss'] = 'TheSSLStore-Produkte verbinden';
$_LANG['admin.settings.index.forms.api_settings_form.connect_tss_desc'] = 'Verkaufen Sie zusätzlich SiteLock, cWatch und CodeGuard über Ihr TheSSLStore-Konto. Produkte, die auch GoGetSSL verkauft, werden nicht angeboten, damit nichts doppelt importiert wird.';
$_LANG['admin.settings.index.forms.api_settings_form.connectOther'] = 'Verbinden';
$_LANG['admin.settings.index.forms.api_settings_form.connectOther_description'] = 'Ein: Die Produkte oben erscheinen auf der Produktseite zum Import. Aus: Sie werden wieder ausgeblendet (nur möglich, solange keines davon ein WHMCS-Produkt ist).';
$_LANG['admin.settings.index.forms.api_settings_form.buttonsScope'] = 'Änderungen speichern und Verbindung testen gelten für beide Konten auf dieser Seite: :primary und, solange Verbinden aktiv ist, :other. Das Ergebnis zeigt jede API einzeln.';
$_LANG['admin.settings.index.providers.api_settings_provider.disconnectHasProducts'] = '%1$s kann nicht getrennt werden: %2$d WHMCS-Produkt(e) nutzen es noch. Löschen oder deaktivieren Sie diese zuerst.';
$_LANG['admin.settings.index.forms.general_settings_form.clientArea_desc'] = 'Was Kunden auf ihren Zertifikatsseiten sehen.';
$_LANG['admin.settings.index.forms.general_settings_form.interface_desc'] = 'Wie das Modul für Administratoren aussieht.';
$_LANG['admin.settings.index.forms.general_settings_form.lifecycle_desc'] = 'Was mit einem Service geschieht, wenn er gekündigt oder nicht bezahlt wird.';
$_LANG['admin.settings.index.forms.general_settings_form.lei'] = 'LEI-Codes';
$_LANG['admin.settings.index.forms.general_settings_form.lei_desc'] = 'Firmensuche im LEI-Bestellformular.';
$_LANG['admin.settings.index.forms.general_settings_form.openCorporatesToken'] = 'OpenCorporates-API-Token';
$_LANG['admin.settings.index.forms.general_settings_form.openCorporates'] = 'Suche in Handelsregistern';
$_LANG['admin.settings.index.forms.general_settings_form.openCorporates_description'] = 'Ja: Das LEI-Bestellformular durchsucht auch die Handelsregister der meisten Länder (OpenCorporates, benötigt ein API-Token). Nein: Es findet nur Einheiten, die einen LEI haben oder hatten (GLEIF, kostenlos); ein gespeichertes Token wird entfernt.';
$_LANG['admin.settings.index.forms.general_settings_form.openCorporatesToken_description'] = 'Leer lassen, um das gespeicherte Token zu behalten.';
$_LANG['admin.settings.index.providers.general_settings_provider.openCorporatesTokenMissing'] = 'Geben Sie das OpenCorporates-API-Token ein oder schalten Sie die Suche in Handelsregistern aus.';
$_LANG['admin.settings.index.forms.ssl_settings_form.sslValidation_desc'] = 'Legen Sie fest, wie Kunden- und Kontaktdaten für die Validierung verwendet werden.';
$_LANG['admin.settings.index.forms.ssl_settings_form.csrGenerator_desc'] = 'Legen Sie die Standardwerte für Zertifikatsignierungsanfragen (CSR) fest.';
$_LANG['admin.settings.index.forms.ssl_settings_form.clientMessages'] = 'Kundenmeldungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.clientMessages_desc'] = 'Verwalten Sie die Hinweise und Meldungen, die Kunden angezeigt werden.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewals_desc'] = 'Steuern Sie die Verfügbarkeit von Verlängerungen und die Bestellverarbeitung.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringAndOneTime'] = 'Wiederkehrende und einmalige Bestellungen';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringAndOneTime_desc'] = 'Preis für zusätzliche SANs bei jeder Bestellung, dann Verlängerungsrechnungen und Ablauferinnerungen für wiederkehrende und einmalige Bestellungen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstall_desc'] = 'DNS-Wartezeit, TLS-Prüfung der cPanel-Server und automatische Neuausstellung.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalsSubscription_desc'] = 'Verlängerungsrechnungen und Erinnerungen für ACME- und Plan-Abonnements.';
$_LANG['admin.log_policy.apiMode_off'] = 'Nicht protokollieren';
$_LANG['admin.log_policy.apiMode_errors'] = 'Nur Fehler';
$_LANG['admin.log_policy.apiMode_changes'] = 'Fehler und Änderungen (empfohlen)';
$_LANG['admin.log_policy.apiMode_all'] = 'Alles, einschließlich Leseanfragen (Fehlersuche)';
$_LANG['admin.log_policy.hours_1'] = '1 Stunde';
$_LANG['admin.log_policy.hours_4'] = '4 Stunden';
$_LANG['admin.log_policy.hours_12'] = '12 Stunden';
$_LANG['admin.log_policy.hours_24'] = '24 Stunden';
$_LANG['admin.log_policy.hours_72'] = '3 Tage';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogMode'] = 'API-Anfragen';
$_LANG['admin.settings.index.forms.logs_settings_form.apiLogMode_description'] = 'Anfragen an %%VENDOR%% und deren Antworten. Fehler = %%VENDOR%% hat einen Fehler zurückgegeben oder nicht geantwortet. Änderungen = Bestellungen, Validierungen, Neuausstellungen, Stornierungen. Leseanfragen = die Crons fragen alle paar Minuten den Status jeder ausstehenden Bestellung ab: Sie füllen das Protokoll, verwenden Sie Alles daher nur zur Fehlersuche. Dieselbe Auswahl bestimmt, was in das WHMCS-Modulprotokoll geschrieben wird';
$_LANG['admin.settings.index.forms.logs_settings_form.eventLog'] = 'Ereignisse';
$_LANG['admin.settings.index.forms.logs_settings_form.eventLog_description'] = 'Was die Crons und Bestellaktionen getan haben: Statusänderungen, Benachrichtigungen, Verlängerungsrechnungen, Preisaktualisierungen. Fehler werden immer erfasst';
$_LANG['admin.settings.index.forms.logs_settings_form.debugLogs'] = 'Fehlersuche für eine Bestellung';
$_LANG['admin.settings.index.forms.logs_settings_form.debugLogs_desc'] = 'Sie gehen einem Problem mit einer Bestellung nach? Zeichnen Sie für diesen Service für begrenzte Zeit alles auf, unabhängig von den Einstellungen oben; danach kehrt das Modul von selbst zum Normalbetrieb zurück.';
$_LANG['admin.settings.index.forms.logs_settings_form.debugServices'] = 'Service-IDs';
$_LANG['admin.settings.index.forms.logs_settings_form.debugServices_description'] = 'Service-Nummern durch Komma getrennt, zum Beispiel 71, 58 (die Nummer nach # unter Related Item). Jede %%VENDOR%%-Anfrage, einschließlich Leseanfragen, und jedes Ereignis dieser Services wird aufgezeichnet. Leer lassen, um den Debug-Modus auszuschalten';
$_LANG['admin.settings.index.forms.logs_settings_form.debugHours'] = 'Aufzeichnen für';
$_LANG['admin.settings.index.forms.logs_settings_form.debugHours_description'] = 'Beim Speichern beginnt dieser Zeitraum von vorn. Danach endet der Debug-Modus von selbst';
$_LANG['admin.settings.index.widgets.finance_settings_widget.subtitle'] = 'Preise, Margen, Verlängerungen, Währung und Abrechnungsregeln';
$_LANG['admin.settings.index.forms.finance_settings_form.pricing'] = 'Preisgestaltung';
$_LANG['admin.settings.index.forms.finance_settings_form.minProfit'] = 'Mindestgewinn pro Zertifikat';
$_LANG['admin.settings.index.forms.finance_settings_form.minProfit_description'] = 'In USD (oder in Ihrer Standardwährung, wenn WHMCS kein USD hat). Ein Zertifikat bringt immer mindestens diesen Betrag über seinem Einkaufspreis ein, unabhängig von der Marge: 30% eines Zertifikats für 6.00 sind nur 1.80. Gilt nicht für SAN-Optionen. 0 = aus';
$_LANG['admin.settings.index.forms.finance_settings_form.termMarginStep'] = 'Geringere Marge pro zusätzlichem Jahr';
$_LANG['admin.settings.index.forms.finance_settings_form.termMarginStep_description'] = 'Die Marge für Laufzeiten von 2 und 3 Jahren wird pro zusätzlichem Jahr um diesen Wert gesenkt (Marge 30, Schritt 5: 1 Jahr 30%, 2 Jahre 25%, 3 Jahre 20%), damit lange Laufzeiten wettbewerbsfähig bleiben. Nie unter 0. 0 = dieselbe Marge für jede Laufzeit';
$_LANG['admin.settings.index.forms.finance_settings_form.priceRounding'] = 'Preisrundung';
$_LANG['admin.settings.index.forms.finance_settings_form.priceRounding_description'] = 'Rundet jeden berechneten Preis auf eine glatte Endung auf. Die Rundung senkt nie einen Preis';
$_LANG['admin.settings.index.forms.finance_settings_form.keepMarginOnSync'] = 'Marge bei Kostenänderungen beibehalten';
$_LANG['admin.settings.index.forms.finance_settings_form.keepMarginOnSync_description'] = 'Produkte mit manuellen Preisen ("Auto" auf der Seite Preise deaktiviert) folgen Änderungen des Reseller-Einkaufspreises, sodass ihre Marge gleich bleibt';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_none'] = 'Keine Rundung (13.37)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_99'] = 'Auf .99 aufrunden (13.99)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_95'] = 'Auf .95 aufrunden (13.95)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_whole'] = 'Auf eine ganze Zahl aufrunden (14.00)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rounding_half'] = 'Auf die nächste halbe Einheit aufrunden (13.50)';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCost'] = 'Schutz vor Preisen unter Einkaufspreis';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCost_desc'] = 'Ein Preis kann nach einer Preiserhöhung des Anbieters oder einer Wechselkursänderung unter Ihren Reseller-Einkaufspreis fallen. Wird nach jeder Preisaktualisierung geprüft.';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostAction'] = 'Wenn ein Preis unter dem Einkaufspreis liegt';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostAction_description'] = 'Preis erhöhen: Der Preis wird auf den normalen Preis des Produkts gesetzt (Einkaufspreis + dessen Marge, Mindestgewinn und Rundung). Gilt für manuelle und automatische Preise gleichermaßen';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostEmail'] = 'Administratoren per E-Mail informieren';
$_LANG['admin.settings.index.forms.finance_settings_form.belowCostEmail_description'] = 'Liste der Preise unter Einkaufspreis (oder der erhöhten Preise), wird einmal pro Änderung gesendet';
$_LANG['admin.settings.index.providers.finance_settings_provider.belowCost_notify'] = 'Nur benachrichtigen';
$_LANG['admin.settings.index.providers.finance_settings_provider.belowCost_raise'] = 'Preis automatisch erhöhen';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPrices'] = 'Verlängerungspreise bestehender Services';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceMode'] = 'Bestehende Services verlängern zum';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceClientEmail'] = 'Kunden vor einer Preiserhöhung per E-Mail informieren';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceClientEmail_description'] = 'Wird gesendet, wenn der Verlängerungspreis eines Services erhöht wird. Preissenkungen werden nicht angekündigt';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceEmailText'] = 'E-Mail zur Preisänderung';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceEmailText_description'] = 'Die Platzhalterfelder der WHMCS-Produkt-E-Mails funktionieren ({$client_first_name}, {$service_product_name}, {$service_domain}, {$service_next_due_date}, {$signature}), dazu {$old_price} und {$new_price}';
$_LANG['admin.settings.index.forms.finance_settings_form.currency_desc'] = 'Der Reseller-Einkaufspreis ist in USD angegeben. Andere Währungen werden mit den WHMCS-Wechselkursen umgerechnet (Setup > Payments > Currencies).';
$_LANG['admin.settings.index.forms.finance_settings_form.rateSource'] = 'Wechselkurs USD in Ihre Standardwährung';
$_LANG['admin.settings.index.forms.finance_settings_form.rateSource_description'] = 'WHMCS hat keine USD-Währung, daher rechnet das Modul den Einkaufspreis selbst um. Tageskurs: wird vor jeder Preisaktualisierung von der Europäischen Zentralbank abgerufen';
$_LANG['admin.settings.index.forms.finance_settings_form.rate_description'] = '1 USD = so viele Einheiten Ihrer Standardwährung. Beim Tageskurs wird der Wert automatisch eingetragen';
$_LANG['admin.settings.index.forms.finance_settings_form.currencyBuffer'] = 'Währungs-Sicherheitspuffer';
$_LANG['admin.settings.index.forms.finance_settings_form.currencyBuffer_description'] = 'Wird auf Preise in anderen Währungen als USD aufgeschlagen, damit eine Wechselkursänderung zwischen zwei Preisaktualisierungen Ihre Marge nicht aufzehrt. 0 = aus';
$_LANG['admin.settings.index.providers.finance_settings_provider.rateSource_manual'] = 'Fester Kurs (unten eingegeben)';
$_LANG['admin.settings.index.providers.finance_settings_provider.rateSource_ecb'] = 'Tageskurs (Europäische Zentralbank)';
$_LANG['admin.settings.index.forms.finance_settings_form.billing'] = 'Abrechnung';
$_LANG['admin.settings.index.forms.finance_settings_form.billing_desc'] = 'Unbezahlte Zertifikate werden nicht vom Modul gekündigt: WHMCS übernimmt das mit seiner eigenen Regel (Setup > Automation Settings > Suspension & Termination, "Terminate after X days"), und beim Kündigen eines Dienstes wird auch seine Bestellung beim Anbieter storniert (Einstellungen > Allgemein > Service-Lebenszyklus). Unbezahlte ACME- und Plan-Abonnements werden vor ihrer Verlängerung storniert: Einstellungen > Automatisiertes SSL. Verlängerungsrechnungen und Ablauferinnerungen finden Sie unter Einstellungen > SSL-Zertifikate und Automatisiertes SSL.';
$_LANG['admin.settings.index.forms.finance_settings_form.balance'] = 'Reseller-Guthaben';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPrices_desc'] = 'Wenn Sie einen Preis ändern, berechnet WHMCS bestehenden Services weiterhin den Preis, zu dem sie bestellt wurden. Wählen Sie, was bestehende Services bei ihrer nächsten Verlängerung zahlen (wird nach jeder Preisaktualisierung geprüft; bereits vorhandene Rechnungen ändern sich nicht):';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_never'] = 'Bestellpreis beibehalten:';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_never_text'] = 'Kunden verlängern immer zu dem Preis, den sie zuerst bezahlt haben. Sicher für Kunden, aber wenn Ihr Einkaufspreis steigt, verkaufen Sie möglicherweise unter Einkaufspreis.';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_belowCost'] = 'Nur aktualisieren, wenn unter Einkaufspreis (empfohlen):';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_belowCost_text'] = 'Ein Service behält seinen Preis, bis dieser Ihren Reseller-Einkaufspreis nicht mehr deckt; dann wechselt er zum aktuellen Preis. Treue Kunden behalten ihren Preis, und Sie machen nie Verlust.';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_always'] = 'Immer der aktuelle Preis:';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPoint_always_text'] = 'Jeder Service verlängert zur heutigen Preisliste, nach oben wie nach unten, einschließlich der Services, denen Sie einen Sonderpreis gegeben haben.';
$_LANG['admin.settings.index.forms.finance_settings_form.renewalPriceMode_description'] = 'Der aktuelle Preis ist der heutige Produktpreis mit seinen SAN-Optionen und der Aktion, zuzüglich des Aufschlags des Kunden';
$_LANG['admin.settings.index.providers.finance_settings_provider.renewal_never'] = 'Bestellpreis beibehalten';
$_LANG['admin.settings.index.providers.finance_settings_provider.renewal_belowCost'] = 'Nur aktualisieren, wenn unter Einkaufspreis';
$_LANG['admin.settings.index.providers.finance_settings_provider.renewal_always'] = 'Immer der aktuelle Preis';
$_LANG['admin.settings.index.forms.finance_settings_form.balance_desc'] = 'Zertifikatsbestellungen werden aus Ihrem Guthaben beim Zertifikatsanbieter bezahlt. Ist das Guthaben aufgebraucht, können bezahlte Bestellungen nicht ausgestellt werden und Ihre Kunden warten auf ihre Zertifikate.';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceAlert'] = 'Per E-Mail informieren, wenn eine Bestellung mangels Guthaben fehlschlägt';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceAlert_description'] = 'Sie erhalten eine E-Mail, sobald der Anbieter eine Bestellung ablehnt, weil das Guthaben zu niedrig ist, sodass Sie es aufladen und die Bestellung erneut senden können. Höchstens eine E-Mail alle 6 Stunden';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceThreshold'] = 'Warnen, wenn das verbleibende Guthaben unter diesem Betrag liegt (:currency)';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceThresholdBrand'] = ':brand-Konto: warnen, wenn das verbleibende Guthaben unter diesem Betrag liegt (:currency)';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceThreshold_description'] = 'Das Guthaben wird stündlich gelesen. Unter diesem Betrag erhalten die WHMCS-Administratoren eine E-Mail, höchstens eine pro Tag, solange es niedrig bleibt. Prepaid-Konten: es zählt das Guthaben; Kreditkonten: der verbleibende Kredit (Guthaben + Kreditlimit); Konten mit Monatsrechnung werden nie durch das Guthaben gestoppt. Sie erhalten auch eine E-Mail, wenn das Konto nicht mehr aktiv ist. 0 = aus';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceNow'] = ':brand-Konto: Guthaben :balance (Prepaid).';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceNowCredit'] = ':brand-Konto: Ihnen steht eine Kreditlinie zur Verfügung: :left (Kreditlimit :limit, Guthaben :balance).';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceNowCreditNoLimit'] = ':brand-Konto: Guthaben :balance, Kredit ohne Limit - keine Warnung nötig.';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceNowInvoice'] = ':brand-Konto: Guthaben :balance, Zahlung per Monatsrechnung - ein niedriges Guthaben stoppt keine Bestellungen, daher keine Warnung.';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceUpdated'] = 'Aktualisiert am :time';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceNotChecked'] = ':brand-Konto: Das Guthaben wurde noch nicht gelesen. Es wird beim Speichern dieser Seite und danach stündlich gelesen.';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceError'] = ':brand-Konto: Das Guthaben konnte nicht gelesen werden: :error';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceErrorLast'] = 'Das letzte Lesen ist fehlgeschlagen: :error';
$_LANG['admin.settings.index.forms.finance_settings_form.balanceInactive'] = 'Kontostatus: :status - Bestellungen werden abgelehnt, bis das Konto wieder aktiv ist.';
$_LANG['admin.settings.index.forms.finance_settings_form.pricing_desc'] = 'Die Standardregeln, nach denen aus Ihrem Reseller-Einkaufspreis ein Verkaufspreis wird. Sie gelten für Produkte mit automatischer Preisgestaltung, für Marge anwenden auf der Seite Preise, für den Produktimport und für neue SAN-Optionen.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesIntro'] = 'Auch andere Einstellungen beeinflussen, was Kunden zahlen. Was dort derzeit eingestellt ist:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesAuto'] = 'Seite Preise:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesProducts'] = 'Seite Produkte:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkups'] = 'Aufschläge:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsGlobal'] = 'Aufschläge:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsNone'] = 'Aufschläge:';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsNone_text'] = 'keine Regeln, Kunden zahlen die hier berechneten Preise.';
$_LANG['admin.settings.index.forms.finance_settings_form.openPricing'] = 'Preise öffnen';
$_LANG['admin.settings.index.forms.finance_settings_form.openProducts'] = 'Produkte öffnen';
$_LANG['admin.settings.index.forms.finance_settings_form.openMarkups'] = 'Aufschläge öffnen';
$_LANG['admin.settings.index.forms.finance_settings_form.profitMargin_description'] = 'Wird bei Produkten mit automatischer Preisgestaltung, die keine eigene Marge haben, auf den Reseller-Einkaufspreis aufgeschlagen. Bei 0 wird zum Einkaufspreis verkauft';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesAuto_text'] = 'automatische Preisgestaltung (verwendet die Marge unten): :auto, manuelle Preise (werden durch diese Marge nicht geändert): :manual.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesProducts_text'] = 'Produkte mit eigener Marge, die die Standardmarge unten ersetzt: :count.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkups_text'] = 'Regeln, die für ausgewählte Kunden oder Produkte einen Prozentsatz auf den Preis aufschlagen, bei der Bestellung und bei Verlängerungen: :count.';
$_LANG['admin.settings.index.forms.finance_settings_form.rulesMarkupsGlobal_text'] = '+:global% für alle Kunden auf alle Produkte (Regeln insgesamt: :count). Der Aufschlag kommt bei der Bestellung und bei Verlängerungen zu jedem Preis hinzu, sodass Kunden mehr als die hier berechneten Preise zahlen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.useDefaultWebServer'] = 'Frage nach dem Webserver überspringen';
$_LANG['admin.settings.index.forms.ssl_settings_form.useDefaultWebServer_description'] = 'Ja: Kunden werden bei der Konfiguration des Zertifikats nicht gefragt, welchen Webserver sie verwenden (es wird Other gespeichert). Nein: Kunden wählen ihren Webserver (Apache, IIS, cPanel ...) während der Konfiguration aus der WHMCS-Liste; die Auswahl wird beim Service angezeigt. Das Zertifikat ist in beiden Fällen dasselbe';
$_LANG['admin.settings.index.forms.ssl_settings_form.ordering_desc'] = 'Ob Kunden nach ihrem Webserver gefragt werden, und die Teilnehmervereinbarung, der sie zustimmen, bevor die Bestellung aufgegeben wird.';
$_LANG['admin.settings.index.forms.ssl_settings_form.requireAgreement_description'] = 'Der Kunde muss der Teilnehmervereinbarung der Zertifizierungsstelle des Produkts zustimmen, bevor die Bestellung aufgegeben wird. Die Vereinbarung unten wird automatisch anhand der Marke des Produkts gewählt';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_sectigo'] = 'Sectigo-Vereinbarung (Sectigo, Comodo, PositiveSSL, InstantSSL, EssentialSSL)';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_digicert'] = 'DigiCert-Vereinbarung (DigiCert, GeoTrust, RapidSSL, Thawte)';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_gogetssl'] = 'GoGetSSL-Vereinbarung';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreement_other'] = 'Andere Zertifizierungsstellen';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_sectigo'] = 'Link zur Vereinbarung';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_digicert'] = 'Link zur Vereinbarung';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_gogetssl'] = 'Link zur Vereinbarung';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_sectigo_description'] = 'Wird über das Kontrollkästchen geöffnet, das der Kunde aktiviert. Leer = der Name der Vereinbarung ohne Link';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_digicert_description'] = 'Wird über das Kontrollkästchen geöffnet, das der Kunde aktiviert. Leer = der Name der Vereinbarung ohne Link';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementUrl_gogetssl_description'] = 'Wird über das Kontrollkästchen geöffnet, das der Kunde aktiviert. Leer = der Name der Vereinbarung ohne Link';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_sectigo'] = 'Text oberhalb des Kontrollkästchens';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_digicert'] = 'Text oberhalb des Kontrollkästchens';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_gogetssl'] = 'Text oberhalb des Kontrollkästchens';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_sectigo_description'] = 'Optional, HTML erlaubt. Leer = der Text für andere Zertifizierungsstellen unten';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_digicert_description'] = 'Optional, HTML erlaubt. Leer = der Text für andere Zertifizierungsstellen unten';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_gogetssl_description'] = 'Optional, HTML erlaubt. Leer = der Text für andere Zertifizierungsstellen unten';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText'] = 'Text oberhalb des Kontrollkästchens';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_description'] = 'Optional, HTML erlaubt. Wird für Produkte aller anderen Zertifizierungsstellen angezeigt sowie für die oben genannten Stellen, die keinen eigenen Text haben';
$_LANG['admin.settings.index.forms.ssl_settings_form.delivery_desc'] = 'Die Download-Schaltflächen, die Kunden sehen, sobald ihr Zertifikat ausgestellt ist. Jede liefert dasselbe Zertifikat, so verpackt, wie es ein bestimmter Servertyp erwartet. Schalten Sie die Formate aus, die Ihre Kunden nicht benötigen; sind alle ausgeschaltet, wird das ZIP mit allen Dateien angeboten.';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_zip'] = 'Alles in einem ZIP';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_zip_description'] = 'Ein ZIP mit allen unten genannten Dateien (Zertifikat, CA-Bundle, verkettetes Zertifikat, .p7b). Die sichere Wahl, wenn Sie den Server des Kunden nicht kennen';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_crt'] = 'Nur Zertifikat';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_crt_description'] = 'Nur das Zertifikat des Kunden (.crt), ohne die Zwischenzertifikate. Für Control Panels, die Zertifikat und CA-Bundle in getrennten Feldern abfragen (cPanel, Plesk, DirectAdmin)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_ca'] = 'Zwischenzertifikate (CA-Bundle)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_ca_description'] = 'Nur die Zwischenzertifikate der Zertifizierungsstelle (.ca-bundle). Werden zusammen mit dem Zertifikat installiert; fehlen sie, zeigen Browser Fehler an';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_fullchain'] = 'Zertifikat mit Zwischenzertifikaten (vollständige Kette)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_fullchain_description'] = 'Zertifikat und Zwischenzertifikate in einer Datei (.fullchain.crt). Für die meisten modernen Server und Tools: HAProxy, Load Balancer, Caddy, neuere Apache-Versionen';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_nginx'] = 'Nginx';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_nginx_description'] = 'Gleicher Inhalt wie die vollständige Kette, für Nginx benannt (.chained.crt); die Datei, auf die die Zeile ssl_certificate verweist';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_apache'] = 'Apache';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_apache_description'] = 'Ein ZIP mit dem Zertifikat und dem CA-Bundle als zwei Dateien, für SSLCertificateFile und SSLCertificateChainFile von Apache';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_pkcs7'] = 'Windows IIS / Tomcat';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_pkcs7_description'] = 'Zertifikat und Zwischenzertifikate in einer PKCS#7-Datei (.p7b), dem Format, das Windows (IIS, Exchange) und Java-Server (Tomcat) importieren';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates_placeholder'] = 'Jede Seite außer der Startseite des Kundenbereichs';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareahome'] = 'Startseite des Kundenbereichs';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareaproducts'] = 'Meine Services';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareaproductdetails'] = 'Service-Details';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareadomains'] = 'Meine Domains';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareadomaindetails'] = 'Domain-Details';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareainvoices'] = 'Meine Rechnungen';
$_LANG['admin.settings.index.providers.general_settings_provider.page_supportticketslist'] = 'Meine Tickets';
$_LANG['admin.settings.index.providers.general_settings_provider.page_viewticket'] = 'Ticket-Details';
$_LANG['admin.settings.index.providers.general_settings_provider.page_clientareadetails'] = 'Kontodetails';
$_LANG['admin.settings.index.providers.general_settings_provider.page_knowledgebase'] = 'Wissensdatenbank';
$_LANG['admin.settings.index.providers.general_settings_provider.page_announcements'] = 'Ankündigungen';
$_LANG['admin.configuration.content.step2.command_hint'] = 'Führen Sie dies auf dem Server aus (SSH, als Dateibesitzer oder root) und laden Sie dann diese Seite neu:';

$_LANG['admin.settings.index.forms.ssl_settings_form.terminateUnpaidSubscriptions'] = 'Unbezahlte Abonnements beenden';
$_LANG['admin.settings.index.forms.ssl_settings_form.terminateUnpaidSubscriptions_description'] = 'Ist die Verlängerungsrechnung nach ihrem Fälligkeitsdatum noch unbezahlt, wird das Abonnement beim Anbieter vor der Verlängerung gekündigt und der Dienst beendet. Die Verlängerungsrechnung ist am Tag vor der Verlängerung des Abonnements fällig.';
$_LANG['admin.settings.index.forms.ssl_settings_form.sanUpgradePricing'] = 'Preis für zusätzliche SANs';
$_LANG['admin.settings.index.forms.ssl_settings_form.sanUpgradePricing_description'] = 'Preis für SANs, die ein Kunde nach der Bestellung hinzufügt. Voller Preis: der SAN-Preis für den gesamten Abrechnungszeitraum. Anteilig: nur die Zeit bis zum nächsten Fälligkeitsdatum. Einmalige Bestellungen zahlen immer den vollen SAN-Preis.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.sanUpgradePricing_full'] = 'Voller Preis für den Abrechnungszeitraum';
$_LANG['admin.settings.index.providers.ssl_settings_provider.sanUpgradePricing_prorated'] = 'Anteilig bis zum nächsten Fälligkeitsdatum';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstallReportEmail'] = 'AutoInstall-Bericht per E-Mail';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstallReportEmail_description'] = 'Eine tägliche E-Mail an die Administratoren: die Zertifikate, die AutoInstall in cPanel installiert hat (neue Bestellungen, Verlängerungen, automatische Neuausstellungen), die fehlgeschlagenen, die manuell installiert werden müssen, und die nach 3 Tagen noch auf die Validierung warten. Nichts Neues, keine E-Mail';
$_LANG['admin.settings.index.forms.ssl_settings_form.subscriptionSlotPricing'] = 'Preis für „Weitere Domains kaufen“';
$_LANG['admin.settings.index.forms.ssl_settings_form.subscriptionSlotPricing_description'] = 'Preis der Domains (Plätze), die ein Kunde während der Abonnementlaufzeit kauft. Anteilig: der Jahrespreis für die Tage bis zur nächsten Verlängerung. Voller Preis: der Jahrespreis, unabhängig vom Kaufzeitpunkt. Die Verlängerung berechnet immer den vollen Jahrespreis für alle Domains.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.subscriptionSlotPricing_prorated'] = 'Anteilig bis zur nächsten Verlängerung';
$_LANG['admin.settings.index.providers.ssl_settings_provider.subscriptionSlotPricing_full'] = 'Voller Jahrespreis';

// renewal invoices and renewals (1.5.0)
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpiry_description'] = 'Die Verlängerungsrechnung wird so viele Tage vor dem Ende der Bestellung erstellt - nie nach dem Ablauf des Zertifikats selbst. Höchstens 30: eine CA überträgt nicht mehr als 30 Tage des alten Zertifikats, ein früher verlängernder Kunde würde die Differenz verlieren.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTerm'] = 'Zahlungsziel (Tage)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTerm_description'] = 'Die Verlängerungsrechnung ist so viele Tage nach ihrer Erstellung fällig.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpirySubscription_description'] = 'Die Verlängerungsrechnung wird so viele Tage erstellt, bevor der Anbieter das Abonnement verlängert (höchstens 30).';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTermSubscription'] = 'Zahlungsziel (Tage)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringPaymentTermSubscription_description'] = 'Die Verlängerungsrechnung ist so viele Tage nach ihrer Erstellung fällig. Eine unbezahlte Verlängerung wird beim Anbieter am Tag vor der Verlängerung storniert: der Kunde behält das bezahlte Jahr, und Ihnen wird das nächste nicht berechnet.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewOrderViaExistingOrder_description'] = 'Ein: Die Verlängerung bleibt beim selben WHMCS-Service. Aus: Sie erhält einen neuen Service, der alte wird geschlossen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewReuseOrderDetails'] = 'Mit denselben Bestelldaten verlängern';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewReuseOrderDetails_description'] = 'Ein: Eine bezahlte Verlängerung wird sofort mit dem bisherigen CSR, den Domains und Kontakten bestellt. Aus: Der Kunde wird gebeten, sie erneut zu senden.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckCertificateBefore'] = 'Rechnung :lead Tage vor Ende der Bestellung erstellt und :term Tage später fällig: :days Tage vor dem Ende.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckCertificateOn'] = 'Rechnung :lead Tage vor Ende der Bestellung erstellt und :term Tage später fällig: am Tag, an dem die Bestellung endet.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckCertificateAfter'] = 'Rechnung :lead Tage vor Ende der Bestellung erstellt und :term Tage später fällig: :days Tage nach dem Ende.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckSubscriptionBefore'] = 'Rechnung :lead Tage vor der Verlängerung des Abonnements erstellt und :term Tage später fällig: :days Tage vor der Verlängerung.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckSubscriptionOn'] = 'Rechnung :lead Tage vor der Verlängerung des Abonnements erstellt und :term Tage später fällig: am Tag der Verlängerung.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalCheckSubscriptionAfter'] = 'Rechnung :lead Tage vor der Verlängerung des Abonnements erstellt und :term Tage später fällig: :days Tage nach der Verlängerung.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalDueAfterCertificateEnds'] = 'Die Rechnung ist :days Tage nach dem Ende der Bestellung fällig. Ein Kunde, der am Fälligkeitstag zahlt, verlängert erst nach Ablauf des Zertifikats, die Website läuft bis zu :days Tage ohne gültiges Zertifikat. Für Sie ohne Kosten: die Verlängerung wird erst nach Zahlung bestellt. Empfohlen: ein Zahlungsziel von höchstens :safe Tagen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalDueDayBeforeRenewal'] = 'Die Rechnung ist am Tag vor der Verlängerung des Abonnements fällig. Eine unbezahlte Verlängerung wird dann erst am Tag der Verlängerung storniert, was zu spät sein kann, um die Verlängerung und Berechnung durch den Anbieter zu verhindern. Empfohlen: ein Zahlungsziel von höchstens :safe Tagen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewalDueAfterSubscriptionRenews'] = 'Risiko: die Rechnung ist am oder nach dem Tag fällig, an dem der Anbieter das Abonnement verlängert. Ihnen wird das nächste Jahr berechnet, bevor Ihr Kunde bezahlt hat, und eine unbezahlte Verlängerung kann erst danach storniert werden. Empfohlen: ein Zahlungsziel von höchstens :safe Tagen.';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoice_description'] = 'Ein: das Modul erstellt die Verlängerungsrechnungen nach den Tagen unten, die eigene Rechnungsstellung von WHMCS wird für diese Dienste zurückgehalten - eine Rechnung pro Verlängerung. Aus: WHMCS stellt Verlängerungen nach seiner eigenen Einstellung zur Rechnungserstellung in Rechnung, ohne die 30-Tage-Grenze.';
$_LANG['admin.emails.name_renewal_details_required'] = 'Verlängerungsdaten erforderlich';
$_LANG['admin.emails.when_renewal_details_required'] = 'Wird gesendet, wenn eine Verlängerung bezahlt ist und der Kunde die Daten erneut einreichen muss (CSR, Domains, Kontakte) - "Mit denselben Bestelldaten verlängern" ist aus, oder der Anbieter hat die bisherigen Daten abgelehnt.';

// renewal paid but still pending (1.5.2)
$_LANG['admin.settings.index.forms.ssl_settings_form.pendingPaymentWaitSubscription'] = 'Verlängerung bezahlt, aber noch ausstehend';
$_LANG['admin.settings.index.forms.ssl_settings_form.pendingPaymentWaitSubscription_description'] = 'Eine vom Kunden bezahlte Verlängerung, deren Zahlung bei Fälligkeit der Rechnung noch nicht eingegangen ist ("Payment Pending"). Als unbezahlt behandeln: das Abonnement wird am Tag vor der Verlängerung durch den Anbieter storniert, wie eine unbezahlte Verlängerung. Warten: es wird so viele Tage nicht storniert. Eine "Payment Pending"-Rechnung storniert das Modul nie.';
$_LANG['admin.settings.index.forms.ssl_settings_form.pendingWaitSubscriptionRisk'] = 'Warten auf ausstehende Zahlungen: der Anbieter verlängert das Abonnement und berechnet es Ihnen, bevor eine ausstehende Zahlung eingegangen ist. Schlägt die Zahlung fehl, bleibt die Belastung bei Ihnen.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.pendingWait_0'] = 'Als unbezahlt behandeln';
$_LANG['admin.settings.index.providers.ssl_settings_provider.pendingWait_3'] = '3 Tage auf den Eingang warten';
$_LANG['admin.settings.index.providers.ssl_settings_provider.pendingWait_7'] = '7 Tage auf den Eingang warten';
$_LANG['admin.settings.index.providers.ssl_settings_provider.pendingWait_14'] = '14 Tage auf den Eingang warten';
$_LANG['admin.settings.index.providers.ssl_settings_provider.pendingWait_30'] = '30 Tage auf den Eingang warten';

// migration: products without a gateway match, automatic rollback (1.7.0)
$_LANG['admin.migration.map_title'] = ':n Altprodukte haben keine Entsprechung beim Zertifikatsanbieter';
$_LANG['admin.migration.map_intro'] = 'Wählen Sie für jedes vor dem Start: ein Alternativprodukt (bestehende Zertifikate funktionieren weiter und werden als das neue Produkt verlängert) oder die Zertifikate nur als Aufzeichnung behalten. Das Altmodul bleibt für sie nicht erhalten.';
$_LANG['admin.migration.map_col_legacy'] = 'Altprodukt';
$_LANG['admin.migration.map_col_services'] = 'Dienste';
$_LANG['admin.migration.map_col_choice'] = 'Migrieren als';
$_LANG['admin.migration.map_services'] = ':active aktiv, :awaiting warten auf Konfiguration, :other sonstige';
$_LANG['admin.migration.map_choose'] = '- wählen -';
$_LANG['admin.migration.map_record'] = 'Nur als Aufzeichnung behalten (keine Verlängerung)';
$_LANG['admin.migration.map_same_brand'] = 'Gleiche Marke';
$_LANG['admin.migration.map_all_products'] = 'Alle Produkte';
$_LANG['admin.migration.map_unsupported'] = 'Dieser Produkttyp wird vom Modul nicht unterstützt: seine Zertifikate können nur als Aufzeichnung behalten werden.';
$_LANG['admin.migration.map_record_hint'] = 'Nur Aufzeichnung: das Zertifikat und seine Bestelldaten bleiben für den Kunden und Sie sichtbar, es kann aber nicht verlängert, neu ausgestellt oder konfiguriert werden, wird nie für eine Verlängerung in Rechnung gestellt, und das Produkt kann nicht erneut bestellt werden. Zertifikate, die auf Konfiguration warten, können dann nie konfiguriert werden - ordnen Sie diese Produkte nach Möglichkeit einer Alternative zu.';
$_LANG['admin.migration.auto_rollback'] = 'Diese Migration wurde angehalten und automatisch zurückgesetzt: :reason. Das Altmodul arbeitet wie zuvor. Laden Sie den Fehlerbericht herunter und senden Sie ihn an den Support.';
$_LANG['admin.migration.btn_error_report'] = 'Fehlerbericht herunterladen';
$_LANG['admin.migration.map_quiet'] = ':n weitere Altprodukte ohne Entsprechung haben keine aktiven oder unkonfigurierten Zertifikate: sie werden als eingestellte Aufzeichnungen behalten (keine Auswahl nötig)';

// setup wizard: migration step (1.7.0)
$_LANG['instalation.wizard.step7_name'] = 'Migration';
$_LANG['instalation.wizard.step7_desc'] = 'Altmodul übernehmen';
$_LANG['admin.configuration.content.step7.step7_title'] = 'Migration';
$_LANG['admin.migration.wizard_done'] = 'Die Migration ist abgeschlossen: die Zertifikate des Altmoduls werden jetzt hier verwaltet, und das Altmodul ist abgeschaltet.';
$_LANG['admin.migration.wizard_continue'] = 'Weiter';
$_LANG['admin.migration.wizard_later_hint'] = 'Sie können die Migration auch später unter Addons > Migration ausführen; bis dahin arbeitet das Altmodul weiter.';
$_LANG['admin.migration.wizard_later'] = 'Später migrieren';
$_LANG['admin.configuration.content.step6.migration_pending'] = 'Die Migration des Altmoduls wurde noch nicht ausgeführt: beide Module sind aktiv, bis Sie sie unter <b>Addons > Migration</b> ausführen.';
$_LANG['admin.migration.nothing_left'] = 'Nichts mehr zu migrieren: die Produkte und Zertifikate dieses Altmoduls wurden durch die Migration unten übernommen.';

// main domain choice of FLEX products
$_LANG['admin.pricing_grid.item_main_wildcard'] = 'Wildcard-Hauptdomain (Aufpreis)';

// Settings > Email notifications
$_LANG['admin.settings.index.widgets.sidebar.emailSettings'] = 'E-Mail-Benachrichtigungen';
$_LANG['admin.settings.index.widgets.email_settings_widget.title'] = 'E-Mail-Benachrichtigungen';
$_LANG['admin.settings.index.widgets.email_settings_widget.subtitle'] = 'Alle E-Mails, die das Modul an Kunden sendet: jede einzeln ein- oder ausschalten. Die Texte bearbeiten Sie auf der Seite E-Mails.';
$_LANG['admin.settings.index.widgets.email_settings_widget.col_email'] = 'E-Mail';
$_LANG['admin.settings.index.widgets.email_settings_widget.col_when'] = 'Wird gesendet';
$_LANG['admin.settings.index.widgets.email_settings_widget.col_status'] = 'Status';
$_LANG['admin.settings.index.widgets.email_settings_widget.enabled'] = 'aktiv';
$_LANG['admin.settings.index.widgets.email_settings_widget.edit'] = 'Bearbeiten';
$_LANG['admin.emails.name_document_signing_configuration_required'] = 'Document Signing: Bestelldaten erforderlich';
$_LANG['admin.emails.when_document_signing_configuration_required'] = 'Wird gesendet, wenn ein Document-Signing-Dienst angelegt wird: Der Kunde gibt Organisation, Administrator und Token-Wahl ein.';
$_LANG['admin.emails.name_document_signing_certificate_issued'] = 'Document-Signing-Zertifikat ausgestellt';
$_LANG['admin.emails.when_document_signing_certificate_issued'] = 'Wird gesendet, wenn die Zertifizierungsstelle das Document-Signing-Zertifikat ausstellt.';
$_LANG['admin.emails.name_lei_configuration_required'] = 'LEI: Bestelldaten erforderlich';
$_LANG['admin.emails.when_lei_configuration_required'] = 'Wird gesendet, wenn ein LEI-Dienst angelegt wird oder seine Verlängerung den Kunden braucht: Der Kunde gibt die juristische Person und den Unterzeichner ein.';
$_LANG['admin.emails.name_lei_issued'] = 'LEI ausgestellt';
$_LANG['admin.emails.when_lei_issued'] = 'Wird gesendet, wenn die LEI-Vergabestelle den LEI-Code ausstellt oder verlängert.';
$_LANG['admin.emails.name_codeguard_licence_ready'] = 'CodeGuard-Lizenz bereit';
$_LANG['admin.emails.when_codeguard_licence_ready'] = 'Wird gesendet, wenn eine CodeGuard-Lizenz eingerichtet ist, mit dem Weg zu ihrem Dashboard.';
$_LANG['admin.emails.name_hackerguardian_pci_configuration_required'] = 'HackerGuardian PCI: Bestelldaten erforderlich';
$_LANG['admin.emails.when_hackerguardian_pci_configuration_required'] = 'Wird gesendet, wenn ein HackerGuardian-PCI-Dienst angelegt wird: Der Kunde gibt die Organisation und den Administrator des Scan-Kontos ein.';
$_LANG['admin.emails.name_hackerguardian_pci_scan_account_ready'] = 'HackerGuardian-PCI-Scan-Konto bereit';
$_LANG['admin.emails.when_hackerguardian_pci_scan_account_ready'] = 'Wird gesendet, wenn das Scan-Konto angelegt ist, mit Benutzername und Anmeldeseite (das Passwort steht nur auf der Dienstseite).';

// certificate revoked / expired e-mails
$_LANG['admin.emails.name_certificate_revoked'] = 'Zertifikat widerrufen';
$_LANG['admin.emails.when_certificate_revoked'] = 'Wird gesendet, wenn die Synchronisierung das Zertifikat als von der Zertifizierungsstelle widerrufen erkennt; der Dienst wird gekündigt.';
$_LANG['admin.emails.name_certificate_expired'] = 'Zertifikat abgelaufen';
$_LANG['admin.emails.when_certificate_expired'] = 'Wird gesendet, wenn die Synchronisierung das Zertifikat als abgelaufen und nicht verlängert erkennt; der Dienst wird beendet.';

// certificates sold as product addons
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_sellAsAddon'] = 'Als Produkt-Addon verkaufen';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_sell_as_addon'] = 'Ein WHMCS-Produkt-Addon anlegen, das dieses Zertifikat verkauft (gleicher Name und Preise)? Produkt-Addons haben keine konfigurierbaren Optionen: zusätzliche SANs und die FLEX-Wildcard-Wahl werden damit nicht verkauft. Auf der nächsten Seite wählen Sie die Produkte, zu denen es angeboten wird.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.product_addon'] = 'Produkt-Addon #:id';

// Katalog-Synchronisierung (M-16)
$_LANG['admin.settings.index.forms.products_settings_form.catalogueSync'] = 'Katalog-Synchronisierung';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueSync_desc'] = 'Jede Nacht vergleicht das Modul die Produktliste des Anbieters mit der vorherigen. Jede Änderung wird auf der Seite Produkte aufgeführt (Reiter "Katalogänderungen"). Der Modus legt fest, was das Modul selbst ändert:';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_manual'] = 'Manuell:';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_manual_text'] = 'nichts wird geändert, jede Änderung wird nur gemeldet.';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_alterations'] = 'Nur Anpassungen:';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_alterations_text'] = 'geänderte Produkte werden aktualisiert (SAN-Optionen, Produkteinstellungen); neue und eingestellte Produkte werden nur gemeldet.';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_alterations_removal'] = 'Anpassungen und Entfernen:';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_alterations_removal_text'] = 'setzt außerdem Produkte in den Ruhestand, die der Anbieter nicht mehr verkauft (an zwei verschiedenen Tagen fehlend). Ein Produkt mit aktiven Services wird nur im Bestellformular ausgeblendet; seine Services funktionieren weiter.';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_all'] = 'Alles:';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueMode_all_text'] = 'importiert außerdem neue Produkte, ausgeblendet und mit der Standardmarge, in eine ausgeblendete Gruppe, damit Sie sie vor dem Verkauf prüfen.';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueSyncMode'] = 'Synchronisierungsmodus';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueSyncMode_description'] = 'Was die nächtliche Synchronisierung selbst ändern darf. Die Schaltfläche Neu synchronisieren auf der Seite Produkte aktualisiert die Produkte immer.';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueChangesEmail'] = 'Administratoren per E-Mail informieren';
$_LANG['admin.settings.index.forms.products_settings_form.catalogueChangesEmail_description'] = 'Eine Zusammenfassung der Änderungen und dessen, was das Modul getan hat, nach der nächtlichen Synchronisierung. Keine Änderungen, keine E-Mail';
$_LANG['admin.settings.index.providers.products_settings_provider.catalogueSync_manual'] = 'Manuell';
$_LANG['admin.settings.index.providers.products_settings_provider.catalogueSync_alterations'] = 'Nur Anpassungen';
$_LANG['admin.settings.index.providers.products_settings_provider.catalogueSync_alterations_removal'] = 'Anpassungen und Entfernen';
$_LANG['admin.settings.index.providers.products_settings_provider.catalogueSync_all'] = 'Alles (auch neue Produkte importieren)';
$_LANG['admin.products.productsTab'] = 'Produkte';
$_LANG['admin.products.catalogueTab'] = 'Katalogänderungen';
$_LANG['admin.products.show_label'] = 'Anzeigen';
$_LANG['admin.products.show_all'] = 'Alle';
$_LANG['admin.products.show_imported'] = 'Importiert';
$_LANG['admin.products.show_not_imported'] = 'Nicht importiert';
$_LANG['admin.products.type_label'] = 'Validierung';
$_LANG['admin.products.type_all'] = 'Alle Typen';
$_LANG['admin.products.type_dv'] = 'DV';
$_LANG['admin.products.type_ov'] = 'OV';
$_LANG['admin.products.type_ev'] = 'EV';
$_LANG['admin.products.type_other'] = 'Andere';
$_LANG['admin.products.catalogue_intro'] = 'Änderungen in der Produktliste des Anbieters in den letzten :days Tagen, die neuesten zuerst, und was das Modul daraufhin getan hat.';
$_LANG['admin.products.catalogue_mode'] = 'Synchronisierungsmodus: :mode.';
$_LANG['admin.products.catalogue_settings'] = 'Synchronisierungsmodus ändern';
$_LANG['admin.products.catalogue_empty'] = 'Noch keine Änderungen in der Produktliste des Anbieters. Die nächtliche Synchronisierung führt hier jedes neue, geänderte und eingestellte Produkt auf.';
$_LANG['admin.products.catalogue_date'] = 'Datum';
$_LANG['admin.products.catalogue_type'] = 'Art';
$_LANG['admin.products.catalogue_product'] = 'Produkt';
$_LANG['admin.products.catalogue_change'] = 'Änderung';
$_LANG['admin.products.catalogue_before'] = 'Vorher';
$_LANG['admin.products.catalogue_now'] = 'Jetzt';
$_LANG['admin.products.catalogue_action'] = 'Was das Modul getan hat / was zu tun ist';

// Module update (App\Libs\Update)
$_LANG['admin.module_update.title'] = 'Modul-Update';
$_LANG['admin.module_update.notice_checked'] = 'Nach einer neuen Version gesucht.';
$_LANG['admin.module_update.notice_installed'] = 'Das Modul wurde auf Version :version aktualisiert.';
$_LANG['admin.module_update.notice_rolled_back'] = 'Version :version ist wieder installiert.';
$_LANG['admin.module_update.installing'] = 'Version :version wird installiert';
$_LANG['admin.module_update.step_prepare'] = 'Version herunterladen und prüfen';
$_LANG['admin.module_update.step_backup'] = 'Laufende Version sichern';
$_LANG['admin.module_update.step_install'] = 'Installieren, aktualisieren und prüfen';
$_LANG['admin.module_update.installing_wait'] = 'Lassen Sie diese Seite geöffnet. Jeder Schritt läuft von selbst weiter; das Modul bleibt bis zum letzten Schritt nutzbar, der wenige Sekunden dauert.';
$_LANG['admin.module_update.never'] = 'nie';
$_LANG['admin.module_update.installed'] = 'Installierte Version';
$_LANG['admin.module_update.newest'] = 'Neueste Version';
$_LANG['admin.module_update.release_page'] = 'Versionshinweise';
$_LANG['admin.module_update.checked'] = 'Zuletzt geprüft';
$_LANG['admin.module_update.check_now'] = 'Jetzt prüfen';
$_LANG['admin.module_update.available'] = 'Version :version ist verfügbar';
$_LANG['admin.module_update.checks'] = 'Serverprüfungen';
$_LANG['admin.module_update.check_signing'] = 'Signatur der Version prüfbar';
$_LANG['admin.module_update.check_verified'] = 'Version geprüft';
$_LANG['admin.module_update.check_manual'] = 'Version automatisch installierbar';
$_LANG['admin.module_update.check_php'] = 'PHP-Version';
$_LANG['admin.module_update.check_whmcs'] = 'WHMCS-Version';
$_LANG['admin.module_update.check_extensions'] = 'PHP-Erweiterungen';
$_LANG['admin.module_update.check_os'] = 'Betriebssystem';
$_LANG['admin.module_update.check_writable'] = 'Modulordner beschreibbar';
$_LANG['admin.module_update.check_disk'] = 'Modulordner auf einem Laufwerk';
$_LANG['admin.module_update.check_space'] = 'Freier Speicherplatz';
$_LANG['admin.module_update.check_opcache'] = 'OPcache';
$_LANG['admin.module_update.check_idle'] = 'Kein anderes Update läuft';
$_LANG['admin.module_update.check_loopback'] = 'Server kann die neue Version prüfen';
$_LANG['admin.module_update.check_changes'] = 'Keine von Hand geänderten Dateien';
$_LANG['admin.module_update.install_info'] = 'Die Version ist signiert und wird Datei für Datei geprüft, bevor etwas ersetzt wird. Die laufende Version wird zuerst gesichert; startet die neue nicht, wird die vorherige von selbst wiederhergestellt.';
$_LANG['admin.module_update.install'] = 'Version :version installieren';
$_LANG['admin.module_update.install_confirm'] = 'Version :version jetzt installieren? Das Modul pausiert einige Sekunden.';
$_LANG['admin.module_update.full_admin_only'] = 'Nur ein Vollständiger Administrator kann das Update starten.';
$_LANG['admin.module_update.manual_only'] = 'Ein automatisches Update ist auf diesem Server nicht möglich (siehe Prüfungen oben). Manuell aktualisieren:';
$_LANG['admin.module_update.cli_hint'] = 'Oder aktualisieren Sie über die Kommandozeile auf dem Server: Dort wird die neue Version selbst geprüft.';
$_LANG['admin.module_update.download'] = 'Version :version herunterladen';
$_LANG['admin.module_update.up_to_date'] = 'Das Modul ist aktuell.';
$_LANG['admin.module_update.rollback_title'] = 'Vorherige Version';
$_LANG['admin.module_update.rollback_unsafe'] = 'Diese Version hat die Datenbank so geändert, dass die vorherige Version damit nicht arbeiten kann; sie kann daher nicht automatisch zurückgesetzt werden.';
$_LANG['admin.module_update.rollback_info'] = 'Eine Sicherung der Version :version ist vorhanden. Das Zurücksetzen stellt deren Dateien wieder her; Einstellungen und Daten bleiben unverändert.';
$_LANG['admin.module_update.rollback'] = 'Auf :version zurücksetzen';
$_LANG['admin.module_update.rollback_confirm'] = 'Version :version jetzt wiederherstellen?';
$_LANG['admin.module_update.history'] = 'Update-Verlauf';
$_LANG['admin.module_update.h_date'] = 'Datum';
$_LANG['admin.module_update.h_change'] = 'Änderung';
$_LANG['admin.module_update.h_result'] = 'Ergebnis';
$_LANG['admin.module_update.recover_hint'] = 'Falls ein Update einmal mittendrin stoppt und das Modul fehlt, führen Sie auf dem Server aus: php :path';
$_LANG['admin.module_update.banner'] = 'Version :version des Moduls ist verfügbar (installiert: :installed).';
$_LANG['admin.module_update.banner_link'] = 'Ansehen und installieren';
$_LANG['admin.breadcrumbs.ModuleUpdate'] = 'Modul-Update';
$_LANG['admin.breadcrumbs.ModuleUpdate_index'] = 'Modul-Update';
$_LANG['admin.module_update.rollback_cli'] = 'Das Zurücksetzen ist auf diesem Server nicht über diese Seite möglich (siehe Serverprüfungen). Auf dem Server ausführen: :command';
$_LANG['admin.module_update.continue'] = 'Weiter';

// Settings > Products (catalogue sync moved here from Finance)
$_LANG['admin.settings.index.widgets.sidebar.productsSettings'] = 'Produkte';
$_LANG['admin.settings.index.widgets.products_settings_widget.title'] = 'Produkte';
$_LANG['admin.settings.index.widgets.products_settings_widget.subtitle'] = 'Wie Ihre Produktliste dem Katalog des Anbieters folgt';

// pages on the module's own kernel
$_LANG['admin.kernel.notFound']     = 'Diese Seite existiert nicht.';
$_LANG['admin.kernel.tokenExpired'] = 'Ihr Sitzungstoken ist abgelaufen. Laden Sie die Seite neu und versuchen Sie es erneut.';
$_LANG['admin.kernel.error']        = 'Die Seite konnte nicht angezeigt werden: :message';
$_LANG['admin.kernel.search'] = 'Suchen...';
$_LANG['admin.kernel.noRecords'] = 'Keine Einträge gefunden';
$_LANG['admin.kernel.confirm'] = 'Bestätigen';
$_LANG['admin.kernel.cancel'] = 'Abbrechen';
$_LANG['admin.kernel.yes'] = 'Ja';
$_LANG['admin.kernel.no'] = 'Nein';
$_LANG['admin.kernel.noFile'] = 'Keine Datei ausgewählt';
$_LANG['admin.kernel.browse'] = 'Durchsuchen';
$_LANG['admin.import.choose_file'] = 'Wählen Sie eine CSV-Datei aus.';
$_LANG['admin.import.fill_required'] = 'Geben Sie die Bestell-ID ein und wählen Sie den Kunden und die Zahlungsmethode.';
$_LANG['admin.import.api_not_configured'] = 'Die API ist noch nicht verbunden. Schließen Sie zuerst die Einrichtung des Moduls ab.';
$_LANG['admin.kernel.submit'] = 'Speichern';
$_LANG['admin.kernel.selected'] = 'Einträge ausgewählt';
$_LANG['admin.products.not_imported'] = 'Dieses Produkt ist noch nicht importiert.';
$_LANG['admin.products.select_first'] = 'Markieren Sie zuerst die Produkte, die importiert werden sollen.';
$_LANG['admin.products.choose_group'] = 'Wählen Sie eine Produktgruppe oder geben Sie der neuen Gruppe einen Namen.';
