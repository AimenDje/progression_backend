DROP PROCEDURE IF EXISTS migration;
DELIMITER &&
  CREATE PROCEDURE migration()
  proc: BEGIN
		  SET @version := (SELECT `version` FROM `version`);
		  IF @version >= 13 THEN
			LEAVE proc;
		  END IF;

		  START TRANSACTION;
		

		  CREATE TABLE `banque` (
			`id`				int(11) NOT NULL,
			`nom` 				varchar(255) NOT NULL,
			`url`			 	int(10) NOT NULL,
			`user_id`      	 	varchar(255) NOT NULL,
			PRIMARY KEY (`id`, `nom`),
			FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
			
		  );
		  
		  UPDATE `version` SET `version` = 13;
		  COMMIT;

		END &&
DELIMITER ;

CALL migration();
DROP PROCEDURE migration;

