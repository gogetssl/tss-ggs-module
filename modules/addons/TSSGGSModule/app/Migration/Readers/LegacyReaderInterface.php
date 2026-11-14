<?php

namespace ModulesGarden\TSSGGSModule\App\Migration\Readers;

/**
 * A reader knows one legacy module's database layout and produces vendor-neutral records.
 *
 * Service record (array) produced by readService():
 *   service_id, client_id, product_id (tblproducts.id), ssl_order_id (tblsslorders.id)
 *   kind            'ssl' | 'acme' | 'ais' | 'unsupported'
 *   legacy_status   tblsslorders.status as found
 *   target_status   value to write to tblsslorders.status ('Awaiting Configuration' | 'Configuration Submitted' | inactive legacy value)
 *   remote_id       value for tblsslorders.remoteid (item id when known, else order id)
 *   preordered      true when a remote order already exists for an unconfigured service (TSS invited orders)
 *   order_payload   array in the gateway POST /certificates shape (product, csr, common_name, alternative_names, contacts) or null
 *   certificate     array ['orderData' => <gateway order shape + 'orderData' => order_payload>, 'orderFiles' => <files shape>] or null
 *   request_status  gateway order status for the Requests rows (active|pending|processing|cancelled|expired|...)
 *   prices          ['api_price' => float, 'whmcs_price' => float, 'invoiceid' => int]
 *   autoinstall     array (unified autoinstall state without encryption applied) or null
 *   private_key     PEM or '' (encrypted by the writer)
 *   queue           list of queue tasks [['type' => validate|install|reissue, 'data' => [...]]]
 *   subscription    ['acme' => [...row, 'domains' => [...]]] or ['ais' => [...row]] or null
 *   warnings        list of strings (migrated, but worth knowing)
 *   skip_reason     string when the service cannot be migrated (then kind may still be set)
 *
 * Product record produced by readProduct():
 *   pid, legacy_product_ref (api id or code), included_san, included_san_wildcard, price_auto (bool),
 *   san_options => [['id' => optionid, 'role' => 'sans'|'sans_wildcard', 'display' => current display name]],
 *   config_group_id, warnings
 */
interface LegacyReaderInterface
{
    /** 'ggs' | 'tss' */
    public function source(): string;

    /** Legacy module display name, e.g. "GoGetSSL (SSLCENTERWHMCS)" */
    public function label(): string;

    /** tblproducts.servertype value of the legacy module */
    public function serverType(): string;

    /** tblsslorders.module values of the legacy module (incl. older names) */
    public function orderModules(): array;

    /** addon module name in tbladdonmodules */
    public function addonName(): string;

    /** Legacy module tables that exist on this install (for backups) */
    public function tables(): array;

    /** Version string or null when it cannot be determined */
    public function version(): ?string;

    /** ['supported' => bool, 'band' => 'full'|'partial'|'unsupported', 'reason' => string] */
    public function versionSupport(): array;

    /** Legacy settings as unified sslSettings / financeSettings (only keys that have a legacy value) */
    public function readSettings(): array;

    /**
     * Gateway credentials of the legacy module as a unified block: PartnerCode, AuthToken and, for TheSSLStore,
     * TestPartnerCode, TestAuthToken, OperationMode (live|sandbox). Empty strings when not configured.
     */
    public function readCredentials(): array;

    /** Product record for one legacy product */
    public function readProduct(int $pid): array;

    /** Service record for one tblhosting row of a legacy product */
    public function readService(int $serviceId): array;

    /**
     * Counts for the detection card: products, services by domainstatus, orders by status, subscriptions,
     * autoinstall, unconfigured_preordered, unsupported, other_ssl_modules
     */
    public function counts(): array;

    /** Legacy cron command lines (for the "remove these" list), as far as the module documents them */
    public function cronLines(): array;

    /**
     * Stops legacy automation that would interfere after the switch (e.g. a product sync that re-creates
     * products). Called once in the switch phase; must be undone by the rollback through the table backups.
     */
    public function freeze(): array;

    /** Tables freeze() changes: backed up before it runs, so a rollback restores them as they were */
    public function freezeTables(): array;
}
