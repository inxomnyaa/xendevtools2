<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use muqsit\invmenu\inventory\InvMenuInventory;

class LlamaInventory extends InvMenuInventory
{
	public function createInventory(): InvMenuInventory
	{
		return new InvMenuInventory($this->getSize());
	}
}