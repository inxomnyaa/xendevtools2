<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use muqsit\invmenu\metadata\MenuMetadata;
use muqsit\invmenu\session\MenuExtradata;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\world\World;

class LlamaInventoryMetadata extends MenuMetadata
{
	private int $id;

	public function sendGraphic(Player $player, MenuExtradata $metadata): bool
	{
		$positions = $this->getBlockPositions($metadata);
		if (count($positions) > 0) {
			foreach ($positions as $pos) {
				$this->sendGraphicAt($pos, $player, $metadata);
			}
			return true;
		}
		return false;
	}

	protected function sendGraphicAt(Vector3 $pos, Player $player, MenuExtradata $metadata): void
	{
		$meta = clone $player->getNetworkProperties();
		$meta->setInt(EntityMetadataProperties::CONTAINER_TYPE, WindowTypes::HORSE);
		$meta->setInt(EntityMetadataProperties::CONTAINER_BASE_SIZE, 1);
		$meta->setInt(EntityMetadataProperties::CONTAINER_EXTRA_SLOTS_PER_STRENGTH, 1);
		$meta->setInt(EntityMetadataProperties::STRENGTH, 16);
		$meta->setGenericFlag(EntityMetadataFlags::TAMED, true);
		$meta->setGenericFlag(EntityMetadataFlags::CHESTED, true);
		$meta->setGenericFlag(EntityMetadataFlags::SILENT, true);
		#$meta->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
		$meta->setGenericFlag(EntityMetadataFlags::IMMOBILE, true);
		$meta->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);

		$p = new AddActorPacket();
		$this->id = $p->entityRuntimeId = Entity::nextRuntimeId();
		$p->type = EntityIds::LLAMA;
		$p->position = $pos;
		$p->motion = null;
		$p->metadata = $meta->getAll();
		$player->getNetworkSession()->sendDataPacket($p);
		var_dump($p);

		$id = $player->getNetworkSession()->getInvManager()->getCurrentWindowId() + 1;

		$containerOpenPacket = ContainerOpenPacket::entityInv(
			$id,
			WindowTypes::HORSE,
			$this->id
		);
		var_dump($containerOpenPacket);
		$player->getNetworkSession()->sendDataPacket($containerOpenPacket);
	}

	public function removeGraphic(Player $player, MenuExtradata $extradata): void
	{
		$player->getNetworkSession()->sendDataPacket(ContainerClosePacket::create($this->id, true));
		$player->getNetworkSession()->sendDataPacket(RemoveActorPacket::create($this->id));
	}

	/**
	 * @param MenuExtradata $metadata
	 * @return Vector3[]
	 */
	protected function getBlockPositions(MenuExtradata $metadata): array
	{
		$pos = $metadata->getPositionNotNull();
		return $pos->y >= 0 && $pos->y < World::Y_MAX ? [$pos] : [];
	}
}