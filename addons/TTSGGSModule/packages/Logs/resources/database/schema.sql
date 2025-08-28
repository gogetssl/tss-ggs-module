--
-- `#prefix#Logs`
--
CREATE TABLE IF NOT EXISTS `#prefix#Logs`
(
    `id` int
(
    10
) unsigned NOT NULL AUTO_INCREMENT,
    `type` VARCHAR
(
    255
) NOT NULL,
    `date` DATETIME DEFAULT null,
    `message` TEXT NOT NULL,
    `data` TEXT NOT NULL,
    `created_at` DATE DEFAULT null,
    `updated_at` DATE DEFAULT null,
    PRIMARY KEY
(
    `id`
)
    ) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

