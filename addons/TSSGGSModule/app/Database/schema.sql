
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
