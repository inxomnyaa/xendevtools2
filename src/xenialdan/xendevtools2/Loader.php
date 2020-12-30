<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use Closure;
use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

class Loader extends PluginBase implements Listener
{
	/** @var self */
	private static $instance;

	/**
	 * Returns an instance of the plugin
	 * @return self
	 */
	public static function getInstance(): self
	{
		return self::$instance;
	}

	public function onLoad()
	{
		EntityFactory::getInstance()->register(NPCBase::class, function (World $world, CompoundTag $nbt): NPCBase {
			return new NPCBase(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ['NPCBase', 'npc:base'], EntityLegacyIds::NPC);
		EntityFactory::getInstance()->register(NPCHuman::class, function (World $world, CompoundTag $nbt): NPCHuman {
			return new NPCHuman(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ['NPCHuman', 'npc:human'], EntityLegacyIds::PLAYER);
		EntityFactory::getInstance()->register(TestFallingBlock::class, function (World $world, CompoundTag $nbt): TestFallingBlock {
			return new TestFallingBlock(EntityDataHelper::parseLocation($nbt, $world), TestFallingBlock::parseBlockNBT(BlockFactory::getInstance(), $nbt), $nbt);
		}, ['TestFallingBlock', 'xenialdevtools2:testfallingblock'], EntityLegacyIds::FALLING_BLOCK);
		EntityFactory::getInstance()->register(TestCart::class, function (World $world, CompoundTag $nbt): TestCart {
			return new TestCart(EntityDataHelper::parseLocation($nbt, $world), TestFallingBlock::parseBlockNBT(BlockFactory::getInstance(), $nbt), $nbt);
		}, ['TestCart', 'xenialdevtools2:testcart'], EntityLegacyIds::MINECART);
		self::$instance = $this;

		#$rtbm = RuntimeBlockMapping::getInstance();
		#var_dump($rtbm);
		#var_dump(self::readAnyValue($rtbm, 'legacyToRuntimeMap'));
		#foreach (self::readAnyValue($rtbm, 'legacyToRuntimeMap') as $k => $v) {
		#$expression = $rtbm->fromRuntimeId($k);
		#var_dump("ID:{$expression[0]} META:{$expression[1]}", $v);
		#	var_dump($k);
		#}
		#foreach (self::readAnyValue($rtbm, 'runtimeToLegacyMap') as $k => $v) {
		#$expression = $rtbm->fromRuntimeId($k);
		#var_dump("ID:{$expression[0]} META:{$expression[1]}", $v);
		#	var_dump($k);
		#}
		#var_dump(self::readAnyValue($rtbm, 'runtimeToLegacyMap'));
		#var_dump(self::readAnyValue($rtbm, 'bedrockKnownStates'));
		#var_dump(self::readAnyValue($rtbm, 'startGamePaletteCache'));
	}

	public function onEnable(): void
	{
		//$this->getServer()->getPluginManager()->registerEvents(new TestMCStructureListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new TestAnimationsListener(), $this);
		/*NpcDialog::register($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$loc = Location::fromObject($this->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());

		$e = new NPCBase($loc);
		$loc->getWorld()->addEntity($e);
		$e->spawnToAll();

		$form = new DialogForm("This is the dialog text");

		$form->addButton(new Button("Hi", function (Player $player) {
			$player->sendMessage("Hi!!");
		}));

		$form->setCloseListener(function (Player $player) {
			$player->sendMessage("You closed the form!");
		});

		$form->pairWithEntity($e);*/
	}

	public function onDisable(): void
	{
	}

	public function onJoin(PlayerJoinEvent $event): void
	{
		$loc = $event->getPlayer()->getLocation();

		/*
		$e = new NPCBase($loc);
		$event->getPlayer()->getWorld()->addEntity($e);
		$e->spawnToAll();

		$form = new DialogForm("This is the dialog text");

		$form->addButton(new Button("Hi", function (Player $player) {
			$player->sendMessage("Hi!!");
		}));

		$form->setCloseListener(function (Player $player) {
			$player->sendMessage("You closed the form!");
		});

		$form->pairWithEntity($e);*/
	}

	public function onSneak(PlayerToggleSneakEvent $event): void
	{/*
		if (!$event->isSneaking()) return;
		$loc = $event->getPlayer()->getLocation();

		$e = new NPCBase($loc);
		$event->getPlayer()->getWorld()->addEntity($e);
		$e->spawnToAll();

		$form = new DialogForm("This is the dialog text");

		$form->addButton(new Button("Hi", function (Player $player) {
			$player->sendMessage("Hi!!");
		}));

		$form->setCloseListener(function (Player $player) {
			$player->sendMessage("You closed the form!");
		});

		$form->pairWithEntity($e);*/
	}

	public function &readAnyValue($object, $property)
	{
		$invoke = Closure::bind(function & () use ($property) {
			return $this->$property;
		}, $object, $object)->__invoke();
		$value = &$invoke;

		return $value;
	}
}