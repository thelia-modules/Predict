-- Insert config variables into the config table

REPLACE INTO `config`(`name`, `value`) VALUES
("store_exapaq_account", 0),
("store_cellphone", ""),
("store_predict_option", NULL),
("predict_freeshipping", FALSE)
;

-- ---------------------------------------------------------------------
-- Mail templates for predict
-- ---------------------------------------------------------------------

-- First, delete existing entries
SET @var := 0;
SELECT @var := `id` FROM `message` WHERE name="mail_predict";
DELETE FROM `message` WHERE `id`=@var;
-- Try if ON DELETE constraint isn't set
DELETE FROM `message_i18n` WHERE `id`=@var;

-- Then add new entries
SELECT @max := MAX(`id`) FROM `message`;
SET @max := @max+1;
-- insert message
INSERT INTO `message` (`id`, `name`, `secured`) VALUES
(@max,
'mail_predict',
'0'
);

-- and template fr_FR
INSERT INTO `message_i18n` (`id`, `locale`, `title`, `subject`, `text_message`, `html_message`) VALUES
  (@max, 'en_US', 'Predict shipping message', 'Your order {$order_ref} has been shipped', '{loop type="customer" name="customer.order" current="false" id="$customer_id" backend_context="1"}\nDear {$FIRSTNAME} {$LASTNAME},\n{/loop}\nThank you for your order on our online store {config key="store_name"}.\nYour order {$order_ref} dated {format_date date=$order_date} has been shipped on {format_date date=$update_date}.\nThe tracking number for this delivery is {$package}. Please check the Exapaq website for tracking your parcel: http://www.exapaq.com/index.php/fr/Suivi-colis.\nYou will receive a SMS from Predict by Exapaq to give you two time slots where they may deliver your order, you will have to choose one.\nFeel free to contact us for any forther information\nBest Regards.', '{loop type="customer" name="customer.order" current="false" id="$customer_id" backend_context="1"}\r\n<p>Dear {$FIRSTNAME} {$LASTNAME},</p>\r\n{/loop}\r\n<p>Thank you for your order on our online store {config key="store_name"}.</p>\r\n<p>Your order {$order_ref} dated {format_date date=$order_date} has been shipped on {format_date date=$update_date}.\r\nThe tracking number for this delivery is {$package}. Please check the Exapaq website for tracking your parcel: <a href="http://www.exapaq.com/index.php/fr/Suivi-colis">http://www.exapaq.com/index.php/fr/Suivi-colis</a>.</p>\r\n<p>You will receive a SMS from Predict by Exapaq to give you two time slots where they may deliver your order, you will have to choose one.</p>\r\n<p>Feel free to contact us for any forther information</p>\r\n<p>Best Regards.</p>'),
  (@max, 'fr_FR', 'Message d''expédition de Predict', 'Suivi Predict commande : {$order_ref}', '{loop type="customer" name="customer.order" current="false" id="$customer_id" backend_context="1"}\r\n{$LASTNAME} {$FIRSTNAME},\r\n{/loop}\r\nNous vous remercions de votre commande sur notre site {config key="store_name"}\r\nUn colis concernant votre commande {$order_ref} du {format_date date=$order_date} a quitté nos entrepôts pour être pris en charge par Exapaq le {format_date date=$update_date}.\r\nSon numéro de suivi est le suivant : {$package}\r\nIl vous permet de suivre votre colis en ligne sur le site d''Exapaq: http://www.exapaq.com/index.php/fr/Suivi-colis\r\nVous allez recevoir un SMS de Predict par Exapaq vous proposant deux créneaux horaires pour recevoir votre colis, vous pourrez alors en choisir un.\r\nNous restons à votre disposition pour toute information complémentaire.\r\nCordialement', '{loop type="customer" name="customer.order" current="false" id="$customer_id" backend_context="1"}\r\n{$LASTNAME} {$FIRSTNAME},\r\n{/loop}\r\nNous vous remercions de votre commande sur notre site {config key="store_name"}\r\nUn colis concernant votre commande {$order_ref} du {format_date date=$order_date} a quitté nos entrepôts pour être pris en charge par Exapaq le {format_date date=$update_date}.\r\nSon numéro de suivi est le suivant : {$package}\r\nIl vous permet de suivre votre colis en ligne sur le site d''Exapaq: http://www.exapaq.com/index.php/fr/Suivi-colis\r\nVous allez recevoir un SMS de Predict par Exapaq vous proposant deux créneaux horaires pour recevoir votre colis, vous pourrez alors en choisir un.\r\nNous restons à votre disposition pour toute information complémentaire.\r\nCordialement');