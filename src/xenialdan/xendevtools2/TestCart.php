<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\block\Block;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\Vec3MetadataProperty;
use pocketmine\player\Player;

class TestCart extends Entity
{
	public static function getNetworkTypeId(): string
	{
		return EntityIds::MINECART;
	}

	public $width = 0.98;
	public $height = 0.98;

	/** @var EntityLink[] */
	public $links = [];
	protected $gravity = 0.0;
	protected $gravityEnabled = false;
	protected $immobile = true;
	/* * @var bool */
	private $savedWithChunk = true;
	public ?Block $block = null;

	public function __construct(Location $location, Block $block, ?CompoundTag $nbt = null)
	{
		$this->block = $block;
		parent::__construct($location, $nbt);
	}

	protected function sendSpawnPacket(Player $player): void
	{
		$pk = new AddActorPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = static::getNetworkTypeId();
		$pk->position = $this->location->asVector3();
		$pk->motion = $this->getMotion();
		$pk->yaw = $this->location->yaw;
		$pk->headYaw = $this->location->yaw; //TODO
		$pk->pitch = $this->location->pitch;
		$pk->attributes = array_map(function (Attribute $attr): NetworkAttribute {
			return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue());
		}, $this->attributeMap->getAll());
		$pk->metadata = $this->getSyncedNetworkData(false);
		$pk->links = array_values($this->links);

		$player->getNetworkSession()->sendDataPacket($pk);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties): void
	{
		parent::syncNetworkData($properties);

		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
		$properties->setInt(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, RuntimeBlockMapping::getInstance()->toRuntimeId($this->block->getFullId()));
		$properties->setByte(EntityMetadataProperties::MINECART_HAS_DISPLAY, 1);
		$properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
		$properties->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
		$properties->setGenericFlag(EntityMetadataFlags::SILENT, true);
	}

	protected function entityBaseTick(int $tickDiff = 1): bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$world = $this->getWorld();

		if (!$this->isFlaggedForDespawn()) {
			if ($this->ticksLived % 20 === 0) {
				if (empty($this->links))
					[$this->location->x, $this->location->z] = [$this->location->x + random_int(-1, 1), $this->location->z + random_int(-1, 1)];
				else {
					$link = $this->links[0];
					$parentId = $link->toEntityUniqueId;
					$parent = $world->getEntity($parentId);
					if (!$parent instanceof self || $parent->isFlaggedForDespawn()) {
						$this->flagForDespawn();
					} else {
						$parentLoc = $parent->getLocation();
						/** @var Vec3MetadataProperty $offsetProp */
						$offsetProp = $this->getNetworkProperties()->getAll()[EntityMetadataProperties::RIDER_SEAT_POSITION];
						[$this->location->x, $this->location->z] = [$parentLoc->x + $offsetProp->getValue()->x, $parentLoc->z + $offsetProp->getValue()->z];
					}
				}
			}
			$pos = $this->location->add(-$this->width / 2, $this->height, -$this->width / 2)->floor();
			$this->block->position($world, $pos->x, $pos->y, $pos->z);
		}

		return $hasUpdate;
	}

}