INSERT INTO serie (themeID, serieID, numero, titre, description)
VALUES (1, 3, 3, "Les tableaux", "Ces questions vous permettront de vérifier vos connaissances sur les tableaux, plus précisément le découpage en sous-tableaux, l'ajout et l'insertion d'éléments.");

    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 1, 3,'Question 1', 'Question 1', 'Soit le tableau nommé <em>tableau</em> ci-dessous. Faites un programme qui calcule et affiche le nombre d\'éléments que contient le tableau <em>tableau</em>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$nb_i+1', '$nb_i=rand(500,1000); $t="["; for($i=0;$i<$nb_i;$i++){     $t=$t . strval(rand(0,999)) . ", "; } $t=$t . strval(rand(0,999)) . "]"; ', '', '"tableau=$t"', 'print(0)', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 2, 3,'Question 2', 'Question 2', 'Soit le tableau nommé <em>tableau</em> ci-dessous. Faites un programme qui calcule et affiche le nombre d\'éléments <em>pairs</em> que contient le tableau <em>tableau</em>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$nb_pair', '$nb_i=rand(500,1000); $nb_pair=0; $t="["; for($i=0;$i<$nb_i;$i++){     $nb_r=strval(rand(0,999));     if($nb_r%2==0) $nb_pair++;     $t=$t . $nb_r . ", "; } chop($t, ", "); $t=$t . "]";  ', '', '"tableau=$t"', 'print(0)', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 3, 3,'Question 3', 'Question 3', 'Soit le tableau nommé <em>tableau</em> ci-dessous. Faites un programme qui calcule et affiche la somme de tous les éléments que contient le tableau <em>tableau</em>.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$nb_somme', '$nb_i=rand(500,1000); $nb_somme=0; $t="["; for($i=0;$i<$nb_i;$i++){     $nb_r=strval(rand(0,999));     $nb_somme+=$nb_r;     $t=$t . $nb_r . ", "; } chop($t,", "); $t=$t . "]";  ', '', '"tableau=$t"', 'print(0)', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 4, 3,'Question 4', 'Question 4', 'Soit le tableau nommé <em>tableau</em> ci-dessous. Faites un programme qui calcule et affiche pour chaque élément la différence d\'avec le précédent en commençant par le deuxième élément.<br><br>Ex:<br>[1]-[0] = 393<br>[2]-[1] = -282<br>etc.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '"[1]-[0] = 393
[2]-[1] = -282
[3]-[2] = 97
[4]-[3] = -344
[5]-[4] = -127
[6]-[5] = 229
[7]-[6] = 25
[8]-[7] = 362
[9]-[8] = -577
[10]-[9] = 501
[11]-[10] = -133
[12]-[11] = -411
[13]-[12] = 915
[14]-[13] = -86
[15]-[14] = -731
[16]-[15] = 809
[17]-[16] = -173
[18]-[17] = -436
[19]-[18] = -223
[20]-[19] = 135
[21]-[20] = 142
[22]-[21] = -144
[23]-[22] = 412
[24]-[23] = -463
[25]-[24] = 305
[26]-[25] = -467
[27]-[26] = 654
[28]-[27] = -549
[29]-[28] = 723
[30]-[29] = 34
[31]-[30] = 21
[32]-[31] = -8
[33]-[32] = 20
[34]-[33] = -74
[35]-[34] = -641
[36]-[35] = 69
[37]-[36] = 366
[38]-[37] = 269
[39]-[38] = -248
[40]-[39] = 337
[41]-[40] = -523
[42]-[41] = 339
[43]-[42] = -1
[44]-[43] = 29
[45]-[44] = -246
[46]-[45] = 213
[47]-[46] = -380
[48]-[47] = 250
[49]-[48] = -667
[50]-[49] = 655
[51]-[50] = -96
[52]-[51] = 236
[53]-[52] = 148
[54]-[53] = -667
[55]-[54] = 53
[56]-[55] = 131
[57]-[56] = -348
[58]-[57] = 749
[59]-[58] = -131
[60]-[59] = -87
[61]-[60] = -173
[62]-[61] = 358
[63]-[62] = -294
[64]-[63] = 317
[65]-[64] = -721
[66]-[65] = 538
[67]-[66] = 92
[68]-[67] = -130
[69]-[68] = -36
[70]-[69] = -546
[71]-[70] = 303
[72]-[71] = 86
[73]-[72] = -71
[74]-[73] = 470
[75]-[74] = -383
[76]-[75] = 479
[77]-[76] = -881
[78]-[77] = 364
[79]-[78] = 162
[80]-[79] = -251
[81]-[80] = -145
[82]-[81] = 760
[83]-[82] = -892
[84]-[83] = 453
[85]-[84] = 445
[86]-[85] = -685
[87]-[86] = 558
[88]-[87] = -213
[89]-[88] = -567
[90]-[89] = 196
[91]-[90] = -55
[92]-[91] = 490
[93]-[92] = -453
[94]-[93] = 609
[95]-[94] = -15
[96]-[95] = 176
[97]-[96] = -708
[98]-[97] = 390
[99]-[98] = 224
[100]-[99] = -715
[101]-[100] = 715
[102]-[101] = 94
[103]-[102] = -763
[104]-[103] = 347
[105]-[104] = -123
[106]-[105] = -381
[107]-[106] = 380
[108]-[107] = -162
[109]-[108] = 212
[110]-[109] = 12
[111]-[110] = 232
[112]-[111] = -550
[113]-[112] = -131
[114]-[113] = 830
[115]-[114] = -794
[116]-[115] = 77
[117]-[116] = 531
[118]-[117] = -461
[119]-[118] = 49
[120]-[119] = 51
[121]-[120] = -337
[122]-[121] = 516
[123]-[122] = 303
[124]-[123] = -319
[125]-[124] = -337
[126]-[125] = -146
[127]-[126] = 416
[128]-[127] = 54
[129]-[128] = -334
[130]-[129] = 618
[131]-[130] = 21
[132]-[131] = 179
[133]-[132] = -452
[134]-[133] = 261
[135]-[134] = -97
[136]-[135] = -684
[137]-[136] = 851
[138]-[137] = -251
[139]-[138] = -21
[140]-[139] = 156
[141]-[140] = -446
[142]-[141] = 594
[143]-[142] = -691
[144]-[143] = 294
[145]-[144] = 30
[146]-[145] = 17
[147]-[146] = 96
[148]-[147] = 16
[149]-[148] = -635
[150]-[149] = 97
[151]-[150] = 606
[152]-[151] = -168
[153]-[152] = -13
[154]-[153] = 407
[155]-[154] = -703
[156]-[155] = 376
[157]-[156] = -480
[158]-[157] = 624
[159]-[158] = 26
[160]-[159] = 72
[161]-[160] = -464
[162]-[161] = -259
[163]-[162] = 568
[164]-[163] = -54
[165]-[164] = -573
[166]-[165] = 118
[167]-[166] = 782
[168]-[167] = -361
[169]-[168] = 161
[170]-[169] = -404
[171]-[170] = -218
[172]-[171] = 264
[173]-[172] = 250
[174]-[173] = -81
[175]-[174] = 409
[176]-[175] = -904
[177]-[176] = 411
[178]-[177] = 93
[179]-[178] = 366
[180]-[179] = -704
[181]-[180] = -148
[182]-[181] = 248
[183]-[182] = -275
[184]-[183] = 200
[185]-[184] = 61
[186]-[185] = -115
[187]-[186] = 694
[188]-[187] = -397
[189]-[188] = -520
[190]-[189] = 899
[191]-[190] = 28
[192]-[191] = -311
[193]-[192] = -538
[194]-[193] = 527
[195]-[194] = 355
[196]-[195] = -310
[197]-[196] = -512
[198]-[197] = 733
[199]-[198] = -710
[200]-[199] = 545
[201]-[200] = -631
[202]-[201] = 433
[203]-[202] = -143
[204]-[203] = -232
[205]-[204] = 117
[206]-[205] = -245
[207]-[206] = 859
[208]-[207] = -417
[209]-[208] = -69
[210]-[209] = -360
[211]-[210] = 767
[212]-[211] = -246
[213]-[212] = 216
[214]-[213] = -328
[215]-[214] = -142
[216]-[215] = 518
[217]-[216] = -276
[218]-[217] = -460
[219]-[218] = 752
[220]-[219] = -369
[221]-[220] = 268
[222]-[221] = -229
[223]-[222] = 455
[224]-[223] = -649
[225]-[224] = 243
[226]-[225] = 215
[227]-[226] = -738
[228]-[227] = 368
[229]-[228] = 230
[230]-[229] = -358
[231]-[230] = 249
[232]-[231] = 423
[233]-[232] = -292
[234]-[233] = -512
[235]-[234] = 645
[236]-[235] = -611
[237]-[236] = -60
[238]-[237] = 171
[239]-[238] = -220
[240]-[239] = 815
[241]-[240] = -34
[242]-[241] = -736
[243]-[242] = 628
[244]-[243] = -497
[245]-[244] = 235
[246]-[245] = 296
[247]-[246] = -454
[248]-[247] = 328
[249]-[248] = 253
[250]-[249] = -366
[251]-[250] = 45
[252]-[251] = 31
[253]-[252] = -32
[254]-[253] = 48
[255]-[254] = 316
[256]-[255] = -714
[257]-[256] = 22
[258]-[257] = 97
[259]-[258] = -180
[260]-[259] = -46
[261]-[260] = 90
[262]-[261] = 132
[263]-[262] = 85
[264]-[263] = 25
[265]-[264] = -227
[266]-[265] = 397
[267]-[266] = 206
[268]-[267] = -789
[269]-[268] = 223
[270]-[269] = 695
[271]-[270] = -701
[272]-[271] = 687
[273]-[272] = 6
[274]-[273] = -581
[275]-[274] = -88
[276]-[275] = -23
[277]-[276] = 593
[278]-[277] = -827
[279]-[278] = 581
[280]-[279] = 209
[281]-[280] = -348
[282]-[281] = 255
[283]-[282] = -86
[284]-[283] = -474
[285]-[284] = 418
[286]-[285] = -306
[287]-[286] = -188
[288]-[287] = -75
[289]-[288] = 452
[290]-[289] = 532
[291]-[290] = -652
[292]-[291] = 454
[293]-[292] = -277
[294]-[293] = -476
[295]-[294] = 878
[296]-[295] = -852
[297]-[296] = 574
[298]-[297] = -233
[299]-[298] = -112
[300]-[299] = 285
[301]-[300] = 168
[302]-[301] = -195
[303]-[302] = 275
[304]-[303] = -704
[305]-[304] = 338
[306]-[305] = 67
[307]-[306] = -386
[308]-[307] = 377
[309]-[308] = 17
[310]-[309] = -531
[311]-[310] = 311
[312]-[311] = -169
[313]-[312] = 654
[314]-[313] = -362
[315]-[314] = -349
[316]-[315] = 290
[317]-[316] = 60
[318]-[317] = 389
[319]-[318] = -454
[320]-[319] = 214
[321]-[320] = 177
[322]-[321] = 181
[323]-[322] = 46
[324]-[323] = -824
[325]-[324] = 652
[326]-[325] = -376
[327]-[326] = -345
[328]-[327] = 862
[329]-[328] = -888
[330]-[329] = 16
[331]-[330] = 379
[332]-[331] = 16
[333]-[332] = 248
[334]-[333] = 138
[335]-[334] = -84
[336]-[335] = -165
[337]-[336] = -270
[338]-[337] = 180
[339]-[338] = 73
[340]-[339] = -256
[341]-[340] = 367
[342]-[341] = -337
[343]-[342] = 218
[344]-[343] = -464
[345]-[344] = 467
[346]-[345] = -236
[347]-[346] = 518
[348]-[347] = -203
[349]-[348] = -282
[350]-[349] = -325
[351]-[350] = 616
[352]-[351] = -347
[353]-[352] = -131
[354]-[353] = -140
[355]-[354] = 514
[356]-[355] = 226
[357]-[356] = 2
[358]-[357] = -276
[359]-[358] = 200
[360]-[359] = -680
[361]-[360] = 924
[362]-[361] = -280
[363]-[362] = -554
[364]-[363] = 21
[365]-[364] = 801
[366]-[365] = -75
[367]-[366] = -64
[368]-[367] = -589
[369]-[368] = 567
[370]-[369] = -472
[371]-[370] = 561
[372]-[371] = -746
[373]-[372] = 834
[374]-[373] = -664
[375]-[374] = 550
[376]-[375] = -824
[377]-[376] = 715
[378]-[377] = -17
[379]-[378] = -192
[380]-[379] = -73
[381]-[380] = -80
[382]-[381] = -338
[383]-[382] = 540
[384]-[383] = 368
[385]-[384] = -694
[386]-[385] = 167
[387]-[386] = -437
[388]-[387] = 296
[389]-[388] = 105
[390]-[389] = -175
[391]-[390] = 568
[392]-[391] = 84
[393]-[392] = 99
[394]-[393] = -430
[395]-[394] = -68
[396]-[395] = 184
[397]-[396] = -659
[398]-[397] = 748
[399]-[398] = -573
[400]-[399] = 363
[401]-[400] = -78
[402]-[401] = 242
[403]-[402] = 240
[404]-[403] = -285
[405]-[404] = -419
[406]-[405] = -175
[407]-[406] = 493
[408]-[407] = 379
[409]-[408] = -675
[410]-[409] = 663
[411]-[410] = -477
[412]-[411] = -30
[413]-[412] = -143
[414]-[413] = 490
[415]-[414] = -633
[416]-[415] = 727
[417]-[416] = -806
[418]-[417] = 715
[419]-[418] = -149
[420]-[419] = -352
[421]-[420] = -171
[422]-[421] = 190
[423]-[422] = 348
[424]-[423] = -248
[425]-[424] = -304
[426]-[425] = 584
[427]-[426] = -373
[428]-[427] = -205
[429]-[428] = -29
[430]-[429] = 376
[431]-[430] = -80
[432]-[431] = -40
[433]-[432] = 593
[434]-[433] = -607
[435]-[434] = 3
[436]-[435] = -58
[437]-[436] = -180
[438]-[437] = 306
[439]-[438] = 406
[440]-[439] = 204
[441]-[440] = -436
[442]-[441] = -215
[443]-[442] = 587
[444]-[443] = 45
[445]-[444] = -146
[446]-[445] = -769
[447]-[446] = 512
[448]-[447] = -251
[449]-[448] = 411
[450]-[449] = -461
[451]-[450] = -225
[452]-[451] = 592
[453]-[452] = -601
[454]-[453] = 501
[455]-[454] = 121
[456]-[455] = 284
[457]-[456] = -335
[458]-[457] = -285
[459]-[458] = 559
[460]-[459] = -823
[461]-[460] = 943
[462]-[461] = -101
[463]-[462] = -726
[464]-[463] = -36
[465]-[464] = 712
[466]-[465] = -257
[467]-[466] = 284
[468]-[467] = -821
[469]-[468] = 218
[470]-[469] = 636
[471]-[470] = -186
[472]-[471] = -599
[473]-[472] = 647
[474]-[473] = 217
[475]-[474] = -222
[476]-[475] = -535
[477]-[476] = -52
[478]-[477] = 623
[479]-[478] = -44
[480]-[479] = -223
[481]-[480] = 220
[482]-[481] = -279
[483]-[482] = 188
[484]-[483] = -367
[485]-[484] = -283
[486]-[485] = 535
[487]-[486] = -132
[488]-[487] = -262
[489]-[488] = 73
[490]-[489] = 540
[491]-[490] = -340
[492]-[491] = -351
[493]-[492] = 764
[494]-[493] = -631
[495]-[494] = 122
[496]-[495] = 617
[497]-[496] = 35
[498]-[497] = -809
[499]-[498] = 747
[500]-[499] = -238
[501]-[500] = 157
[502]-[501] = -580
[503]-[502] = 270
[504]-[503] = -353
[505]-[504] = 287
[506]-[505] = 296
[507]-[506] = -588
[508]-[507] = 707
[509]-[508] = -725
[510]-[509] = 438
[511]-[510] = 414
[512]-[511] = -382
[513]-[512] = 373
[514]-[513] = -208
[515]-[514] = -665
[516]-[515] = 385
[517]-[516] = -411
[518]-[517] = 301
[519]-[518] = 339
[520]-[519] = 82
[521]-[520] = -142
[522]-[521] = -323
[523]-[522] = 346
[524]-[523] = -503
[525]-[524] = 702
[526]-[525] = 53
[527]-[526] = -398
[528]-[527] = 256
[529]-[528] = -457
[530]-[529] = 117
[531]-[530] = 198
[532]-[531] = -309
[533]-[532] = 46
[534]-[533] = 531
[535]-[534] = -580
[536]-[535] = 284
[537]-[536] = -130
[538]-[537] = 221
[539]-[538] = -392
[540]-[539] = 15
[541]-[540] = -260
[542]-[541] = 850
[543]-[542] = -749
[544]-[543] = -16
[545]-[544] = 642
[546]-[545] = -801
[547]-[546] = 183
[548]-[547] = 626
[549]-[548] = -53
[550]-[549] = -178
[551]-[550] = -408
[552]-[551] = 500
[553]-[552] = -108
[554]-[553] = 207
[555]-[554] = -696
[556]-[555] = 138
[557]-[556] = 90
[558]-[557] = 72
[559]-[558] = 382
[560]-[559] = -457
[561]-[560] = 47
[562]-[561] = -66
[563]-[562] = 121
[564]-[563] = -264
[565]-[564] = 626
[566]-[565] = -596
[567]-[566] = 744
[568]-[567] = -764
[569]-[568] = 687
[570]-[569] = -67
[571]-[570] = -746
[572]-[571] = 671
[573]-[572] = -618
[574]-[573] = 316
[575]-[574] = 167
[576]-[575] = -328
[577]-[576] = -36
[578]-[577] = -59
[579]-[578] = -121
[580]-[579] = 299
[581]-[580] = 639
[582]-[581] = -265
[583]-[582] = -241
[584]-[583] = 500
[585]-[584] = -143
[586]-[585] = 42
[587]-[586] = -421
[588]-[587] = 438
[589]-[588] = -598
[590]-[589] = 600
[591]-[590] = 77
[592]-[591] = -497
[593]-[592] = 166
[594]-[593] = 255
[595]-[594] = -311
[596]-[595] = 35
[597]-[596] = -364
[598]-[597] = 578
[599]-[598] = -132
[600]-[599] = -610
[601]-[600] = 143
[602]-[601] = -78
[603]-[602] = -92
[604]-[603] = 141
[605]-[604] = -65
[606]-[605] = 812
[607]-[606] = -241
[608]-[607] = 201
[609]-[608] = -78
[610]-[609] = -499
[611]-[610] = 327
[612]-[611] = -365
[613]-[612] = 551
[614]-[613] = -278
[615]-[614] = 385
[616]-[615] = -843
[617]-[616] = 169
[618]-[617] = 379
[619]-[618] = -629
[620]-[619] = 454
[621]-[620] = -434
[622]-[621] = 375
[623]-[622] = 90
[624]-[623] = 26
[625]-[624] = 251
[626]-[625] = -774
[627]-[626] = 977
[628]-[627] = -258
[629]-[628] = 73
[630]-[629] = -44
[631]-[630] = -170
[632]-[631] = 52
[633]-[632] = -135
[634]-[633] = -458
[635]-[634] = 566
[636]-[635] = -213
[637]-[636] = -208
[638]-[637] = 534
[639]-[638] = 5
[640]-[639] = -216
[641]-[640] = 21
[642]-[641] = 391
[643]-[642] = -827
[644]-[643] = 288
[645]-[644] = 187
[646]-[645] = -560
[647]-[646] = 582
[648]-[647] = -107
[649]-[648] = 116
[650]-[649] = -3
[651]-[650] = -195
[652]-[651] = -277
[653]-[652] = 281
[654]-[653] = 515
[655]-[654] = -496
[656]-[655] = 477
[657]-[656] = -29
[658]-[657] = 69
[659]-[658] = -597
[660]-[659] = 265
[661]-[660] = -63
[662]-[661] = -57
[663]-[662] = -57
[664]-[663] = -413
[665]-[664] = 667
[666]-[665] = 214
[667]-[666] = -133
[668]-[667] = -461
[669]-[668] = -101
[670]-[669] = 280
[671]-[670] = 78
[672]-[671] = 229
[673]-[672] = -328
[674]-[673] = 477
[675]-[674] = -18
[676]-[675] = -742
[677]-[676] = 303
[678]-[677] = 265
[679]-[678] = 239
[680]-[679] = -121
[681]-[680] = -115
[682]-[681] = -451
[683]-[682] = 32
[684]-[683] = -26
[685]-[684] = -19
[686]-[685] = 397
[687]-[686] = -458
[688]-[687] = 466
[689]-[688] = -375
[690]-[689] = 317
[691]-[690] = 175
[692]-[691] = -116
[693]-[692] = 55
[694]-[693] = -633
[695]-[694] = 712
[696]-[695] = -825
[697]-[696] = 902
[698]-[697] = -450
[699]-[698] = -307
[700]-[699] = 548
[701]-[700] = -29
[702]-[701] = 123
[703]-[702] = -391
[704]-[703] = -298
[705]-[704] = 296
[706]-[705] = 40
[707]-[706] = 360
[708]-[707] = 0
[709]-[708] = 47
[710]-[709] = -718
[711]-[710] = 136
[712]-[711] = -170
[713]-[712] = 328
[714]-[713] = -342
[715]-[714] = 788
[716]-[715] = -479
[717]-[716] = 158
[718]-[717] = 207
[719]-[718] = -395
[720]-[719] = -247
[721]-[720] = 855
[722]-[721] = -193
[723]-[722] = -677
[724]-[723] = 728
[725]-[724] = -500
[726]-[725] = -232
[727]-[726] = 562
[728]-[727] = -323
[729]-[728] = -148
[730]-[729] = 669
[731]-[730] = -271
[732]-[731] = -313
[733]-[732] = 31
[734]-[733] = -130
[735]-[734] = 649
[736]-[735] = -560
[737]-[736] = 492
[738]-[737] = -438
[739]-[738] = -6
[740]-[739] = 642
[741]-[740] = -310
[742]-[741] = -433
[743]-[742] = -125
[744]-[743] = 233
[745]-[744] = 500
[746]-[745] = 112
[747]-[746] = -703
[748]-[747] = -15
[749]-[748] = 351
[750]-[749] = -42
[751]-[750] = -262
[752]-[751] = 142
[753]-[752] = -113
[754]-[753] = 241
[755]-[754] = -347
[756]-[755] = 541
[757]-[756] = -608
[758]-[757] = 68
[759]-[758] = 566
[760]-[759] = -413
[761]-[760] = 203
[762]-[761] = 270
[763]-[762] = -35
[764]-[763] = -32
[765]-[764] = -694
[766]-[765] = 594
[767]-[766] = -66
[768]-[767] = -214
[769]-[768] = 428
[770]-[769] = -211
[771]-[770] = -348
[772]-[771] = 106
[773]-[772] = 356
[774]-[773] = -227
[775]-[774] = 333
[776]-[775] = -17
[777]-[776] = 15
[778]-[777] = -462
[779]-[778] = 244
[780]-[779] = -178
[781]-[780] = -298
[782]-[781] = 391
[783]-[782] = -132
[784]-[783] = -113
[785]-[784] = 283
[786]-[785] = -267
[787]-[786] = -118
[788]-[787] = 178
[789]-[788] = 376
[790]-[789] = -351
[791]-[790] = 94
[792]-[791] = 428
[793]-[792] = -64
[794]-[793] = -283
[795]-[794] = 387
[796]-[795] = -606
[797]-[796] = 172
[798]-[797] = -439
[799]-[798] = 258
[800]-[799] = 705
[801]-[800] = -274
[802]-[801] = 57
[803]-[802] = -726
[804]-[803] = 413
[805]-[804] = -198
[806]-[805] = 252
[807]-[806] = -168
[808]-[807] = -94
[809]-[808] = 415
[810]-[809] = 226
[811]-[810] = -404
[812]-[811] = -256
[813]-[812] = 380
[814]-[813] = -168
[815]-[814] = -79
[816]-[815] = 568
[817]-[816] = -692
[818]-[817] = -82
[819]-[818] = 752
[820]-[819] = -534
[821]-[820] = 202
[822]-[821] = -12
[823]-[822] = -22
[824]-[823] = 31
[825]-[824] = -225
[826]-[825] = 601
[827]-[826] = -693
[828]-[827] = 618
[829]-[828] = -311
[830]-[829] = 174
[831]-[830] = -348
[832]-[831] = 39
[833]-[832] = 376
[834]-[833] = -704
[835]-[834] = 512
[836]-[835] = 173
[837]-[836] = -183
[838]-[837] = 376
[839]-[838] = -389
[840]-[839] = -588
[841]-[840] = 600
[842]-[841] = -517
[843]-[842] = 889
[844]-[843] = -318
[845]-[844] = -512
[846]-[845] = -67
[847]-[846] = 268
[848]-[847] = -198
[849]-[848] = 300
[850]-[849] = 505
[851]-[850] = -387
[852]-[851] = -444
[853]-[852] = -124
[854]-[853] = 399
[855]-[854] = -192
[856]-[855] = -35
[857]-[856] = -120
[858]-[857] = 373
[859]-[858] = -355
[860]-[859] = 173
[861]-[860] = -15
[862]-[861] = 522
[863]-[862] = -172
[864]-[863] = -322
[865]-[864] = 659
[866]-[865] = -8
[867]-[866] = -628
[868]-[867] = -85
[869]-[868] = 607
[870]-[869] = -732
[871]-[870] = 434
[872]-[871] = 49
[873]-[872] = -348
[874]-[873] = 107
[875]-[874] = 267
[876]-[875] = 142
[877]-[876] = -175
[878]-[877] = -540
[879]-[878] = 325
[880]-[879] = 384
[881]-[880] = -534
[882]-[881] = 200
[883]-[882] = 236
[884]-[883] = -275
[885]-[884] = -57
[886]-[885] = 333
[887]-[886] = -351
[888]-[887] = 13
[889]-[888] = 207
[890]-[889] = -104
[891]-[890] = 464
[892]-[891] = -333
[893]-[892] = 51
[894]-[893] = -159
[895]-[894] = 150
[896]-[895] = -324
[897]-[896] = 313
[898]-[897] = -129
[899]-[898] = -8
[900]-[899] = 81
[901]-[900] = 408
[902]-[901] = -169
[903]-[902] = -587
[904]-[903] = 683
[905]-[904] = -182
[906]-[905] = -631
[907]-[906] = 117
[908]-[907] = 508
[909]-[908] = -559
[910]-[909] = 145
[911]-[910] = -222
[912]-[911] = 598
[913]-[912] = -213
[914]-[913] = 462
[915]-[914] = -675
[916]-[915] = 208
[917]-[916] = -196
[918]-[917] = 475
[919]-[918] = 220
[920]-[919] = -259
[921]-[920] = 368
[922]-[921] = -853
[923]-[922] = 628
[924]-[923] = -217
[925]-[924] = -506
[926]-[925] = 559
[927]-[926] = 205
[928]-[927] = -677
[929]-[928] = -44
[930]-[929] = 307
[931]-[930] = 59
[932]-[931] = -139
[933]-[932] = 382
[934]-[933] = -590
[935]-[934] = 374
[936]-[935] = 127
[937]-[936] = 106
[938]-[937] = -438
[939]-[938] = 269
[940]-[939] = -529
[941]-[940] = 398
[942]-[941] = 375
[943]-[942] = -493
[944]-[943] = 151
[945]-[944] = -180
[946]-[945] = 247
[947]-[946] = 112
[948]-[947] = -599
[949]-[948] = 111
[950]-[949] = 400
[951]-[950] = 233
[952]-[951] = -719
[953]-[952] = 10
[954]-[953] = 772
[955]-[954] = -486
[956]-[955] = 253
[957]-[956] = -509
[958]-[957] = 639
[959]-[958] = -556
[960]-[959] = 473
[961]-[960] = -222
[962]-[961] = -393
[963]-[962] = 322
[964]-[963] = -103
[965]-[964] = 247
[966]-[965] = -440
[967]-[966] = 220
[968]-[967] = 467
[969]-[968] = -370
[970]-[969] = -329
[971]-[970] = 112
[972]-[971] = -99
[973]-[972] = 147
[974]-[973] = -136
[975]-[974] = 632
[976]-[975] = 101
[977]-[976] = 58
[978]-[977] = -285
[979]-[978] = 62
[980]-[979] = 109
[981]-[980] = -308
[982]-[981] = 165
[983]-[982] = -303
[984]-[983] = 32
[985]-[984] = 232
[986]-[985] = -431
[987]-[986] = 251
[988]-[987] = -332
[989]-[988] = 132
[990]-[989] = -81
[991]-[990] = 81
[992]-[991] = -179
[993]-[992] = 946
[994]-[993] = -546
[995]-[994] = 398
[996]-[995] = -189
[997]-[996] = 353
[998]-[997] = -151
[999]-[998] = -587
[1000]-[999] = -225"', '', '', '"tableau=[286, 679, 397, 494, 150, 23, 252, 277, 639, 62, 563, 430, 19, 934, 848, 117, 926, 753, 317, 94,\
 229, 371, 227, 639, 176, 481, 14, 668, 119, 842, 876, 897, 889, 909, 835, 194, 263, 629, 898, 650,\
 987, 464, 803, 802, 831, 585, 798, 418, 668, 1, 656, 560, 796, 944, 277, 330, 461, 113, 862, 731,\
 644, 471, 829, 535, 852, 131, 669, 761, 631, 595, 49, 352, 438, 367, 837, 454, 933, 52, 416, 578,\
 327, 182, 942, 50, 503, 948, 263, 821, 608, 41, 237, 182, 672, 219, 828, 813, 989, 281, 671, 895,\
 180, 895, 989, 226, 573, 450, 69, 449, 287, 499, 511, 743, 193, 62, 892, 98, 175, 706, 245, 294,\
 345, 8, 524, 827, 508, 171, 25, 441, 495, 161, 779, 800, 979, 527, 788, 691, 7, 858, 607, 586,\
 742, 296, 890, 199, 493, 523, 540, 636, 652, 17, 114, 720, 552, 539, 946, 243, 619, 139, 763, 789,\
 861, 397, 138, 706, 652, 79, 197, 979, 618, 779, 375, 157, 421, 671, 590, 999, 95, 506, 599, 965,\
 261, 113, 361, 86, 286, 347, 232, 926, 529, 9, 908, 936, 625, 87, 614, 969, 659, 147, 880, 170,\
 715, 84, 517, 374, 142, 259, 14, 873, 456, 387, 27, 794, 548, 764, 436, 294, 812, 536, 76, 828,\
 459, 727, 498, 953, 304, 547, 762, 24, 392, 622, 264, 513, 936, 644, 132, 777, 166, 106, 277, 57,\
 872, 838, 102, 730, 233, 468, 764, 310, 638, 891, 525, 570, 601, 569, 617, 933, 219, 241, 338, 158,\
 112, 202, 334, 419, 444, 217, 614, 820, 31, 254, 949, 248, 935, 941, 360, 272, 249, 842, 15, 596,\
 805, 457, 712, 626, 152, 570, 264, 76, 1, 453, 985, 333, 787, 510, 34, 912, 60, 634, 401, 289,\
 574, 742, 547, 822, 118, 456, 523, 137, 514, 531, 0, 311, 142, 796, 434, 85, 375, 435, 824, 370,\
 584, 761, 942, 988, 164, 816, 440, 95, 957, 69, 85, 464, 480, 728, 866, 782, 617, 347, 527, 600,\
 344, 711, 374, 592, 128, 595, 359, 877, 674, 392, 67, 683, 336, 205, 65, 579, 805, 807, 531, 731,\
 51, 975, 695, 141, 162, 963, 888, 824, 235, 802, 330, 891, 145, 979, 315, 865, 41, 756, 739, 547,\
 474, 394, 56, 596, 964, 270, 437, 0, 296, 401, 226, 794, 878, 977, 547, 479, 663, 4, 752, 179,\
 542, 464, 706, 946, 661, 242, 67, 560, 939, 264, 927, 450, 420, 277, 767, 134, 861, 55, 770, 621,\
 269, 98, 288, 636, 388, 84, 668, 295, 90, 61, 437, 357, 317, 910, 303, 306, 248, 68, 374, 780,\
 984, 548, 333, 920, 965, 819, 50, 562, 311, 722, 261, 36, 628, 27, 528, 649, 933, 598, 313, 872,\
 49, 992, 891, 165, 129, 841, 584, 868, 47, 265, 901, 715, 116, 763, 980, 758, 223, 171, 794, 750,\
 527, 747, 468, 656, 289, 6, 541, 409, 147, 220, 760, 420, 69, 833, 202, 324, 941, 976, 167, 914,\
 676, 833, 253, 523, 170, 457, 753, 165, 872, 147, 585, 999, 617, 990, 782, 117, 502, 91, 392, 731,\
 813, 671, 348, 694, 191, 893, 946, 548, 804, 347, 464, 662, 353, 399, 930, 350, 634, 504, 725, 333,\
 348, 88, 938, 189, 173, 815, 14, 197, 823, 770, 592, 184, 684, 576, 783, 87, 225, 315, 387, 769,\
 312, 359, 293, 414, 150, 776, 180, 924, 160, 847, 780, 34, 705, 87, 403, 570, 242, 206, 147, 26,\
 325, 964, 699, 458, 958, 815, 857, 436, 874, 276, 876, 953, 456, 622, 877, 566, 601, 237, 815, 683,\
 73, 216, 138, 46, 187, 122, 934, 693, 894, 816, 317, 644, 279, 830, 552, 937, 94, 263, 642, 13,\
 467, 33, 408, 498, 524, 775, 1, 978, 720, 793, 749, 579, 631, 496, 38, 604, 391, 183, 717, 722,\
 506, 527, 918, 91, 379, 566, 6, 588, 481, 597, 594, 399, 122, 403, 918, 422, 899, 870, 939, 342,\
 607, 544, 487, 430, 17, 684, 898, 765, 304, 203, 483, 561, 790, 462, 939, 921, 179, 482, 747, 986,\
 865, 750, 299, 331, 305, 286, 683, 225, 691, 316, 633, 808, 692, 747, 114, 826, 1, 903, 453, 146,\
 694, 665, 788, 397, 99, 395, 435, 795, 795, 842, 124, 260, 90, 418, 76, 864, 385, 543, 750, 355,\
 108, 963, 770, 93, 821, 321, 89, 651, 328, 180, 849, 578, 265, 296, 166, 815, 255, 747, 309, 303,\
 945, 635, 202, 77, 310, 810, 922, 219, 204, 555, 513, 251, 393, 280, 521, 174, 715, 107, 175, 741,\
 328, 531, 801, 766, 734, 40, 634, 568, 354, 782, 571, 223, 329, 685, 458, 791, 774, 789, 327, 571,\
 393, 95, 486, 354, 241, 524, 257, 139, 317, 693, 342, 436, 864, 800, 517, 904, 298, 470, 31, 289,\
 994, 720, 777, 51, 464, 266, 518, 350, 256, 671, 897, 493, 237, 617, 449, 370, 938, 246, 164, 916,\
 382, 584, 572, 550, 581, 356, 957, 264, 882, 571, 745, 397, 436, 812, 108, 620, 793, 610, 986, 597,\
 9, 609, 92, 981, 663, 151, 84, 352, 154, 454, 959, 572, 128, 4, 403, 211, 176, 56, 429, 74,\
 247, 232, 754, 582, 260, 919, 911, 283, 198, 805, 73, 507, 556, 208, 315, 582, 724, 549, 9, 334,\
 718, 184, 384, 620, 345, 288, 621, 270, 283, 490, 386, 850, 517, 568, 409, 559, 235, 548, 419, 411,\
 492, 900, 731, 144, 827, 645, 14, 131, 639, 80, 225, 3, 601, 388, 850, 175, 383, 187, 662, 882,\
 623, 991, 138, 766, 549, 43, 602, 807, 130, 86, 393, 452, 313, 695, 105, 479, 606, 712, 274, 543,\
 14, 412, 787, 294, 445, 265, 512, 624, 25, 136, 536, 769, 50, 60, 832, 346, 599, 90, 729, 173,\
 646, 424, 31, 353, 250, 497, 57, 277, 744, 374, 45, 157, 58, 205, 69, 701, 802, 860, 575, 637,\
 746, 438, 603, 300, 332, 564, 133, 384, 52, 184, 103, 184, 5, 951, 405, 803, 614, 967, 816, 229,\
 4]"', 'print(0)', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 5, 3,'Question 5', 'Question 5', 'Faites afficher la valeur du  premier élément du tableau <em>numeros</em>');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', '$tab=rand(0,100); $rep=$tab; for ($i=0;$i<10;$i++){   $tab=$tab . ", " . rand(0,100); } ', '', '"numeros=[$tab]"', '', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 6, 3,'Question 6', 'Question 6', 'Faites afficher sous forme de tableau les 3 premiers éléments de <em>numeros</em>');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', '$tab=rand(0,100); for ($i=0;$i<2;$i++){   $tab=$tab . ", " . rand(0,100); } $rep="[$tab]"; for ($i=0;$i<10;$i++){   $tab=$tab . ", " . rand(0,100); }  ', '', '"numeros=[$tab]"', '', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 7, 3,'Question 7', 'Question 7', 'Faites afficher sous forme de tableau les éléments 1 à 8 de <em>numeros</em>');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', '$tab=rand(0,100); $tab1=""; for ($i=0;$i<8;$i++){   $tab1=$tab1 . ", " . rand(0,100); }  $rep="[" . substr("$tab1]",2); $tab=$tab . $tab1; for ($i=0;$i<10;$i++){   $tab=$tab . ", " . rand(0,100); } ', '', '"numeros=[$tab]"', '', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 8, 3,'Question 8', 'Question 8', 'Faites afficher sous forme de tableau les 4 derniers éléments de <em>numeros</em>');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', '$tab=rand(0,100); for ($i=0;$i<rand(3,10);$i++){   $tab=$tab . ", " . rand(0,100); }  $tab1=rand(0,100); for ($i=0;$i<3;$i++){   $tab1=$tab1 . ", " . rand(0,100); }  $rep="[$tab1]"; ', '', '"numeros=[$tab,  $tab1]"', '', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 9, 3,'Question 9', 'Question 9', 'Faites afficher sous forme de tableau tous les éléments de <em>numeros</em> dans l\'ordre inverse.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', '$tab=rand(0,100); $rep=$tab; for ($i=0;$i<rand(3,10);$i++){   $num=rand(0,100);   $tab=$tab . ", " . $num;   $rep=$num . ", " . $rep; }  $rep="[$rep]"; ', '', '"numeros=[$tab]"', '', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 10, 3,'Question 10', 'Question 10', 'Insérez les nombres 17, 42 et 25 au milieu de <em>numeros</em> puis faites afficher tous ses éléments sous forme de tableau, sachant que le tableau <em>numeros</em> est le taille fixe.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', '$tab=rand(0,100); for ($i=0;$i<4;$i++){   $num=rand(0,100);   $tab=$tab . ", " . $num; } $rep=$tab . ", 17, 42, 25, "; $tab1=rand(0,100); for ($i=0;$i<4;$i++){   $num=rand(0,100);   $tab1=$tab1 . ", " . $num; } $rep=$rep . $tab1;  $rep="[$rep]"; ', '', '"numeros=[$tab, $tab1]"', '', '');
    
INSERT INTO question (type, numero, serieID, titre, description, enonce) VALUES (0, 11, 3,'Question 11', 'Question 11', 'Insérez les nombres 17, 42 et 25 au milieu de <em>numeros</em> puis faites afficher tous ses éléments sous forme de tableau, sachant que le tableau <em>numeros</em> est le taille variable.');
INSERT INTO question_prog (questionID, reponse, setup, pre_exec, pre_code, in_code, post_code) VALUES ((SELECT max(questionID) FROM question), '$rep', 'echo   $n=rand(4,8); $tab=rand(0,100); for ($i=0;$i<$n;$i++){   $num=rand(0,100);   $tab=$tab . ", " . $num; } $rep=$tab . ", 17, 42, 25, "; $tab1=rand(0,100); for ($i=0;$i<$n;$i++){   $num=rand(0,100);   $tab1=$tab1 . ", " . $num; } $rep=$rep . $tab1;  $rep="[$rep]"; ', '', '"numeros=[$tab, $tab1]"', '', '');