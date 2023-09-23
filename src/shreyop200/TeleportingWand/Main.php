<?php

declare(strict_types=1);

namespace shreyop200\TeleportingWand;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\VoxelRayTrace;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class Main extends PluginBase implements Listener{
	private const TPWAND_NBT = 'tpwand';

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		StringToItemParser::getInstance()->register("tpwand", function() : Item{
		      	$stick = VanillaItems::STICK();
		      	$stick->setCustomName("§l§cTeleporter Wand");
            $lore = [
                "§r§bUnleash The Futuristic Teleportation Technology for Limitless Travels",
                "§aUse the Power of Teleporting Wand",
                "§a8AIM On Something And Right Click to Use!",
                "§l§dFUTURISTIC"
            ];
            $stick->setLore($lore);

			$stick->getNamedTag()->setByte(self::TPWAND_NBT, 1);
			return $stick;
		});
	}

	public function onBreakBlock(BlockBreakEvent $event) : void{
		if($event->getItem()->getNamedTag()->getTag(self::TPWAND_NBT) !== null){
			$event->cancel();
		}
	}

	public function onItemUse(PlayerItemUseEvent $event) : void{
		if($event->getItem()->getNamedTag()->getTag(self::TPWAND_NBT) !== null){
			$player = $event->getPlayer();
			if(!$player->hasPermission('tpwand.use')){
				$player->sendMessage(TextFormat::RED . 'You dont have permission to use me!');
				return;
			}
			$start = $player->getPosition()->add(0, $player->getEyeHeight(), 0);
			$end = $start->addVector($player->getDirectionVector()->multiply($player->getViewDistance() * 16));
			$world = $player->getWorld();

			foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
				if($vector3->y >= World::Y_MAX or $vector3->y <= 0){
					return;
				}

				if(!$world->isChunkLoaded($vector3->x >> Chunk::COORD_BIT_SIZE, $vector3->z >> Chunk::COORD_BIT_SIZE)){
					return;
				}

				if(($result = $world->getBlock($vector3)->calculateIntercept($start, $end)) !== null){
					$target = $result->hitVector;
					$player->teleport($target);
					return;
				}
			}
		}
	}
}
