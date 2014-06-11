-- Insert config variables into the config table

INSERT INTO config VALUES
("store_exapaq_account", 0),
("store_cellphone", ""),
("store_predict_option", NULL)
;

INSERT INTO `predict_freeshipping`(`active`, `created_at`, `updated_at`) VALUES (0, NOW(), NOW());