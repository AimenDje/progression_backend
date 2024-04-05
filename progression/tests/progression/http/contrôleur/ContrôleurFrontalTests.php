<?php

use progression\ContrôleurTestCase;

use Illuminate\Support\Facades\Config;

final class ContrôleurFrontalTests extends ContrôleurTestCase
{
	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_inscrit_sans_fournir_de_type_on_obtient_une_erreur_409()
	{
		Config::set("authentification.local", false);
		Config::set("authentification.ldap", false);

		$résultat_observé = $this->json_api("PUT", "/user/bob", [
			"data" => [
				"attributes" => [
					"username" => "autre_nom",
				],
			],
		]);

		$this->assertEquals(409, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"type":["Le champ type est manquant ou ne correspond pas à un type valide"]}}',
			$résultat_observé->content(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_inscrit_sans_fournir_de_champ_attributs_on_obtient_une_erreur_400()
	{
		Config::set("authentification.local", false);
		Config::set("authentification.ldap", false);

		$résultat_observé = $this->json_api("PUT", "/user/bob", [
			"data" => [
				"type" => "user",
			],
		]);

		$this->assertEquals(400, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"username":["Le champ username est obligatoire."]}}',
			$résultat_observé->content(),
		);
	}

	public function test_étant_donné_un_utilisateur_inexistant_avec_authentification_lorsquon_inscrit_sans_fournir_de_données_on_obtient_une_erreur_409()
	{
		Config::set("authentification.local", false);
		Config::set("authentification.ldap", false);

		$résultat_observé = $this->json_api("PUT", "/user/bob", []);

		$this->assertEquals(409, $résultat_observé->status());
		$this->assertEquals(
			'{"erreur":{"type":["Le champ type est manquant ou ne correspond pas à un type valide"]}}',
			$résultat_observé->content(),
		);
	}
}

?>
