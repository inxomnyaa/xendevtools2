<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use pocketmine\player\Player;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class NPCSword extends NPCBase
{

	private const NPC_SKIN_FOLDER = 'skins' . DIRECTORY_SEPARATOR . 'npcs' . DIRECTORY_SEPARATOR;

	public function sendSpawnPacket(Player $player): void
	{
		$uuid = Uuid::uuid4();
		$id = Entity::nextRuntimeId();
		$location = $player->getLocation();

		$networkSession = $player->getNetworkSession();
		$networkSession->sendDataPacket(PlayerListPacket::add([$entry = $this->getListEntry($uuid, $id)]));

		$pk = new AddPlayerPacket();
		$pk->uuid = $uuid;
		$pk->username = 'Sword';
		$pk->entityRuntimeId = $id;
		$pk->position = $location->asVector3();
		$pk->yaw = $location->getYaw();
		$pk->pitch = $location->getPitch();
		$pk->item = ItemStackWrapper::legacy(ItemStack::null());
		$networkSession->sendDataPacket($pk);

		$networkSession->sendDataPacket(PlayerListPacket::remove([$entry]));
	}

	public function getListEntry(UuidInterface $uuid, int $id): PlayerListEntry
	{
		return PlayerListEntry::createAdditionEntry($uuid, $id, 'Sword', $this->getSkinData());
	}

	public function getSkinData(): SkinData
	{
		$geometryName = 'geometry.sword';
		$geometry = Loader::getInstance()->getResource(self::NPC_SKIN_FOLDER . 'sword.geo.json');

		$geometryData = stream_get_contents($geometry);
		fclose($geometry);

		$resourcePatch = json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR);
		$skinImage = $this->getSkinImageFromResources(self::NPC_SKIN_FOLDER . 'sword_compact.png');
		return new SkinData('Sword', '', $resourcePatch, $skinImage, [], null, $geometryData, '', true);
	}

	public function getSkinImageFromResources(string $filename): SkinImage
	{
		/** @var resource $png */
		$png = Loader::getInstance()->getResource($filename);
		/** @var string $pngContent */
		$pngContent = stream_get_contents($png);

		fclose($png);

		return $this->getSkinImageFromString($pngContent);
	}

	public function getSkinImageFromString(string $string): SkinImage
	{
		$img = imagecreatefromstring($string);
		[$width, $height] = getimagesizefromstring($string);
		$bytes = '';

		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$argb = imagecolorat($img, $x, $y);
				$bytes .= chr(($argb >> 16) & 0xff) . chr(($argb >> 8) & 0xff) . chr($argb & 0xff) . chr(((~((int)($argb >> 24))) << 1) & 0xff);
			}
		}

		imagedestroy($img);
		return new SkinImage($height, $width, $bytes);
	}

	protected function entityBaseTick(int $tickDiff = 1): bool
	{
		$this->getLocation()->yaw = $this->getLocation()->yaw + 5 % 360;
		return parent::entityBaseTick($tickDiff); // TODO: Change the autogenerated stub
	}
}