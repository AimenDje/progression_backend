INSERT INTO serie (themeID, serieID, numero, titre, description)
VALUES (1, 2, 2, "Les structures itératives", "Ces questions vous permettront de vérifier vos connaissances sur les structures itératives et l'itération sur les tableaux.");

    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 1, 2,'Question 1', 'Question 1', 'Faites un programme qui affiche les nombres de 0 à 100 inclusivement sur une ligne chacun.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"0\n1\n2\n3\n4\n5\n6\n7\n8\n9\n10\n11\n12\n13\n14\n15\n16\n17\n18\n19\n20\n21\n22\n23\n24\n25\n26\n27\n28\n29\n30\n31\n32\n33\n34\n35\n36\n37\n38\n39\n40\n41\n42\n43\n44\n45\n46\n47\n48\n49\n50\n51\n52\n53\n54\n55\n56\n57\n58\n59\n60\n61\n62\n63\n64\n65\n66\n67\n68\n69\n70\n71\n72\n73\n74\n75\n76\n77\n78\n79\n80\n81\n82\n83\n84\n85\n86\n87\n88\n89\n90\n91\n92\n93\n94\n95\n96\n97\n98\n99\n100\n"', '', '', '', 'print(0)', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 2, 2,'Question 2', 'Question 2', 'Faites un programme qui affiche les nombres pairs de 0 à 100 inclusivement sur une ligne chacun.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"0\n2\n4\n6\n8\n10\n12\n14\n16\n18\n20\n22\n24\n26\n28\n30\n32\n34\n36\n38\n40\n42\n44\n46\n48\n50\n52\n54\n56\n58\n60\n62\n64\n66\n68\n70\n72\n74\n76\n78\n80\n82\n84\n86\n88\n90\n92\n94\n96\n98\n100\n"', '', '', '', 'print(0)', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 3, 2,'Question 3', 'Question 3', 'Faites un programme qui affiche les multiples de 3 entre 0 à 100 inclusivement <em>en ordre décroissant</em> sur une ligne chacun.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"99\n96\n93\n90\n87\n84\n81\n78\n75\n72\n69\n66\n63\n60\n57\n54\n51\n48\n45\n42\n39\n36\n33\n30\n27\n24\n21\n18\n15\n12\n9\n6\n3\n0"', '', '', '', 'print(0)', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 4, 2,'Question 4', 'Question 4', 'Faites un programme qui affiche les multiples <em>impairs</em> de 3 entre 0 à 100 inclusivement <em>en ordre croissant</em> sur une ligne chacun.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"3\n9\n15\n21\n27\n33\n39\n45\n51\n57\n63\n69\n75\n81\n87\n93\n99"', '', '', '', 'print(0)', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 5, 2,'Question 5', 'Question 5', 'Faites un programme qui affiche les 20 premières puissances de 2 (de 2<sup>1</sup> à 2<sup>20</sup>) sur une ligne chacun.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"2\n4\n8\n16\n32\n64\n128\n256\n512\n1024\n2048\n4096\n8192\n16384\n32768\n65536\n131072\n262144\n524288\n1048576\n"', '', '', '', 'print(0)', ''); 
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 6, 2,'Question 6', 'Question 6', 'Faites un programme qui affiche les 20 premières puissances de 2 (de 2<sup>1</sup> à 2<sup>20</sup>) avec tous ses multiples sur une ligne chacune, sous la forme «2x2x2x2x2x2x2x2x2x2x = 1024».');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"2x = 2\n2x2x = 4\n2x2x2x = 8\n2x2x2x2x = 16\n2x2x2x2x2x = 32\n2x2x2x2x2x2x = 64\n2x2x2x2x2x2x2x = 128\n2x2x2x2x2x2x2x2x = 256\n2x2x2x2x2x2x2x2x2x = 512\n2x2x2x2x2x2x2x2x2x2x = 1024\n2x2x2x2x2x2x2x2x2x2x2x = 2048\n2x2x2x2x2x2x2x2x2x2x2x2x = 4096\n2x2x2x2x2x2x2x2x2x2x2x2x2x = 8192\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 16384\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 32768\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 65536\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 131072\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 262144\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 524288\n2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x2x = 1048576\n"', '', '', '', 'print(0)', ''); 
