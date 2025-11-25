<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getGid()
 * @method string getUid()
 */
final class GroupUserRelation extends Entity {
	/** @var string */
	protected $gid;
	/** @var string */
	protected $uid;

	private function getId(): void {
	}

	public function __construct() {
		$this->addType('gid', 'string');
		$this->addType('uid', 'string');
	}
}
