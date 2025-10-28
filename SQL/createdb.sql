/*
**************************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
*	   =============================================
*
* Пакетный файл: createdb.sql
* Функции:     Описание структуры базы данных
* ------------------------------------------------------------------------
* Авторство:   ИП "HOME LAB", Пенза (с), 2025
* Разработчик: Александр Васильков
* E-Mail:	    bac@sura.ru
* ------------------------------------------------------------------------
* Версия: 0.0.1
* Дата:   06.09.2025
**************************************************************************
 */

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS memorial;
CREATE SCHEMA IF NOT EXISTS `memorial` DEFAULT CHARACTER SET utf8;
SHOW WARNINGS;
USE `memorial`;

SET NAMES utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id`            INTEGER NOT NULL AUTO_INCREMENT 
                  PRIMARY KEY                       COMMENT 'Id пользователя',
  `username`      VARCHAR(255) NOT NULL UNIQUE KEY  COMMENT 'Имя пользователя',
  `last_name`     VARCHAR(255) NOT NULL             COMMENT 'Фамилия',
  `first_name`    VARCHAR(255) NOT NULL             COMMENT 'Имя',
  `middle_name`   VARCHAR(255) NOT NULL             COMMENT 'Отчество',
  `auth_key`      VARCHAR(32) NOT NULL              COMMENT 'Код авторизации',
  `password_hash` VARCHAR(255) NOT NULL             COMMENT 'Хэш-свёртка пароля',
  `email`         VARCHAR(255) NOT NULL UNIQUE KEY  COMMENT 'Почта',
  `role`          ENUM('User','Moderator','Admin', 'Develop') 
                  NOT NULL DEFAULT 'User'           COMMENT 'Роль пользователя',
  `status`        SMALLINT NOT NULL DEFAULT '2'     COMMENT 'Статус 0 - заблокирован; 1 - активен; 2 - ожидает подтверждения',
  `created_at`    TIMESTAMP 
                  DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT 'Дата и время создания записи',
  `updated_at`    TIMESTAMP                         COMMENT 'Дата и время обновления записи'
) ENGINE=InnoDB COMMENT='Пользователи';

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
	logId INTEGER AUTO_INCREMENT PRIMARY KEY COMMENT 'id записи в журнале',
	dtLog  TIMESTAMP NOT NULL
          DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время записи',
	userId INTEGER		    COMMENT 'id пользователя',
	`code` VARCHAR(10)	  COMMENT 'Код сообщения',
	msg  VARCHAR(1024)	  COMMENT 'Текст сообщения'
) ENGINE=MyISAM COMMENT='Журнал действий пользователей';

DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
	code	VARCHAR(6) PRIMARY KEY	COMMENT 'Код опции',
	val	  VARCHAR(128)				    COMMENT 'Значение',
  `opt_at` TIMESTAMP
          DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время записи'
) ENGINE=MyISAM COMMENT='Опции';
REPLACE `options` (code, val) VALUES('vers', '0.0.1'); -- Версия БД

-- Справочник статусов бойцов
CREATE TABLE `fighter_status` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'id записи',
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `color` CHAR(7),
    `description` TEXT
) ENGINE=InnoDB COMMENT='Статусы бойцов';

-- Справочник воинских званий
CREATE TABLE `military_rank` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'id записи',
    `name` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Наименование звания',
    `category` ENUM('before_1943', 'after_1943') NOT NULL COMMENT 'Период введения',
    `rank_order` INT NOT NULL COMMENT 'Порядок звания для сортировки',
    `description` TEXT COMMENT 'Описание звания'
) ENGINE=InnoDB COMMENT='Воинские звания';

-- Справочник воинских наград
CREATE TABLE `military_award` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'id записи',
    `name` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Наименование награды',
    `description` TEXT COMMENT 'Описание награды',
    `institution_date` VARCHAR(50) COMMENT 'Дата учреждения',
    `award_svg` MEDIUMBLOB COMMENT 'SVG изображение награды',
    `status` ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Статус награды',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания'
) ENGINE=InnoDB COMMENT='Воинские награды';

-- Основная таблица бойцов
CREATE TABLE `fighter` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'id записи',
    `user_id` INT NOT NULL COMMENT 'id пользователя создавшего запись',
    `status_id` INT NOT NULL COMMENT 'id состояния записи бойце',       
    `returnStatus` ENUM('returned', 'died', 'missing') NOT NULL COMMENT 'Признак судьбы бойца', 
    `last_name` VARCHAR(100) NOT NULL COMMENT 'Фамилия',
    `first_name` VARCHAR(100) NOT NULL COMMENT 'Имя',
    `middle_name` VARCHAR(100) COMMENT 'Отчество',
    
    -- Дата рождения (может быть частично заполнена)
    `birth_year` SMALLINT COMMENT 'Год рождения',
    `birth_month` TINYINT COMMENT 'Месяц рождения (1-12)',
    `birth_day` TINYINT COMMENT 'День рождения (1-31)',
    
    `death_year` SMALLINT COMMENT 'Год смерти',
    `birth_place` VARCHAR(500) COMMENT 'Место рождения',
    `conscription_place` VARCHAR(500) COMMENT 'Место призыва',
    `military_unit` VARCHAR(500) COMMENT 'Воинская часть',
    
    -- Ссылка на справочник званий
    `military_rank_id` INT COMMENT 'Воинское звание',
    
    `biography` TEXT COMMENT 'Биография',
    `burial_place` VARCHAR(500) COMMENT 'Место захоронения',
    `additional_info` TEXT COMMENT 'Дополнительная информация',
    
    `created_at` TIMESTAMP NOT NULL 
                 DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания записи',
    `updated_at` TIMESTAMP COMMENT 'Дата и время обновления записи',
    `moderated_at` TIMESTAMP COMMENT 'Дата и время модерирования',
    `moderator_id` INT COMMENT 'id записи пользователя модератора',
    
    FOREIGN KEY (`status_id`) REFERENCES `fighter_status`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`),
    FOREIGN KEY (`moderator_id`) REFERENCES `user`(`id`),
    FOREIGN KEY (`military_rank_id`) REFERENCES `military_rank`(`id`),
    
    INDEX `idx_name` (`last_name`, `first_name`),
    INDEX `idx_status` (`status_id`),
    INDEX `idx_birth_date` (`birth_year`, `birth_month`, `birth_day`)
) ENGINE=InnoDB COMMENT='Бойцы';

-- Таблица пленений бойцов
CREATE TABLE `fighter_capture` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'id записи',
    `fighter_id` INT NOT NULL COMMENT 'id записи бойца',
    `capture_date` DATE COMMENT 'Дата пленения',
    `capture_place` VARCHAR(500) COMMENT 'Место пленения',
    `camp_name` VARCHAR(255) COMMENT 'Название лагеря',
    `capture_circumstances` TEXT COMMENT 'Обстоятельства пленения',
    `liberated_date` DATE COMMENT 'Дата освобождения',
    `liberated_by` VARCHAR(255) COMMENT 'Кем освобожден',
    `liberation_circumstances` TEXT COMMENT 'Обстоятельства освобождения',
    `duration_days` INT COMMENT 'Продолжительность плена в днях',
    `additional_info` TEXT COMMENT 'Дополнительная информация о пленении',
    `created_at` TIMESTAMP NOT NULL 
                 DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    `updated_at` TIMESTAMP COMMENT 'Дата обновления',
    
    FOREIGN KEY (`fighter_id`) REFERENCES `fighter`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_fighter` (`fighter_id`),
    INDEX `idx_capture_date` (`capture_date`),
    INDEX `idx_liberated_date` (`liberated_date`)
) ENGINE=InnoDB COMMENT='Пленения бойцов';

-- Таблица наград бойцов
CREATE TABLE `fighter_award` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'id записи',
    `fighter_id` INT NOT NULL COMMENT 'id записи бойца',
    `award_id` INT NOT NULL COMMENT 'id награды из справочника',
    `award_date` VARCHAR(100) COMMENT 'Дата награждения',
    `award_reason` TEXT COMMENT 'За что награжден',
    `document_photo` LONGBLOB COMMENT 'Фото наградного документа',
    `document_mime_type` VARCHAR(50) COMMENT 'Тип документа',
    `document_description` TEXT COMMENT 'Описание документа',
    `created_at` TIMESTAMP NOT NULL 
                 DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
    `updated_at` TIMESTAMP COMMENT 'Дата обновления',
    
    FOREIGN KEY (`fighter_id`) REFERENCES `fighter`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`award_id`) REFERENCES `military_award`(`id`),
    
    INDEX `idx_fighter` (`fighter_id`),
    INDEX `idx_award` (`award_id`)
) ENGINE=InnoDB COMMENT='Награды бойцов';

-- Фотографии бойцов
CREATE TABLE `fighter_photo` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'id записи',
    `fighter_id` INT NOT NULL COMMENT 'id записи бойца',
    `photo_data` LONGBLOB NOT NULL COMMENT 'Фото бойца',
    `thumbnail_data` MEDIUMBLOB COMMENT 'Эскиз фото',
    `mime_type` VARCHAR(50) NOT NULL COMMENT 'Тип фото',
    `file_size` INT NOT NULL COMMENT 'Размер фото',
    `description` TEXT  COMMENT 'Описание фото',
    `ai_description` TEXT COMMENT 'Описание от ИИ',
    `photo_year` SMALLINT COMMENT 'Год фотографии',
    `is_main` TINYINT DEFAULT 0 COMMENT 'Признак главной фотографии',
    `status` ENUM('pending', 'approved', 'rejected') 
             DEFAULT 'pending' COMMENT 'Статус модерации фотографии',
    `created_at` TIMESTAMP NOT NULL 
                 DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания записи',
    `updated_at` TIMESTAMP COMMENT 'Дата и время обновления записи',
    `moderated_at` TIMESTAMP COMMENT 'Дата и время модерирования',
    `moderator_id` INT COMMENT 'id записи пользователя модератора',
    
    FOREIGN KEY (`fighter_id`) REFERENCES `fighter`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`moderator_id`) REFERENCES `user`(`id`),
    
    INDEX `idx_fighter_status` (`fighter_id`, `status`)
) ENGINE=InnoDB COMMENT='Фотографии';

-- Боевой путь
CREATE TABLE `combat_path` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'id записи',
    `fighter_id` INT NOT NULL COMMENT 'id записи бойца',
    `front_name` VARCHAR(255) NOT NULL COMMENT 'Фронт',
    `battle_description` TEXT COMMENT 'Описание участия в сражениях',
    `start_year` SMALLINT COMMENT 'Год начала',
    `end_year` SMALLINT COMMENT 'Года окончания',
    `awards_received` TEXT COMMENT 'Описание наград',
    `created_at` TIMESTAMP NOT NULL 
                 DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время создания записи',
    FOREIGN KEY (`fighter_id`) REFERENCES `fighter`(`id`) ON DELETE CASCADE,
    INDEX `idx_fighter` (`fighter_id`)
) ENGINE=InnoDB COMMENT='Боевой путь';

-- Представление профиля пользователя с расширенной статистикой
CREATE VIEW `profile` AS
SELECT 
    u.`id`,
    u.`username`,
    u.`last_name`,
    u.`first_name`,
    u.`middle_name`,
    u.`email`,
    u.`role`,
    u.`status` as user_status,
    u.`created_at`,
    u.`updated_at`,
    
    -- Статистика по бойцам пользователя
    COUNT(DISTINCT f.`id`) as total_fighters,
    COUNT(DISTINCT CASE WHEN f.`status_id` = 1 THEN f.`id` END) as returned_fighters,
    COUNT(DISTINCT CASE WHEN f.`status_id` = 2 THEN f.`id` END) as killed_fighters,
    COUNT(DISTINCT CASE WHEN f.`status_id` = 3 THEN f.`id` END) as missing_fighters,
    
    -- Статистика по фотографиям
    COUNT(DISTINCT fp.`id`) as total_photos,
    COUNT(DISTINCT CASE WHEN fp.`status` = 'approved' THEN fp.`id` END) as approved_photos,
    COUNT(DISTINCT CASE WHEN fp.`status` = 'pending' THEN fp.`id` END) as pending_photos,
    COUNT(DISTINCT CASE WHEN fp.`status` = 'rejected' THEN fp.`id` END) as rejected_photos,
    
    -- Статистика по наградам
    COUNT(DISTINCT fa.`id`) as total_awards,
    
    -- Статистика по боевому пути
    COUNT(DISTINCT cp.`id`) as combat_path_records,
    
    -- Статистика по пленениям
    COUNT(DISTINCT fc.`id`) as capture_records,
    
    -- Дата последнего добавленного бойца
    MAX(f.`created_at`) as last_fighter_added,
    
    -- Количество дней с регистрации
    DATEDIFF(CURRENT_DATE(), u.`created_at`) as days_since_registration

FROM `user` u
LEFT JOIN `fighter` f ON u.`id` = f.`user_id`
LEFT JOIN `fighter_photo` fp ON f.`id` = fp.`fighter_id`
LEFT JOIN `fighter_award` fa ON f.`id` = fa.`fighter_id`
LEFT JOIN `combat_path` cp ON f.`id` = cp.`fighter_id`
LEFT JOIN `fighter_capture` fc ON f.`id` = fc.`fighter_id`
GROUP BY 
    u.`id`, u.`username`, u.`last_name`, u.`first_name`, u.`middle_name`,
    u.`email`, u.`role`, u.`status`, u.`created_at`, u.`updated_at`;

-- Представление для отображения основных данных бойцов с фото
CREATE VIEW `fighter_view` AS
SELECT 
    f.`id`,
    f.`user_id`,
    f.`status_id`,
    fs.`name` as status_name,
    fs.`color` as status_color,
    f.`last_name`,
    f.`first_name`,
    f.`middle_name`,
    CONCAT_WS(' ', f.`last_name`, f.`first_name`, f.`middle_name`) as full_name,
    
    -- Форматированная дата рождения
    CASE 
        WHEN f.`birth_year` IS NOT NULL AND f.`birth_month` IS NOT NULL AND f.`birth_day` IS NOT NULL 
            THEN CONCAT(f.`birth_day`, '.', f.`birth_month`, '.', f.`birth_year`)
        WHEN f.`birth_year` IS NOT NULL AND f.`birth_month` IS NOT NULL 
            THEN CONCAT('..', f.`birth_month`, '.', f.`birth_year`)
        WHEN f.`birth_year` IS NOT NULL 
            THEN CAST(f.`birth_year` AS CHAR)
        ELSE 'неизвестно'
    END as birth_date_formatted,
    
    f.`birth_year`,
    f.`birth_month`,
    f.`birth_day`,
    f.`death_year`,
    f.`birth_place`,
    f.`conscription_place`,
    f.`military_unit`,
    
    mr.`name` as military_rank_name,
    mr.`category` as military_rank_category,
    
    f.`biography`,
    f.`burial_place`,
    f.`additional_info`,
    
    -- Главная фотография
    fp.`id` as main_photo_id,
    fp.`thumbnail_data`,
    fp.`mime_type`,
    fp.`description` as photo_description,
    fp.`status` as photo_status,
    
    f.`created_at`,
    f.`updated_at`,
    f.`moderated_at`,
    f.`moderator_id`

FROM `fighter` f
LEFT JOIN `fighter_status` fs ON f.`status_id` = fs.`id`
LEFT JOIN `military_rank` mr ON f.`military_rank_id` = mr.`id`
LEFT JOIN `fighter_photo` fp ON (
    f.`id` = fp.`fighter_id` 
    AND fp.`is_main` = 1 
    AND fp.`status` = 'approved'
)
WHERE f.`status_id` IS NOT NULL;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;