<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\Zombie;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\RedstoneParticle;

//for mojang slack to show a client side drag bug with player entities
class TestLaunchpad extends Entity
{

	/** @var Vector3 */
	private $target;

	public function __construct(Location $location, ?CompoundTag $nbt = null)
	{
		parent::__construct($location, $nbt);
		$this->correctRotation();
	}

	public function getName(): string
	{
		return "Launchpad";
	}

	public function canBeMovedByCurrents(): bool
	{
		return false;
	}

	/**
	 * @return Vector3
	 */
	public function getTarget(): Vector3
	{
		if ($this->target === null) {
			$this->target = $this->location->add(0, 0, 30);
		}
		return $this->target;
	}

	public function entityBaseTick(int $tickDiff = 1): bool
	{
		$this->getWorld()->addParticle($this->target->asVector3(), new RedstoneParticle());
		$this->correctRotation();
		$this->setNameTagVisible();
		$this->setNameTag((string)$this->target->asVector3());
		if (count(($colliding = $this->getWorld()->getCollidingEntities($this->getBoundingBox(), $this))) > 0) {
			foreach ($colliding as $entity) {
				if ($entity->isOnGround() && $this->canCollideWith($entity)) {
					$this->launch($entity);
				}
			}
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function canBeCollidedWith(): bool
	{
		return true;
	}

	public function canCollideWith(Entity $entity): bool
	{
		return $entity instanceof Zombie || $entity instanceof NPCHuman || $entity instanceof Player;
	}

	public function correctRotation()
	{
		$loc = $this->getLocation();
		$this->locationLookAt($loc, $this->getTarget());
		$dir = $this->getLocationDirection($loc);
		switch ($dir) {
			case 2:
				$rot = 90;
				break;
			case 0:
				$rot = 270;
				break;
			case 3:
				$rot = 180;
				break;
			case 1:
				$rot = 0;
				break;
			default:
				$rot = 0;
		}
		$this->setRotation($rot, $this->getLocation()->getPitch());
	}

	//https://vilbeyli.github.io/Projectile-Motion-Tutorial-for-Arrows-and-Missiles-in-Unity3D/
	public function launch(Entity $entity): void
	{
		//Init values
		$transform = $this->getLocation();
		$TargetObjectTF = $this->getTarget();
		var_dump($TargetObjectTF);

		if ($transform->asVector3()->equals($TargetObjectTF)) return;

		$Physics_gravity_y = Loader::getInstance()->readAnyValue($entity, 'gravity');
		$LaunchAngle = 45;

		// think of it as top-down view of vectors:->floor()
		//   we don't care about the y-component(height) of the initial and target position.
		/** @var Vector3 */
		$projectileXZPos = new Vector3(floor($transform->x) + 0.5, 0.0, floor($transform->z) + 0.5);
		/** @var Vector3 */
		$targetXZPos = new Vector3(floor($TargetObjectTF->x) + 0.5, 0.0, floor($TargetObjectTF->z) + 0.5);

		// rotate the object to face the target
		$this->locationLookAt($transform, $targetXZPos);

		// shorthands for the formula
		/** @var float */
		$R = $projectileXZPos->distance($targetXZPos);
		/** @var float */
		$G = $Physics_gravity_y;
		/** @var float */
		$tanAlpha = tan(deg2rad($LaunchAngle));
		/** @var float */
		$H = $TargetObjectTF->y - $transform->y;

		// calculate the local space components of the velocity
		// required to land the projectile on the target object
		/** @var float */
		$Vz = sqrt(abs($G * $R * $R / (2.0 * ($H - $R * $tanAlpha))));
		/** @var float */
		$Vy = $tanAlpha * $Vz;

		// create the velocity vector in local space and get it in global space
		/** @var Vector3 */
		$localVelocity = new Vector3(0, $Vy, $Vz);
		// Rotate local into global space
		$angleInRadians = deg2rad($transform->getYaw());
		$cosTheta = cos($angleInRadians);
		$sinTheta = sin($angleInRadians);
		$X = $cosTheta * $localVelocity->getX() - $sinTheta * $localVelocity->getZ();
		var_dump($X);
		$Z = $sinTheta * $localVelocity->getX() + $cosTheta * $localVelocity->getZ();
		var_dump($Z);
		$globalVelocity = new Vector3($X, $localVelocity->getY(), $Z);
		// launch the object by setting its initial velocity
		//$entity->teleport($transform->add(0, 0.5, 0));
		$entity->setMotion($globalVelocity->multiply(1.75));
		$entity->setNameTagVisible(true);
		$entity->setNameTag((string)$globalVelocity->asVector3());
		$this->getWorld()->addParticle($entity->location->addVector($globalVelocity->asVector3()), new HappyVillagerParticle());
	}

	public function locationLookAt(Location &$location, Vector3 $target): void
	{
		$horizontal = sqrt(($target->x - $location->x) ** 2 + ($target->z - $location->z) ** 2);
		$vertical = $target->y - $location->y;
		$this->pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $location->x;
		$zDist = $target->z - $location->z;
		$location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($location->yaw < 0) {
			$location->yaw += 360.0;
		}
	}

	/**
	 * Returns the direction for a given location
	 * @param Location $location
	 * @return int|null
	 */
	public function getLocationDirection(Location $location): ?int
	{
		$rotation = ($location->yaw - 90) % 360;
		if ($rotation < 0) {
			$rotation += 360.0;
		}
		if ((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)) {
			return 2; //North
		} else if (45 <= $rotation and $rotation < 135) {
			return 3; //East
		} else if (135 <= $rotation and $rotation < 225) {
			return 0; //South
		} else if (225 <= $rotation and $rotation < 315) {
			return 1; //West
		} else {
			return null;
		}
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1 / 16, 1.0);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::SLIME;
	}
}
