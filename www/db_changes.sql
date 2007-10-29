-- Neu!!!!!!!!! Nicht mehr hier eintragen, sondern in zuordnen.php
-- function update_database().
-- Dazu gibt es neu die Leitvariable database_version.
-- Incrementiert jeweils um 1. Wenn nicht gesetzt, wird 0 angenommen.

INSERT INTO `nahrungskette`.`leitvariable` (
`name` , `value` , `local` , `comment`
) VALUES (
'basar_id', '99', '0', 'Gruppen-ID der besonderen Basar-Gruppe'
);

INSERT INTO `nahrungskette`.`leitvariable` (
`name` , `value` , `local` , `comment`
) VALUES (
'muell_id', '13', '0', 'Gruppen-ID der besonderen Muell-Gruppe'
);


CREATE TABLE `bankkonto` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
 `kontoauszug_jahr` SMALLINT NOT NULL, 
 `kontoauszug_nr` SMALLINT NOT NULL, 
 `eingabedatum` DATE NOT NULL, 
 `gruppen_id` INT NOT NULL,
 `lieferanten_id` INT NOT NULL,
 `dienstkontrollblatt_id` INT NOT NULL,
 `betrag` DECIMAL(10,2) NOT NULL,
 `konto_id` smallint(4) NOT NULL,
 `kommentar` TEXT NOT NULL,
 `konterbuchung_id` INT NOT NULL,
  KEY `secondary` (`konto_id`, `kontoauszug_jahr`,`kontoauszug_nr`)
 )
 ENGINE = myisam DEFAULT CHARACTER SET utf8 COMMENT = 'Bankkontotransaktionen';

ALTER TABLE `gruppen_transaktion` ADD `konterbuchung_id` INT NOT NULL DEFAULT '0';
ALTER TABLE `gruppen_transaktion` ADD `lieferanten_id` INT NOT NULL DEFAULT '0';

CREATE TABLE `bankkonten` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` TEXT NOT NULL ,
`kontonr` TEXT NOT NULL ,
`blz` TEXT NOT NULL
) ENGINE = MYISAM ;

-- ab hier: alt...
--
-- ALTER TABLE `bestellgruppen` ADD `diensteinteilung` ENUM( "1/2", "3", "4", "5", "freigestellt" ) NOT NULL DEFAULT 'freigestellt',
-- ADD `rotationsplanposition` INT NOT NULL DEFAULT '0';
-- 
-- ALTER TABLE `bestellgruppen` ADD INDEX ( `rotationsplanposition` ) ;
-- 
-- CREATE TABLE `Dienste` (
-- `ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
-- `GruppenID` INT NOT NULL ,
-- `Dienst` ENUM( "1/2", "3", "4", "5", "freigestellt" ) NOT NULL ,
-- `Lieferdatum` DATE NOT NULL ,
-- `Status` ENUM( "Vorgeschlagen", "Akzeptiert", "Bestätigt", "Geleistet", "Nicht geleistet", "Offen" ) NOT NULL ,
-- `Bemerkung` TEXT NULL ,
-- INDEX ( `GruppenID` , `Dienst` )
-- ) ENGINE = MYISAM COMMENT = 'Enthält Dienste für jedes einzelne Lieferdatum Enthält Dienste für j. Lieferdat';
-- 
-- ALTER TABLE `gesamtbestellungen` CHANGE `lieferung` `lieferung` DATE NULL DEFAULT NULL  
-- 
-- 
-- 
-- ALTER TABLE `dienstkontrollblatt`
--   ADD `datum` date NOT NULL default '0000-00-00',
--   CHANGE `zeit` `zeit` TIME NOT NULL DEFAULT '0000-00-00 00:00:00',
--   ADD UNIQUE KEY `secondary` (`gruppen_id` (10),`dienst`,`datum`) ;
-- 
-- 
-- INSERT INTO `nahrungskette`.`leitvariable` (
-- `name` ,
-- `value` ,
-- `local` ,
-- `comment`
-- )
-- VALUES (
-- 'basar_id', '99', '0', 'Gruppen-ID der besonderen Basar-Gruppe'
-- );
-- 
-- INSERT INTO `nahrungskette`.`leitvariable` (
-- `name` ,
-- `value` ,
-- `local` ,
-- `comment`
-- )
-- VALUES (
-- 'sockelbetrag', '6.00', '0', 'Sockelbeitrag pro Gruppenmitglied'
-- );
-- 
-- INSERT INTO `leitvariable` ( `name` , `value` , `local` , `comment` )
-- VALUES (
-- 'crypt_salt', '35464', '0', 'Salz fuer crypt()'
-- );
-- 
-- -- bestellvorschlaege hatte bisher keinen index; dieser sollte gut sein fuer die performance:
-- --
-- ALTER TABLE `bestellvorschlaege` ADD PRIMARY KEY ( `gesamtbestellung_id` , `produkt_id` );
-- 
-- -- bestellzuordnung: index fuer bessere performance (und vielleicht auch irgendwann mal UNIQUE):
-- --
-- ALTER TABLE `bestellzuordnung` ADD INDEX `secondary` ( `produkt_id` , `gruppenbestellung_id` , `art` );
--  
-- -- Wunsch von dienst 4: kontoauszug im Format "Jahr / Nr" eingeben:
-- --
-- ALTER TABLE `gruppen_transaktion` ADD `kontoauszugs_jahr` SMALLINT( 5 ) UNSIGNED NOT NULL;
-- 
--  
