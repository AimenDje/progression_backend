DELETE FROM reponse_prog;
DELETE FROM avancement;
DELETE FROM user;

INSERT INTO user VALUES (
  "bob",
  1,
  0
);

INSERT INTO user VALUES (
  "admin",
  1,
  1
);

INSERT INTO avancement VALUES (
  "bob",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
  0
);

INSERT INTO avancement VALUES (
  "bob",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction",
  1
);

INSERT INTO reponse_prog VALUES (
  "bob",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_fonction",
  1615696276,
  "python",
  "print(\"Tourlou le monde!\")",
  0
);

INSERT INTO reponse_prog VALUES (
  "bob",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction",
  1615696286,
  "python",
  "print(\"Allo le monde!\")",
  0
);

INSERT INTO reponse_prog VALUES (
  "bob",
  "https://depot.com/roger/questions_prog/fonctions01/appeler_une_autre_fonction",
  1615696296,
  "python",
  "print(\"Allo tout le monde!\")",
  1
);
