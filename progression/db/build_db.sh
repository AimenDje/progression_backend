# Création initiale
&>/dev/null mysql --default-character-set=utf8 -v -h$DB_SERVERNAME -uroot -p$DB_PASSWORD <<EOF
CREATE DATABASE IF NOT EXISTS $DB_DBNAME
				  CHARACTER SET utf8mb4
				  COLLATE utf8mb4_general_ci;
show databases;
USE $DB_DBNAME;				  
DROP PROCEDURE IF EXISTS migration;

DELIMITER &&
CREATE PROCEDURE migration()
    proc: BEGIN

    CREATE TABLE IF NOT EXISTS version (
    	version int NOT NULL DEFAULT 0
    );

    SET @version := (SELECT count(version) FROM version);
    IF @version > 0 THEN
    	LEAVE proc;
    END IF;
    
    START TRANSACTION;
    
    CREATE USER IF NOT EXISTS $DB_USERNAME@'%' IDENTIFIED BY "$DB_PASSWORD";
    
    GRANT ALL PRIVILEGES ON $DB_DBNAME.* TO $DB_USERNAME@'%';
    
    INSERT INTO version VALUES(0);
    
    COMMIT;

END&&
DELIMITER ;

CALL migration();
DROP PROCEDURE migration;

EOF

# Migrations
for migration in $(ls migrations.d/[0-9]*.sql)
do
	echo -n Migration $migration...
	mysql --default-character-set=utf8 -v -h$DB_SERVERNAME -u$DB_USERNAME -p$DB_PASSWORD $DB_DBNAME > /dev/null < $(dirname ${BASH_SOURCE[0]})/$migration && echo OK
done
