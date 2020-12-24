<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class NPCBase extends Living
{
	public const SKIN_UGLY = 0;

	public static function getNetworkTypeId(): string
	{
		return EntityIds::NPC;
	}

	/** @var ArmorInventory */
	protected $armorInventory;
	/** @var NPCInventory */
	protected $inventory;

	public $width = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

	/** @var bool */
	private $baby = false;
	/** @var int */
	private $skinIndex = self::SKIN_UGLY;

	public function __construct(Location $location, ?CompoundTag $nbt = null)
	{
		$this->setSkinIndex(mt_rand(0, 19));
		//if (mt_rand(0, 1)) $this->baby = true;
		parent::__construct($location, $nbt);
		$this->inventory = new NPCInventory($this);
		#$this->armorInventory = new ArmorInventory($this);
		$this->setNameTagAlwaysVisible();
		$this->setNameTagVisible();
		$this->setScoreTag(TextFormat::DARK_RED . TextFormat::BOLD . "[!]" . $this->skinIndex);
	}

	protected function sendSpawnPacket(Player $player): void
	{
		parent::sendSpawnPacket($player);
		#$pk = MobArmorEquipmentPacket::create($this->id, TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getHelmet()), TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getChestplate()), TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getLeggings()), TypeConverter::getInstance()->coreItemStackToNet($this->armorInventory->getBoots()));
		#$player->getNetworkSession()->sendDataPacket($pk);
	}

	public function canSaveWithChunk(): bool
	{
		return false;
	}

	public function attack(EntityDamageEvent $source): void
	{
		#$source->setCancelled();
	}

	public function canBeCollidedWith(): bool
	{
		return false;
	}

	protected function checkBlockCollision(): void
	{
	}

	public function canCollideWith(Entity $entity): bool
	{
		return false;
	}

	public function canBeMovedByCurrents(): bool
	{
		return false;
	}

	public function canBreathe(): bool
	{
		return true;
	}

	public function getName(): string
	{
		return "NPC#" . spl_object_id($this);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties): void
	{
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);

		$properties->setInt(EntityMetadataProperties::NPC_SKIN_INDEX, $this->skinIndex);
		$properties->setInt(EntityMetadataProperties::SKIN_ID, $this->skinIndex);
		$properties->setInt(EntityMetadataProperties::VARIANT, $this->skinIndex);
	}

	/**
	 * Sets the NPC skin
	 */
	public function setSkinIndex(int $skinIndex): void
	{
		$this->skinIndex = $skinIndex; //TODO: validation
	}
}