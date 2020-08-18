<?php

namespace MinionShop\commands;

use pocketmine\command\{Command, CommandSender, PluginIdentifiableCommand};
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\plugin\Plugin;

use onebone\economyapi\EconomyAPI;
use CLADevs\Minion\Main;
use jojoe77777\FormAPI\{FormAPI, SimpleForm, CustomForm}; // Form
use MinionShop\MinionShop;

class MinionShopCommand extends Command{
    public $plugin;
    public function __construct($name, Plugin $plugin){
    $this->plugin = $plugin;
    parent::__construct("minionshop");
    $this->setDescription("MinionShop command");
    //$this->setPermission("minionshop.command");
    $this->setAliases(["ms"]);
    }
    public function getPlugin(): Plugin{
        return $this->plugin;
    }
    public function execute(CommandSender $sender, string $label, array $args){
        if($sender instanceof Player){
         $this->mainForm($sender);
          return true;
        } 
        else{
         $sender->sendMessage($this->plugin->config->get("prefix"). $this->plugin->config->get("error-consoleSender"));
        }
          return true;
    }
    /* MAIN FORM */
    public function mainForm(Player $player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                return true;
            }
            switch($data){
                case 0:
                    $this->buyMinionForm($player);
                break;
                case 1:
                    $this->sellMinionForm($player);
                break;
                case 2:
                    $this->priceListForm($player);
                break;
            }
        });
        $form->setTitle($this->plugin->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->setContent(Textformat::colorize("&5&lAccount: &6&l" . $player->getName() . "&8| &5Current Balance: &6$". number_format($money)));
        $form->addButton("§l§2BUY MINION\n§7[CLICK TO BUY]");
        $form->addButton("§l§4MINION SALE\n§7[CLICK TO SELL]");
        $form->addButton("§l§eMINION PRICE LIST\n§7[CLICK TO VIEW]");
        $form->sendToPlayer($player);
        return $form;
    }
    /* BUY FORM */
    public function buyMinionForm(Player $player){
        $form = new CustomForm(function (Player $player, ?array $data){
        if($data[1] === null){
            $this->mainForm($player);
            return true;
        }
        $prices = $this->plugin->config->get("price") * $data[1];
        $money = EconomyAPI::getInstance()->myMoney($player);
        if($money < $prices){
            $player->sendMessage($this->plugin->config->get("prefix"). $this->plugin->config->get("error-notEnoughMoney"));
        }else{
            EconomyAPI::getInstance()->reduceMoney($player, $prices);
            $i = 0;
            while($i < $data[1]){
            $i++;
            $this->plugin->getServer()->getPluginManager()->getPlugin("Minion")->giveItem($player);
            }
            $amount = str_replace("{amount}", $data[1], $this->plugin->config->get("success-boughtMinion"));
            $buy = str_replace("{buy}", $prices, $this->plugin->config->get("success-deductionMoney"));
            $player->sendMessage($this->plugin->config->get("prefix"). $amount);
            $player->sendMessage($this->plugin->config->get("prefix"). $buy);
        }
    });
        $form->setTitle($this->plugin->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->addLabel(Textformat::colorize("&5&lAccount: &6&l" . $player->getName() . "&8| &5Current Balance: &6$". number_format($money)));
        $form->addSlider("amount", 1, $this->plugin->config->get("amountBuyMinion"), 1);
        $form->sendToPlayer($player);
        return $form;
    }
    /* SELL FORM */
    public function sellMinionForm(Player $player){
        $form = new CustomForm(function (Player $player, ?array $data){
        if($data[1] === null){
            $this->mainForm($player);
            return true;
        }
        if($player->getInventory()->contains(Item::get(399, 0, $data[1]))){
        $i = 0;
        while($i < $data[1]){
        $player->getInventory()->removeItem(Item::get(399, 0, 1));
        $i++;
        }
        $money = $this->plugin->config->get("sell") * $data[1];
        $this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($player, $money);
        $amount = str_replace("{amount}", $data[1], $this->plugin->config->get("success-sellMinion"));
        $sell = str_replace(["{sell}", "{player}"], [$money, $player->getName()], $this->plugin->config->get("success-addMoney"));
        $player->sendMessage($this->plugin->config->get("prefix"). $amount);
        $player->sendMessage($this->plugin->config->get("prefix"). $sell);
        }else{
        $player->sendMessage($this->plugin->config->get("prefix"). $this->plugin->config->get("error-notFoundMinion"));
        }
        return true;
    });
        $form->setTitle($this->plugin->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->addLabel(Textformat::colorize("&5&lAccount: &6&l" . $player->getName() . "&8| &5Current Balance: &6$". number_format($money)));
        $form->addSlider("amount", 1, $this->plugin->config->get("amountSellMinion"), 1);
        $form->sendToPlayer($player);
        return $form;
    }
    /* PRICE FORM */
    public function priceListForm(Player $player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                $this->mainForm($player);
                return true;
            }
            switch($data){
                case 0:
                    $this->mainForm($player);
                break;
            }
        });
        $form->setTitle($this->plugin->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->setContent("§l§5Account: §6". $player->getName() ." §8| §5Current Balance:  §6$". number_format($money) ."\n§l§7> §2Purchase price Minion: §e". $this->plugin->config->get("price") ."$ / 1 child \n§l§7> §2Price of Minion §6". $this->plugin->config->get("sell") ."$ / 1 child");
        $form->addButton("§l§cBACK. \n[CLICK TO BACK]");
        $form->sendToPlayer($player);
        return $form;
    }
}
