<?php declare(strict_types = 1);

/**
 * IStateRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:CouchDbStoragePlugin!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           02.03.20
 */

namespace FastyBird\CouchDbStoragePlugin\Models;

use FastyBird\CouchDbStoragePlugin\States;
use Ramsey\Uuid;

/**
 * State repository interface
 *
 * @package        FastyBird:CouchDbStoragePlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IStateRepository
{

	/**
	 * @param Uuid\UuidInterface $id
	 * @param string $class
	 *
	 * @return States\IState|null
	 */
	public function findOne(
		Uuid\UuidInterface $id,
		string $class = States\State::class
	): ?States\IState;

}
