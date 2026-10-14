
-- `#prefix#RemoteProducts`
--
CREATE TABLE IF NOT EXISTS `#prefix#RemoteProducts` (
     `id`                     int(10) unsigned NOT NULL AUTO_INCREMENT,
     `remoteId`               varchar(255) NOT NULL,
     `name`                   varchar(255) NOT NULL,
     `description`            text NOT NULL,
     `vendor`                 varchar(255) NOT NULL,
     `brand`                  varchar(255) NOT NULL,
     `validation`             varchar(255) NOT NULL,
     `category`               varchar(255) NOT NULL,
     `rawData`                json NOT NULL,
     `created_at`             datetime NOT NULL,
     `updated_at`             datetime NOT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

CREATE TABLE IF NOT EXISTS `#prefix#Requests` (
    `id`                     int(10) unsigned NOT NULL AUTO_INCREMENT,
    `invoiceid`              int(10) NULL,
    `serviceid`              int(255) NOT NULL,
    `name`                   varchar(255) NOT NULL,
    `api_price`              varchar(255) NULL,
    `rate`                   varchar(255) NULL,
    `whmcs_price`            varchar(255) NULL,
    `diff_price`             varchar(255) NULL,
    `status`                 varchar(255) NULL,
    `request`                text NOT NULL,
    `created_at`             datetime NOT NULL,
    `updated_at`             datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

CREATE TABLE IF NOT EXISTS `#prefix#CronsCheck` (
    `id`                     int(10) unsigned NOT NULL AUTO_INCREMENT,
    `type`                   varchar(255) NOT NULL,
    `last_run`               varchar(255) NULL,
    `last_error`             text NOT NULL,
    `created_at`             datetime NOT NULL,
    `updated_at`             datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `#prefix#DemonTask`
--
CREATE TABLE IF NOT EXISTS `#prefix#DemonTask` (
    `id`               int(10)      unsigned                NOT NULL AUTO_INCREMENT,
    `session_id`       VARCHAR(128)                         NOT NULL,
    `service_id`       VARCHAR(255)                         NOT NULL,
    `status`           enum('waiting','ready', 'queue', 'processing')              NOT NULL                  DEFAULT 'waiting',
    `created_at`       timestamp                            NULL                      DEFAULT '0000-00-00 00:00:00',
    `updated_at`       timestamp                            NULL                      DEFAULT '0000-00-00 00:00:00',
    `deleted_at`       timestamp                            NULL                      DEFAULT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `#prefix#AcmeSubscriptions`
--
CREATE TABLE IF NOT EXISTS `#prefix#AcmeSubscriptions` (
    `id`               int(10) unsigned                NOT NULL AUTO_INCREMENT,
    `service_id`       int(10) unsigned                NOT NULL UNIQUE,
    `client_id`        int(10) unsigned                NULL,
    `api_order_id`     int(10) unsigned                NULL,
    `acme_id`          int(10) unsigned                NULL,
    `status`           VARCHAR(255)                    NOT NULL DEFAULT 'pending',
    `acme_account_id`  VARCHAR(255)                    NULL,
    `eab_kid`          VARCHAR(255)                    NULL,
    `eab_hmac_key`     text                            NULL,
    `server_url`       text                            NULL,
    `period_start`     date                            NULL,
    `period_end`       date                            NULL,
    `renewal_date`     date                            NULL,
    `auto_renew`       boolean                         NOT NULL DEFAULT 1,
    `cancelled_at`     datetime                        NULL,
    `created_at`       timestamp                       NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       timestamp                       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       timestamp                       NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;


--
-- `#prefix#AcmeSubscriptionDomains`
--
CREATE TABLE IF NOT EXISTS `#prefix#AcmeSubscriptionDomains` (
    `id`            int(10) unsigned       NOT NULL AUTO_INCREMENT,
    `service_id`    int(10) unsigned       NOT NULL,
    `domain`        varchar(255)           NOT NULL,
    `domain_type`   varchar(255)           NOT NULL DEFAULT 'single',
    `status`        varchar(255)           NOT NULL DEFAULT 'added',
    `added_at`      datetime               NULL,
    `removed_at`    datetime               NULL,
    `created_at`    timestamp              NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    timestamp              NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_service_domain` (`service_id`, `domain`),
    KEY `idx_service_id` (`service_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `#prefix#AisSubscriptions`
--
CREATE TABLE IF NOT EXISTS `#prefix#AisSubscriptions` (
    `id`                    int(10) unsigned    NOT NULL AUTO_INCREMENT,
    `service_id`            int(10) unsigned    NOT NULL UNIQUE,
    `client_id`             int(10) unsigned    NULL,
    `api_order_id`          int(10) unsigned    NULL,
    `ais_item_id`           int(10) unsigned    NULL,
    `status`                VARCHAR(255)        NOT NULL DEFAULT 'pending',
    `login_sso_link`        text                NULL,
    `manage_sso_link`       text                NULL,
    `autoinstall_status`    VARCHAR(255)        NULL,
    `period_start`          date                NULL,
    `period_end`            date                NULL,
    `renewal_date`          date                NULL,
    `auto_renew`            boolean             NOT NULL DEFAULT 1,
    `cancelled_at`          datetime            NULL,
    `created_at`            timestamp           NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            timestamp           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;