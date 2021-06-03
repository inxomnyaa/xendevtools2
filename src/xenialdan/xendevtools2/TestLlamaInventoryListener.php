<?php

namespace xenialdan\xendevtools2;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class TestLlamaInventoryListener implements Listener
{
	public const TYPE_LLAMA = "invmenu:llama";

	public function onSneak(PlayerToggleSneakEvent $event)
	{
		$player = $event->getPlayer();

		$world = $player->getWorld();
		if ($event->isSneaking() && $world !== null) {
			$menu = InvMenu::create(self::TYPE_LLAMA);
			var_dump($menu);
			$menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
				$player = $transaction->getPlayer();
				$itemClicked = $transaction->getItemClicked();
				$itemClickedWith = $transaction->getItemClickedWith();
				$action = $transaction->getAction();
				$inv_transaction = $transaction->getTransaction();
				var_dump($player, $itemClicked, $itemClickedWith, $action, $inv_transaction);
				return $transaction->continue();
			});
			$menu->send($player);
		}
	}

	public static function registerCustomMenuTypes(): void
	{
		$type = new LlamaInventoryMetadata(
			self::TYPE_LLAMA, // identifier
			64, // number of slots
			WindowTypes::HORSE // mcpe window type id
		);
		InvMenuHandler::registerMenuType($type, true);
	}
}