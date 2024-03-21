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

namespace progression\dao;

use progression\domaine\entité\banque\Banque;
use progression\dao\EntitéDAO;
use progression\dao\banque\ChargeurFactoryBanque;
use progression\dao\models\{BanqueMdl, UserMdl};
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use DomainException;
use BadMethodCallException;
use progression\domaine\entité\banque\QuestionBanque;
use Progression\dao\question\ChargeurException;
use RuntimeException;
class BanqueDAO extends EntitéDAO
{
	/**
	 * @param array<string> $includes
     * @return array<Banque>
	 */
	public function get_tous(string $username, array $includes = []): array
	{
        
		try {
			return $this->construireBanqueQuestion(BanqueMdl::select("banque.*")
                                     ->join("user", "banque.user_id", "=", "user.id")
                                     ->where("user.username", $username)
                                     ->get(),
                                     $includes,
            );
        } catch (QueryException $e) {
            throw new DAOException($e);
        }
    }

    public function save(string $username, Banque $banque): array
    {
        try {
            
            $user = UserMdl::query()->where("username", $username)->first();

            if (!$user) {
                throw new IntégritéException("Impossible de sauvegarder la ressource; le parent n'existe pas.");
            }
            
            $objet = [
                "nom" => $banque->nom,
                "url" => $banque->url,
                "user_id" => $user->id,
            ];

            //Vérification contenu.yml, doit avoir une liste commençant par "questions" et avoir au minimum un élément qui contient "nom" et "url".
            if (!$this->vérificationContenu($objet["url"])) {
                throw new RuntimeException("Le fichier {$objet["url"]} ne peut pas être décodé. Le format produit est invalide.");
            }
            
            return $this->construire([
                BanqueMdl::updateOrCreate([
                    'url' => $banque->url,],
                                          $objet)
            ]);
                                     } catch (QueryException $e) {
            throw new DAOException($e);
        }
    }

    public static function vérificationContenu(string $uri) : bool {
        try {
            $contenu = ChargeurFactoryBanque::get_instance()->get_chargeur_http()->get_url($uri);

            $info = yaml_parse($contenu);

            if ($info === false) {
                throw new RuntimeException("Le fichier {$uri} ne peut pas être décodé. Le format produit est invalide.");
            }

            $estValide = false;

            foreach ($info['questions'] as $question) {
                if (isset($question['nom']) && isset($question['url'])) {
                    $estValide = true;
                }
            }

            return $estValide;
            
        } catch (\Throwable $e) {
            throw new BadMethodCallException("Schéma d'URI invalide");
        }
    }
    
    public static function construire(mixed $data, $includes = []): array
	{
		$banques = [];
		foreach ($data as $item) {
            if ($item == null) {
                continue;
            }
            
            $banque = new Banque(
				$item["nom"],
				$item["url"],
            );

			$banques[$item["id"]] = $banque;
            }
        return $banques;
	}

	/**
     * @param array<string> $includes
     * @return array<Banque>
	 */    
    public static function construireBanqueQuestion(mixed $data, $includes = []): array
	{
		$banques = [];
		foreach ($data as $item) {
            if ($item == null) {
                continue;
            }

            try {
                $banque = new Banque(
                    $item["nom"],
                    $item["url"],
                );    
            
                $contenu = ChargeurFactoryBanque::get_instance()->get_chargeur_http()->get_url($banque->url);

                $info = yaml_parse($contenu);

                foreach ($info['questions'] as $question) {

                    $questionBanque = new QuestionBanque (

                        $question['nom'],
                        $question['url'],
                    );

                    $banque->ajouterQuestionsBanque($questionBanque);
                }
            } catch (\Throwable $e) {
                $banque = new Banque(
                    $item["nom"],
                    $item["url"],
                );    
                $questionBanque = new QuestionBanque ("erreur de lecture du fichier contenu.yml","",);

                $banque->ajouterQuestionsBanque($questionBanque);
            } 

            $banques[$item["id"]] = $banque;
            }
        return $banques;
	}
}
