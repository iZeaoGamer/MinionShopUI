<?php
namespace MinionShop;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandMap};
use pocketmine\utils\Config;

use MinionShop\commands\MinionShopCommand;

class MinionShop extends PluginBase{

    public $config;

    public function onEnable(){
        foreach([
            "EconomyAPI" => "EconomyAPI",
            "Minion" => "Minion",
            "FormAPI" => "FormAPI"] as $plugins){
            if(!$this->getServer()->getPluginManager()->getPlugin($plugins)){
                $this->getLogger()->error("You have not installed the plugin ". $plugins.". Please install all 3 plugins: FormAPI, EconomyAPI, Minion for the plugin to work smoothly.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }

        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->getServer()->getCommandMap()->register("minionshop", new MinionShopCommand("minionshop", $this));
    }
}
?>
