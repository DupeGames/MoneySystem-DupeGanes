<?php
/**
 * Created by PhpStorm.
 * User: masterocding
 * Date: 28.02.19
 * Time: 16:15
 */
namespace Mastercoding\Money\Commands;

use Mastercoding\Money\Main;
use Mastercoding\Money\Task\mysqlTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class GiveMoneyCommand extends Command {

    public function __construct(string $name, string $description = '', string $usageMessage = null, $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
       if ($sender->hasPermission("give.money.perm")){
           if (!empty($args[0])) {
               $player = Server::getInstance()->getPlayer($args[0]);
               if (!empty($args[1])) {
                   if (is_numeric($args[1])) {
                       $geld = round($args[1]);
                       if ($geld > 0){
                            if ($player !== null){
                                Main::getInstance()->addMoney($player->getName(), $geld);
                                $sender->sendMessage(Main::PREFIX . "§7Du hast §2{$player->getName()} §e{$geld}§2$ §7gegeben");
                            }else{
                                $sender->sendMessage(Main::PREFIX . "§7Dieser Spieler ist gerade nicht online.");
                                /*Server::getInstance()->getAsyncPool()->submitTask(new mysqlTask("SELECT * FROM geld WHERE playername = '$args[0]'", function (\mysqli_result $result, string $extra){
                                    if (mysqli_num_rows($result) <= 0) {
                                        return false;
                                    }else{
                                        return true;
                                    }
                                }, function ($money, string $extra){

                                }, $args[0]));*/
                            }
                       }else{
                           $sender->sendMessage(Main::PREFIX . "§7Die Zahl muss größer als §e0§2$ §7betragen");
                       }
                   }else{
                     $sender->sendMessage(Main::PREFIX . "§7Bitte gebe eine Zahl");
                   }
               }else{
                   $sender->sendMessage(Main::PREFIX . "§2/givemoney §aname money");
               }
           }else{
               $sender->sendMessage(Main::PREFIX . "§2givemoney §aname money");
           }
       }else{
           $sender->sendMessage(Main::PREFIX . "§7Du hast keine Rechte für diesen Command.");
       }
    }
}