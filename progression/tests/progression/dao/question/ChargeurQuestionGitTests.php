<?php
/*
   This file is part of Progression.

   Progression is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Progression is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Progression.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace progression\dao\question;

use progression\TestCase;
use Mockery;
use Gitonomy\Git\Admin;
use Gitonomy\Git\Repository;
use RuntimeException;

final class ChargeurQuestionGitTests extends TestCase
{
	private $contenu_tmp;

	public function setUp(): void
	{
		parent::setUp();

		$this->contenu_tmp = scandir("/tmp");
	}

	public function tearDown(): void
	{
		$this->assertEquals($this->contenu_tmp, scandir("/tmp"));

		parent::tearDown();
	}

	public function test_étant_donné_un_url_dépôt_git_lorsquon_charge_la_question_on_obtient_un_objet_Question_correspondant()
	{
	}

	public function test_étant_donné_un_url_dépôt_git_privé_lorsquon_charge_la_question_on_obtient_une_exception_avec_un_message()
	{
	}

	public function test_étant_donné_un_url_dépôt_git_dans_lequel_le_fichier_infoYml_est_inexistant_lorsquon_charge_la_question_on_obtient_une_exception_avec_un_message()
	{
	}

	public function test_étant_donné_un_répertoire_temporaire_lorsquon_essaye_de_récupérer_le_dernier_commit_on_obtient_le_hash_du_commit()
	{

		$repertoireTemporaire = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'git_test_' . uniqid();
		Admin::init($repertoireTemporaire, false);

		$repository = new Repository($repertoireTemporaire);

		$repository->run('config', ['user.email', 'test@example.com']);
		$repository->run('config', ['user.name', 'Test User']);

		file_put_contents($repertoireTemporaire . DIRECTORY_SEPARATOR . 'file.txt', "Contenu de test");
		$repository->run('add', ['.']);
		$repository->run('commit', ['-m', 'Commit de test']);

		$hashAttendu = trim($repository->run('rev-parse', ['HEAD']));

		$chargeurQuestionGit = new ChargeurQuestionGit();
    
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod('getIdDernierCommit');
		$methode->setAccessible(true);
    
	 	$resultat = $methode->invokeArgs($chargeurQuestionGit, [$repertoireTemporaire]);

		$this->assertEquals($hashAttendu, $resultat);

		system("rm -rf " . escapeshellarg($repertoireTemporaire));
	}

	public function test_étant_donné_un_répertoire_temporaire_avec_aucun_commit_lorsquon_essaye_de_récupérer_un_commit_on_obtient_une_RuntimeException()
	{
		$repertoireTemporaire = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'git_test_' . uniqid();
		Admin::init($repertoireTemporaire, false);

		$chargeurQuestionGit = new ChargeurQuestionGit();
    
		$reflection = new \ReflectionClass(get_class($chargeurQuestionGit));
		$methode = $reflection->getMethod('getIdDernierCommit');
		$methode->setAccessible(true);
    
	 	try {
    
        	$methode->invokeArgs($chargeurQuestionGit, [$repertoireTemporaire]);
        	$this->fail();
    	} catch (RuntimeException $e) {
        	$this->assertEquals("Aucun commit trouvé dans le dépôt cloné.", $e->getMessage());
    	}

		system("rm -rf " . escapeshellarg($repertoireTemporaire));


	}

	public function test_étant_donné_un_uri_invalide_losquon_met_en_cache_la_question_on_obtient_une_exception_avec_un_message_spécifique()
	{
	}
}
