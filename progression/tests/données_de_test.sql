DELETE FROM commentaire;
DELETE FROM reponse_prog;
DELETE FROM avancement;
DELETE FROM sauvegarde;
DELETE FROM cle;
DELETE FROM user;
DELETE FROM banque;

INSERT INTO user(id, courriel, username, password, etat, role, date_inscription) VALUES (
  1,
  null,
  "jdoe",
  "Crosemont2021!",
  0,
  0,
  1600828609
), (
  2,
  "bob@progressionmail.com",
  "bob",
  "motDePasse",
  0,
  0,
  1590828610
), (
  3,
  null,
  "admin",
  "mdpAdmin",
  0,
  1,
  1580828611
), (
  4,
  null,
  "Stefany",
  null,
  0,
  0,
  1610828610
), (
  5,
  "jane@gmail.com",
  "jane",
  null,
  0,
  0,
  1610828611
), (
  6,
  "nouveau@progressionmail.com",
  "nouveau",
  "Test1234",
  2,
  0,
  1610828612
);

INSERT INTO cle(nom, hash, creation, expiration, portee, user_id) VALUES (
  "clé de test",
  "1234",
  1624593600,
  1624680000,
  1,
  2
), (
  "clé de test 2",
  "2345",
  1624593602,
  1624680002,
  1,
  2
);

INSERT INTO avancement(id, type, question_uri, etat, titre, niveau, date_modification, date_reussite, user_id) VALUES (
  1,
  "prog",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
  1,
  "Un titre",
  "facile",
  1615696276,
  null,
  2
),(
  2,
  "prog",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction",
  1,
  "Un titre",
  "facile",
  1645739981,
  1645739959,
  2
),(
  3,
  "prog",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction2",
  2,
  "Un titre 2",
  "facile",
  1645739991,
  1645739969,
  2
),(
  4,
  "prog",
  "https://exemple.com",
  1,
  "Un titre",
  "facile",
  1645739981,
  1645739959,
  4
),(
  5,
  "sys",
  "https://exemple2.com",
  1,
  "Question Système",
  "facile",
  1645739981,
  1645739959,
  1
),(
  6,
  "sys",
  "https://depot.com/roger/questions_sys/permissions01/octroyer_toutes_les_permissions",
  1,
  "Toutes Permissions",
  "facile",
  1645739981,
  1645739959,
  1
);

INSERT INTO sauvegarde(
  `date_sauvegarde`,
  `langage`,
  `code`,
  `avancement_id`
) VALUES (
  1620150294,
  "python",
  "print(\"Hello world!\")",
  1
), (
  1620150375,
  "java",
  "System.out.println(\"Hello world!\");",
  1
);

INSERT INTO reponse_prog(id, date_soumission, langage, code, reussi, tests_reussis, temps_exécution, avancement_id) VALUES (
  1,
  1615696276,
  "python",
  "print(\"Tourlou le monde!\")",
  0,
  2,
  3456,
  1
),(
  2,
  1615696286,
  "python",
  "print(\"Allo le monde!\")",
  0,
  3,
  34567,
  2
),(
  3,
  1615696296,
  "python",
  "print(\"Allo tout le monde!\")",
  1,
  4,
  345633,
  2
);

INSERT INTO reponse_sys(conteneur, reponse, date_soumission, reussi, tests_reussis, temps_exécution, avancement_id) VALUES (
  "leConteneur",
  "laRéponse",
  1615696300,
  0,
  0,
  0,
  6
),(
  "leConteneur2",
  "laRéponse2",
  1615696301,
  1,
  1,
  0,
  6
),(
  "leConteneur3",
  "laRéponse3",
  1615696299,
  1,
  1,
  0,
  6
);

INSERT INTO commentaire(tentative_id, message, date, créateur_id, numéro_ligne) VALUES(
  1,
  "le 1er message",
  1615696277,
  1,
  14
),(
  1,
  "le 2er message",
  1615696278,
  3,
  12
),(
  1,
  "le 3er message",
  1615696279,
  4,
  14
);

INSERT INTO banque(id, nom, url, user_id) VALUES(
  1,
  "Test banque de questions 1 - fichier yaml valide",
  "file:///var/www/progression/tests/progression/dao/démo/banque_1/contenu.yml",
  2
), (
  2,
  "Test banque de questions 2 - fichier yaml valide",
  "file:///var/www/progression/tests/progression/dao/démo/banque_2/contenu.yml",
  2
);
