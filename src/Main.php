<?php

declare(strict_types=1);

namespace dktapps\HardnessCalculator;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @var float[]
	 * @phpstan-var array<int, float>
	 */
	private $timers = [];

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
			$this->timers[$event->getPlayer()->getId()] = microtime(true);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		if(isset($this->timers[$event->getPlayer()->getId()]) and !$event->getInstaBreak()){
			$actualTime = microtime(true) - $this->timers[$event->getPlayer()->getId()];
			$expectedTime = $event->getBlock()->getBreakInfo()->getBreakTime($event->getPlayer()->getInventory()->getItemInHand());
			if($expectedTime <= 0){
				return;
			}
			$ratio = $actualTime / $expectedTime;
			$expectedHardness = $event->getBlock()->getBreakInfo()->getHardness();
			$computedHardness = $expectedHardness * $ratio;

			$color = ($ratio < 0.95 or $ratio > 1.05) ? TextFormat::RED : TextFormat::GREEN;
			$message = sprintf(
				$color . "Block: %s\nExpected time: %g\nActual time: %g\nRatio: %g\nExpected hardness: %g\nCalculated hardness: %g",
				$event->getBlock()->getName(),
				$expectedTime,
				$actualTime,
				$ratio,
				$expectedHardness,
				$computedHardness);
			$event->getPlayer()->sendMessage($message);
		}
	}
}
