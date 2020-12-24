<?php

namespace xenialdan\xendevtools2;

use Exception;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\utils\TextFormat as TF;
use xenialdan\libstructure\format\MCStructure;

class TestListener implements Listener
{
	private $links = [];

	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();

		//.mcstructure to floating blocks test
		$world = $player->getServer()->getWorldManager()->getDefaultWorld();
		if ($world !== null) {
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof TestCart || $entity instanceof TestFallingBlock) $entity->close();
			}
			$spawn = $world->getSafeSpawn()->asVector3();
			$structureFiles = glob(Loader::getInstance()->getDataFolder() . 'structures' . DIRECTORY_SEPARATOR . "*.mcstructure");
			if ($structureFiles !== false)
				foreach ($structureFiles as $file) {
					Loader::getInstance()->getLogger()->debug(TF::GOLD . "Loading " . basename($file));
					try {
						$structure = new MCStructure();
						$structure->parse($file);
						$parentId = -1;
						foreach ($structure->blocks() as $block) {
							if ($block->getId() === BlockLegacyIds::AIR) continue;
							$offset = $block->getPos()->asVector3();
							$at = $spawn->addVector($block->getPos()->asVector3());
							[$block->getPos()->x, $block->getPos()->y, $block->getPos()->z, $block->getPos()->world] = [$at->x, $at->y, $at->z, $world];
							$cart = $this->getCart($block);
							if ($parentId === -1) $parentId = $cart->getId();//first cart - empty links
							else {
								$cart->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $offset);
								$cart->links[] = new EntityLink($cart->getId(), $parentId, EntityLink::TYPE_PASSENGER, true, false);
							}
							$cart->spawnTo($player);
						}
					} catch (Exception $e) {
						Loader::getInstance()->getLogger()->debug($e->getMessage());
					}
				}
		}
	}

	private function getCart(Block $block): TestFallingBlock
	{
		$cart = new TestFallingBlock(Location::fromObject($block->getPos()->add(0.5, 0, 0.5), $block->getPos()->getWorld()), $block);
		$cart->setCanSaveWithChunk(false);
		#$cart->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
		#$cart->setImmobile();
		#$cart->setHasGravity(false);
		/*$property = "gravity";
		$value = 0.0;
		Closure::bind(function & () use($property,$value) {
			$this->$property = $value;
		}, $cart, $cart)->__invoke();*/
		return $cart;
	}

	/*public function onPacketSend(DataPacketSendEvent $event){
		foreach ($event->getPackets() as $packet){
			if($packet instanceof AddActorPacket){
				if(array_key_exists($packet->entityRuntimeId,$this->links))
					$packet->links = $this->links[$packet->entityRuntimeId];
			}
		}
	}*/
}