DROP PROCEDURE IF EXISTS migration;
DELIMITER &&
  CREATE PROCEDURE migration()
  proc: BEGIN
		  SET @version := (SELECT `version` FROM `version`);
		  IF @version >= 1 THEN
			LEAVE proc;
		  END IF;

		  START TRANSACTION;
		  
		  CREATE TABLE `user` (
			`username`	varchar(255),
			`password`	varchar(255),
			`actif`		int NOT NULL DEFAULT 1,
			`role`		int NOT NULL DEFAULT 0,
			PRIMARY KEY (`username`)
		  );

		  CREATE TABLE `cle` (
			`username`   varchar(255),
			`nom`        varchar(255),
			`hash`       varchar(255) NOT NULL,
			`creation`   int NOT NULL,
			`expiration` int NOT NULL,
			`portee`     int NOT NULL DEFAULT 0,
			PRIMARY KEY (`username`, `nom`),
			FOREIGN KEY (`username`) REFERENCES `user`(`username`)
		  );

		  CREATE TABLE `sauvegarde` (
			`username`			varchar(255) NOT NULL,
			`question_uri`		varchar(1024) CHARACTER SET latin1 NOT NULL,
			`date_sauvegarde`	int(10) NOT NULL,
			`langage`			varchar(255) NOT NULL,
			`code`				text NOT NULL,
			PRIMARY KEY (`username`, `question_uri`, `langage`),
			FOREIGN KEY (`username`) REFERENCES `user`(`username`)
		  );

		  CREATE TABLE `avancement` (
			`username`		varchar(255) NOT NULL,
			`question_uri` 	varchar(1024) CHARACTER SET latin1 NOT NULL,
			`etat`			int DEFAULT 1,
			`type`          int NOT NULL,
			PRIMARY KEY (`username`, `question_uri`),
			FOREIGN KEY (`username`) REFERENCES `user`(`username`)
		  );

		  CREATE TABLE `reponse_sys` (
			`username`		varchar(255) NOT NULL,
			`question_uri` 	varchar(1024) CHARACTER SET latin1,
			`conteneur`		varchar(64),
			`reponse`		varchar(255),
			PRIMARY KEY (`username`, `question_uri`),
			FOREIGN KEY ( `username`) REFERENCES `user`(`username`),
			FOREIGN KEY (`username`, `question_uri`) REFERENCES avancement(`username`, `question_uri`)
		  );

		  CREATE TABLE `reponse_prog` (
			`username`			varchar(255) NOT NULL,
			`question_uri` 		varchar(1024) CHARACTER SET latin1,
			`date_soumission` 	int(10) NOT NULL,
			`langage`			varchar(255) NOT NULL,
			`code`				text,
			`reussi`            boolean NOT NULL DEFAULT false,
			`tests_reussis`  int NOT NULL DEFAULT 0,
			PRIMARY KEY (`username`, `question_uri`, `date_soumission`),
			FOREIGN KEY (`username`, `question_uri`) REFERENCES avancement(`username`, `question_uri`)
		  );

		  UPDATE `version` SET `version` = 1;
		  COMMIT;

		END &&
DELIMITER ;

CALL migration();
DROP PROCEDURE migration;

