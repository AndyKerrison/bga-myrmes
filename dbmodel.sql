
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- akMyrmes implementation : © Andrew Kerrison <adesignforlife@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_nurses` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_soldiers` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_workers` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_larvae` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_food` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_dirt` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_stone` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_colony_level` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `player` ADD `player_larvae_slots_allocated` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_worker_slots_allocated` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_soldier_slots_allocated` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_atelier_1_allocated` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_atelier_2_allocated` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_atelier_3_allocated` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_atelier_4_allocated` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `player` ADD `player_event_selected` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_workers_passed` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_colony_used` INT UNSIGNED NOT NULL DEFAULT '0';

--CREATE TABLE IF NOT EXISTS `board_state` (
--   `state_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `x` int(11) NOT NULL,
--   `y` int(11) NOT NULL,
--   `tile_id` int(10) NOT NULL   
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tiles` (
   `tile_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `player_id` int(11) NOT NULL,
   `type_id` int(11) NOT NULL,
   `rotation` int(11) NOT NULL,
   `color` varchar(16) NOT NULL,
   `location` varchar(16) NOT NULL,
   `x1` int(11) NOT NULL default 0,
   `y1` int(11) NOT NULL default 0,
   `x2` int(11) NOT NULL default 0,
   `y2` int(11) NOT NULL default 0,
   `x3` int(11) NOT NULL default 0,
   `y3` int(11) NOT NULL default 0,
   `x4` int(11) NOT NULL default 0,
   `y4` int(11) NOT NULL default 0,
   `x5` int(11) NOT NULL default 0,
   `y5` int(11) NOT NULL default 0,
   `x6` int(11) NOT NULL default 0,
   `y6` int(11) NOT NULL default 0,  
   PRIMARY KEY (`tile_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

