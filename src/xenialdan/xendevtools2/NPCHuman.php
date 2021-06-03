<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use Ramsey\Uuid\Uuid;

class NPCHuman extends NPCBase
{

	public static function getNetworkTypeId(): string
	{
		return EntityIds::PLAYER;
	}

	protected function sendSpawnPacket(Player $player): void
	{
		$skin = $player->getSkin();
		$uuid = Uuid::uuid4();

		$player->getNetworkSession()->sendDataPacket(PlayerSkinPacket::create($uuid, SkinAdapterSingleton::get()->toSkinData($skin)));

		$pk = new AddPlayerPacket();
		$pk->uuid = $uuid;
		$pk->username = $player->getDisplayName();//can be whatever
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $player->getPosition()->asVector3();
		$pk->motion = new Vector3(0, 0, 0);
		$pk->yaw = $player->location->yaw;
		$pk->pitch = $player->location->pitch;
		$pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($player->getInventory()->getItemInHand()));
		$pk->metadata = $player->getAllNetworkData();//nametag is set in here
		$player->getNetworkSession()->sendDataPacket($pk);

		$player->getNetworkSession()->onMobArmorChange($this);
	}

	public function canBeCollidedWith(): bool
	{
		return true;
	}

	public function canCollideWith(Entity $entity): bool
	{
		return true;
	}
}