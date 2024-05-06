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

namespace progression\services\git;

use Gitonomy\Git\Admin;

class GitImpl
{
	/**
	 * @param array<string> $options
	 */
	public function clone(string $destination, string $url, array $options): void
	{
		Admin::cloneTo($destination, $url, false, $options);
	}

	/**
	 * @param array<string> $patterns
	 * @param array<string> $options
	 * @return array<string>
	 */
	public function ls_remote(string $url_dépôt, array $patterns, array $options): array
	{
		$getProcess = (new \ReflectionClass(Admin::class))->getMethod("getProcess");
		$args = array_merge($options, [$url_dépôt], $patterns);
		$processus = $getProcess->invokeArgs(null, ["ls-remote", $args]);

		$processus->run();

		if (!$processus->isSuccessful()) {
			throw new \RuntimeException($processus->getErrorOutput());
		}
		return explode("\n", $processus->getOutput());
	}
}
