--
-- `#prefix#ModuleSettings`
--
CREATE TABLE IF NOT EXISTS `#prefix#ModuleSettings`
(
    `setting` VARCHAR
(
    64
) NOT NULL UNIQUE,
    `value` TEXT NOT NULL,
    PRIMARY KEY
(
    `setting`
)
    ) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;