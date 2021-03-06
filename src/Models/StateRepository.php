<?php declare(strict_types = 1);

/**
 * StateRepository.php
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

use FastyBird\CouchDbStoragePlugin\Connections;
use FastyBird\CouchDbStoragePlugin\Exceptions;
use FastyBird\CouchDbStoragePlugin\States;
use Nette;
use PHPOnCouch;
use Psr\Log;
use Ramsey\Uuid;
use stdClass;
use Throwable;

/**
 * Device property state repository
 *
 * @package        FastyBird:CouchDbStoragePlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class StateRepository implements IStateRepository
{

	use Nette\SmartObject;

	/** @var Connections\ICouchDbConnection */
	private Connections\ICouchDbConnection $dbClient;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	public function __construct(
		Connections\ICouchDbConnection $dbClient,
		?Log\LoggerInterface $logger = null
	) {
		$this->dbClient = $dbClient;

		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOne(
		Uuid\UuidInterface $id,
		string $class = States\State::class
	): ?States\IState {
		$doc = $this->getDocument($id);

		if ($doc === null) {
			return null;
		}

		return States\StateFactory::create($class, $doc);
	}

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return PHPOnCouch\CouchDocument|null
	 */
	private function getDocument(
		Uuid\UuidInterface $id
	): ?PHPOnCouch\CouchDocument {
		try {
			$this->dbClient->getClient()
				->asCouchDocuments();

			/** @var stdClass[]|mixed $docs */
			$docs = $this->dbClient->getClient()
				->find([
					'id' => [
						'$eq' => $id->toString(),
					],
				]);

			if (is_array($docs) && count($docs) >= 1) {
				$doc = new PHPOnCouch\CouchDocument($this->dbClient->getClient());

				return $doc->loadFromObject($docs[0]);
			}

			return null;

		} catch (PHPOnCouch\Exceptions\CouchNotFoundException $ex) {
			return null;

		} catch (Throwable $ex) {
			$this->logger->error('[FB:PLUGIN:COUCHDB] Document could not be loaded', [
				'type'      => 'repository',
				'action'    => 'find_document',
				'property'  => $id->toString(),
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new Exceptions\InvalidStateException('Document could not be loaded from database', 0, $ex);
		}
	}

}
