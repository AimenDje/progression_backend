INSERT INTO serie (themeID, serieID, numero, titre, description)
VALUES (1, 5, 5, "Les fonctions", "Ces questions vous permettront de vérifier vos connaissances sur les fonctions, leur appel et leur déclaration.");

    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 1, 5,'Question 1', 'Question 1', 'Exécutez la fonction <code>test</code>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"Test réussi.\n"', '', '', '\"\ndef test():\n    \\"\\"\\"\n    Fonction de test.\n\n    Affiche systématiquement les mots «Test réussi.»\n\n    \\"\\"\\"\n    print(\\"Test réussi.\\")\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 2, 5,'Question 2', 'Question 2', 'Utilisez la fonction <code>afficher_nb</code> pour faire afficher 10 fois le nombre <code>nombre</code>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$r1\n\$r1\n\$r1\n\$r1\n\$r1\n\$r1\n\$r1\n\$r1\n\$r1\n\$r1\n"', '$r1=rand(0,1000); ', '', '\"\ndef afficher_nb():\n    \\"\\"\\"\n    Affiche un nombre entier «magique».\n\n    Affiche un nombre entier «magique», c\'est à dire un nombre codé en dur\n    dans le code source et sans signification évidente.\n\n    \\"\\"\\"\n    nombre = \$r1\n\n    print(nombre)\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 3, 5,'Question 3', 'Question 3', 'Utilisez les fonctions <code>afficher_nb1</code> et <code>afficher_nb2</code> pour faire afficher les deux nombres dans l\'ordre le  nombre 2 puis le  nombre 1');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$r2\n\$r1\n"', '$r1=rand(0,1000); $r2=rand(0,1000); ', '', '\"\ndef afficher_nb1():\n    \\"\\"\\"\n    Affiche un nombre entier «magique».\n\n    Affiche un nombre entier «magique», c\'est à dire un nombre codé en dur\n    dans le code source et sans signification évidente.\n\n    \\"\\"\\"\n    nombre = \$r1\n    print(nombre)\n\ndef afficher_nb2():\n    \\"\\"\\"\n    Affiche un nombre entier «magique».\n\n    Affiche un nombre entier «magique», c\'est à dire un nombre codé en dur\n    dans le code source et sans signification évidente.\n\n    \\"\\"\\"\n    nombre = \$r2\n    print(nombre)\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 4, 5,'Question 4', 'Question 4', 'Utilisez les fonctions <code>afficher_nb1</code> et <code>afficher_nb2</code> pour faire afficher les deux nombres dans l\'ordre le nombre 2 puis le nombre 1');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$r2\n\$r1\n"', '$r1=rand(0,1000); $r2=rand(0,1000); ', '', '\"\ndef afficher_nb1():\n    \\"\\"\\"\n    Affiche deux nombres entiers «magiques».\n\n    Affiche deux nombres entiers «magiques», c\'est à dire codés en dur\n    dans le code source et sans signification évidente.\n\n    \\"\\"\\"\n    nombre = \$r1\n    afficher_nb2()\n    print(nombre)\n\ndef afficher_nb2():\n    \\"\\"\\"\n    Affiche un nombre entier «magique».\n\n    Affiche un nombre entier «magique», c\'est à dire un nombre codé en dur\n    dans le code source et sans signification évidente.\n\n    \\"\\"\\"\n    nombre = \$r2\n    print(nombre)\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 5, 5,'Question 5', 'Question 5', 'Exécutez la fonction <code>multiplier</code> pour faire afficher le double de <code>nombre</code>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$s\n"', '$r=rand(0,1000); $s=2*$r; ', '', '\"\ndef multiplier(multiplicateur):\n    \\"\\"\\"\n    Affiche un multiple d\'un nombre entier «magique».\n    \n    Affiche un nombre entier «magique» multiplié par un multiplicateur fourni en paramètre.\n\n    Paramètres:\n    multiplicateur : nombre entier multiplicateur du nombre magique.\n\n    \\"\\"\\"\n    nombre = \$r\n    print(multiplicateur * nombre)\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 6, 5,'Question 6', 'Question 6', 'La fonction <code>multiplier</code> a été modifiée. Désormais, elle <em>retourne</em> un nombre entier. Utilisez la fonction <code>multiplier</code> pour faire afficher le double de <code>nombre</code>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$s\n"', '$r=rand(0,1000); $s=2*$r; ', '', '\"\ndef multiplier(multiplicateur):\n    \\"\\"\\"\n    Affiche un multiple d\'un nombre entier «magique».\n    \n    Affiche un nombre entier «magique» multiplié par un multiplicateur fourni en paramètre.\n\n    Paramètres:\n    multiplicateur : nombre entier multiplicateur du nombre magique.\n\n    Retourne: un entier multiple du nombre magique.\n\n    \\"\\"\\"\n    nombre = \$r\n    return multiplicateur * nombre\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 7, 5,'Question 7', 'Question 7', 'Utilisez la fonction <code>entier_aléatoire</code> pour obtenir et afficher un entier choisi aléatoirement entre 0 et 1000 inclusivement, sachant que <code>random.random</code> retourne un nombre réel choisi entre 0,0 et 1,0 inclusivement.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$r\n"', '$r=rand(0,1000); $s=$r/1000;  ', '\"\nimport random;random.random=lambda:\".number_format(\$s,3).\"\n\"', '\"\nimport random\n\ndef entier_aléatoire(max):\n    \\"\\"\\"\n    Fournit un nombre entier pseudo-aléatoire sélectionné entre 0 et <em>max</em>.\n\n    Paramètre :\n    max : un nombre entier limite supérieure inclusive du tirage pseudo-aléatoire.\n\n    Retourne : un nombre entier pseudo-aléatoire sélectionné entre 0 et <em>max</em>.\n\n    \\"\\"\\"\n    nb_aléatoire = random.random()\n    return int(max * nb_aléatoire)\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 8, 5,'Question 8', 'Question 8', 'Utilisez la fonction <code>entier_aléatoire</code> pour obtenir et afficher un entier choisi aléatoirement entre <em>100 et 200</em> inclusivement, sachant que <code>random.random</code> retourne un nombre réel choisi entre 0,0 et 1,0 inclusivement.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$r\n"', '$r=rand(100,200); $s=($r-100)/100;  ', '\"\nimport random;random.random=lambda:\".number_format(\$s,3).\"\n\"', '\"\nimport random\n\ndef entier_aléatoire(max):\n    \\"\\"\\"\n    Fournit un nombre entier pseudo-aléatoire sélectionné entre 0 et <em>max</em>.\n\n    Paramètre :\n    max : un nombre entier limite supérieure inclusive du tirage pseudo-aléatoire.\n\n    Retourne : un nombre entier pseudo-aléatoire sélectionné entre 0 et <em>max</em>.\n\n    \\"\\"\\"\n    nb_aléatoire = random.random()\n    return round(max * nb_aléatoire)\n\n\"', '', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 9, 5,'Question 9', 'Question 9', 'Cette fonction <code>entier_aléatoire</code> est vraiment une bonne idée. Il serait encore mieux de lui donner un nouveau paramètre <code>min</code> délimitant le nombre pseudo-aléatoire minimum pouvant être retourné. Modifiez la fonction <code>entier_aléatoire</code> afin qu\'elle retourne un nombre entier pseudo-aléatoire entre <code>min</code> et <code>max</code>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"\$r\n"', '$min=rand(10,100); $max=rand(100,300);  $r=rand($min,$max); $s=($r-$min)/($max-$min);  ', '\" random=lambda:\".number_format(\$s,3).\" \"', '\"from random import random  "', '\ndef entier_aléatoire(min, max):\n    """\n    Fournit un nombre entier pseudo-aléatoire sélectionné entre min et max\n\n    Paramètre :\n    min : un nombre entier limite inférieure inclusive du tirage pseudo-aléatoire\n    max : un nombre entier limite supérieure inclusive du tirage pseudo-aléatoire\n\n    Retourne : un nombre entier pseudo-aléatoire sélectionné entre min et max\n\n    """\n    nb_aléatoire = random()\n\n', '\"#Affiche un nombre pseudo-aléatoire choisi entre \$min et \$max. print(entier_aléatoire(\$min,\$max))\"'); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 10, 5,'Question 10', 'Question 10', 'Faites une fonction appelée <code>factorielle</code> permettant de calculer la factorielle de n\'importe quel nombre entier selon la signature donnée.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), 'gmp_strval(gmp_fact(\$r)) . "\n"', '$r=rand(10,50);  ', '', '', '\ndef factorielle(x):\n    """\n    Calcule la factorielle de x\n\n    Calcule et retourne la factorielle de x, c\'est-à-dire x * x-1 * x-2 * … * 1\n\n    Paramètre :\n    x : un nombre entier\n\n    Retourne : a factorielle du nombre x\n\n    """\n\n', '\"#Affiche la factorielle du nombre \$r\nprint(factorielle(\$r))\"'); 