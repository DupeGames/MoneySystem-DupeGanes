<?php
/**
 * Created by PhpStorm.
 * User: masterocding
 * Date: 27.02.19
 * Time: 19:21
 */
namespace Mastercoding\Money\Commands;

use Mastercoding\Money\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MyMoneyCommand extends Command {
    public function __construct(string $name, string $description = "", string $usageMessage = null, $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            $money = Main::getInstance()->getMoney($sender->getName());
            $sender->sendMessage(Main::PREFIX . "§7Dein Kontostand lautet §e{$money}§2$");
        }
    }
}