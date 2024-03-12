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

use progression\dao\question\QuestionDAO;
use progression\dao\tentative\{TentativeDAO, TentativeProgDAO, TentativeSysDAO};
use progression\dao\exécuteur\{ExécuteurCache, ExécuteurCompilebox, ExécutionException, Standardiseur};
use progression\dao\mail\Expéditeur;

class DAOFactory
{
	private static $laFactory = null;

	private function __construct()
	{
	}

	static function getInstance()
	{
		if (DAOFactory::$laFactory == null) {
			DAOFactory::$laFactory = new DAOFactory();
		}
		return DAOFactory::$laFactory;
	}

	static function setInstance($uneFactory)
	{
		DAOFactory::$laFactory = $uneFactory;
	}

	function get_avancement_dao()
	{
		return new AvancementDAO($this);
	}

	function get_question_dao()
	{
		return new QuestionDAO($this);
	}

	function get_tentative_dao()
	{
		return new TentativeDAO($this);
	}

	function get_tentative_prog_dao()
	{
		return new TentativeProgDAO($this);
	}

	function get_tentative_sys_dao()
	{
		return new TentativeSysDAO($this);
	}

	function get_user_dao()
	{
		return new UserDAO($this);
	}

	function get_clé_dao()
	{
		return new CléDAO($this);
	}
	function get_commentaire_dao()
	{
		return new CommentaireDAO($this);
	}

	function get_sauvegarde_dao()
	{
		return new SauvegardeDAO($this);
	}

	function get_exécuteur()
	{
		return new ExécuteurCache(new ExécuteurCompilebox(), new Standardiseur());
	}

	function get_expéditeur(): Expéditeur
	{
		return new Expéditeur();
	}

    function get_banque_dao() : BanqueDAO
    {
        return new BanqueDAO($this);
    }        
}
