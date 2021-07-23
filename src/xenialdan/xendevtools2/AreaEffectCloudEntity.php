<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\color\Color;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\player\Player;
use pocketmine\utils\Binary;

class AreaEffectCloudEntity extends Entity
{
	/** @var EntityLink[] */
	public $links = [];
	protected $gravity = 0.0;
	protected $gravityEnabled = false;
	protected $immobile = true;
	/* * @var bool */
	private $savedWithChunk = true;

	private int $PotionId = 0;
	private float $Radius = 3;
	private float $RadiusOnUse = -0.5;
	private float $RadiusPerTick = -0.005;
	private int $WaitTime = 10;
	private int $TileX = 0;
	private int $TileY = 0;
	private int $TileZ = 0;
	private int $Duration = 600;
	private int $DurationOnUse = 0;
	private float $InitialRadius;
	private float $RadiusChangeOnPickup;
	private int $ReapplicationDelay;
	private int $ParticleId;
	private Color $ParticleColor;
	private int $SpawnTick;
	private int $PickupCount;

	protected function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);

		$this->PotionId = $nbt->getShort("PotionId", 0);
		$this->PickupCount = $nbt->getInt("PickupCount", 0);
		$this->Radius = $nbt->getFloat("Radius", 3);
		$this->InitialRadius = $nbt->getFloat("InitialRadius", 3);
		$this->RadiusOnUse = $nbt->getFloat("RadiusOnUse", -0.5);
		$this->RadiusPerTick = $nbt->getFloat("RadiusPerTick", -0.005);
		$this->RadiusChangeOnPickup = $nbt->getFloat("RadiusChangeOnPickup", -0.5);
		$this->ReapplicationDelay = $nbt->getInt("ReapplicationDelay", 40);
		$this->ParticleId = $nbt->getInt("ParticleId", ParticleIds::MOB_SPELL_AMBIENT);
		$this->ParticleColor = Color::fromARGB($nbt->getInt("ParticleColor", (new Color(0,0,0))->toARGB()));//ARGB
		$this->WaitTime = $nbt->getInt("WaitTime", 10);
		$this->SpawnTick = $nbt->getLong("SpawnTick", 0);
		$this->TileX = $nbt->getInt("TileX", 0);
		$this->TileY = $nbt->getInt("TileY", 0);
		$this->TileZ = $nbt->getInt("TileZ", 0);
		$this->Duration = $nbt->getInt("Duration", 600);
		$this->DurationOnUse = $nbt->getInt("DurationOnUse", 0);
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
		$pk->metadata = $this->getAllNetworkData();
		$pk->links = array_values($this->links);

		$player->getNetworkSession()->sendDataPacket($pk);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties): void
	{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
		$properties->setGenericFlag(EntityMetadataFlags::ONFIRE, false);

		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_PARTICLE_ID, ParticleIds::MOB_SPELL_AMBIENT);//todo
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS, $this->Radius);
		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_WAITING, $this->WaitTime);
		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_DURATION, $this->Duration);//todo
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_SPAWN_TIME, $this->SpawnTick);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS_PER_TICK, $this->RadiusPerTick);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS_CHANGE_ON_PICKUP, $this->RadiusChangeOnPickup);
		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_PICKUP_COUNT, $this->PickupCount);
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 1);
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, $this->Radius * 2);
		$properties->setInt(EntityMetadataProperties::POTION_COLOR,Binary::signInt($this->ParticleColor->toARGB()));
		$properties->setByte(EntityMetadataProperties::POTION_AMBIENT, 1);
	}

	public function canSaveWithChunk(): bool
	{
		return false;
	}

	/*public function saveNBT(): CompoundTag
	{
		$nbt = parent::saveNBT();
		$nbt->setShort("PotionId", $this->PotionId);
		$nbt->setFloat("Radius", $this->Radius);
		$nbt->setFloat("RadiusOnUse", $this->RadiusOnUse);
		$nbt->setFloat("RadiusPerTick", $this->RadiusPerTick);
		$nbt->setInt("WaitTime", $this->WaitTime);
		$nbt->setInt("TileX", $this->TileX);
		$nbt->setInt("TileY", $this->TileY);
		$nbt->setInt("TileZ", $this->TileZ);
		$nbt->setInt("Duration", $this->Duration);
		$nbt->setInt("DurationOnUse", $this->DurationOnUse);

		return $nbt;
	}*/

	protected function entityBaseTick(int $tickDiff = 1): bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$world = $this->getWorld();

		if (!$this->isFlaggedForDespawn()) {
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_PARTICLE_ID, ($this->ticksLived % 2 === 0 ? ParticleIds::DRIP_WATER : ParticleIds::MOB_SPELL));//todo
			$color = $this->updateColor($this->ticksLived);
			$type = PotionTypeIdMap::getInstance()->fromId($this->PotionId);
			$effects = $type->getEffects();
			foreach ($effects as $effect) {
				$color = $effect->getColor();
			}
			$this->ParticleColor = $color = $color ?? new Color(0, 0, 0);
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::POTION_COLOR, Binary::signInt($this->ParticleColor->toARGB()));
			#$this->getNetworkProperties()->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS, $this->Radius);
			#$this->getNetworkProperties()->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_WAITING, $this->WaitTime);
		}

		return $hasUpdate;
	}

	private function updateColor(int $step): Color
	{
		$center = 128;
		$width = 127;
		$frequency = 100;//controls change speed
		$red = sin($frequency * $step + 2) * $width + $center;
		$green = sin($frequency * $step + 0) * $width + $center;
		$blue = sin($frequency * $step + 4) * $width + $center;
		return new Color((int)$red, (int)$green, (int)$blue);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1, 5, 0.5);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::AREA_EFFECT_CLOUD;
	}

	public function getName(): string
	{
		return 'AreaEffectCloud#' . spl_object_id($this);
	}
}