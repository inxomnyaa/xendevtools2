<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use muqsit\invmenu\inventory\InvMenuInventory;
use pocketmine\inventory\SimpleInventory;

class LlamaInventory extends SimpleInventory /*implements BlockInventory*/
{
	public function createInventory(): InvMenuInventory
	{
		return new InvMenuInventory($this->getSize());
	}
}