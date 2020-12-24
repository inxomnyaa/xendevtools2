<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\PlayerInventory;

class NPCInventory extends PlayerInventory
{
	/** @var NPCBase */
	protected $holder;

	public function __construct(NPCBase $npc)
	{
		$this->holder = $npc;
		BaseInventory::__construct(36);
	}

	/**
	 * @return NPCBase
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	/**
	 * Returns the number of slots in the hotbar.
	 */
	public function getHotbarSize(): int
	{
		return 2;//Simple: one is air and one is hold item
	}
}