<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Psalm\ShiftTypeAlias;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-import-type Repetition from ShiftTypeAlias
 * @psalm-import-type Caldav from ShiftTypeAlias
 *
 * @method string getGroupId()
 * @method void setGroupId(string $groupId)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getColor()
 * @method void setColor(string $color)
 * @method bool getActive()
 * @method void setActive(bool $active)
 * @method Repetition getRepetition()
 * @method void setRepetition(array $repetition)
 * @method Caldav getCaldav()
 * @method void setCaldav(array $caldav)
 * @method bool getSyncToCalendar()
 * @method void setSyncToCalendar(bool $syncToCalendar)
 * @method ?int getCalendarId()
 * @method void setCalendarId(?int $calendarId)
 */
final class ShiftType extends Entity {
	/** @var string */
	protected $groupId;
	/** @var string */
	protected $name;
	/** @var string */
	protected $description;
	/** @var string */
	protected $color;
	/** @var bool */
	protected $active;
	/** @var Repetition */
	protected $repetition;
	/** @var Caldav */
	protected $caldav;
	/** @var bool */
	protected $syncToCalendar;
	/** @var ?int */
	protected $calendarId;

	public function __construct() {
		$this->addType('groupId', 'string');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('color', 'string');
		$this->addType('active', 'boolean');
		$this->addType('repetition', 'json');
		$this->addType('caldav', 'json');
		$this->addType('syncToCalendar', 'boolean');
		$this->addType('calendarId', 'integer');
	}
}
