CREATE TABLE IF NOT EXISTS `#prefix#ProductConfiguration`
(
    `product_id` int
(
    11
),
    `type` enum
(
    'product',
    'product_addon'
) DEFAULT 'product',
    `setting` varchar
(
    255
),
    `value` text,
    PRIMARY KEY
(
    `product_id`,
    `type`,
    `setting`
)
    ) ENGINE=InnoDB DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;