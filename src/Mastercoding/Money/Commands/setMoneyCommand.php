<?php
/**
 * Created by PhpStorm.
 * User: masterocding
 * Date: 28.02.19
 * Time: 16:34
 */
namespace Mastercoding\Money\Commands;

use Mastercoding\Money\Main;
use Mastercoding\Money\Task\mysqlTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class setMoneyCommand extends Command {

    public function __construct(string $name, string $description = "", string $usageMessage = null, $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
       if ($sender->hasPermission("set.money.perm")){
            if (!empty($args[0])){
                $player = Server::getInstance()->getPlayer($args[0]);
                if (!empty($args[1])){
                    if (is_numeric($args[1])){
                        $geld = round($args[1]);
                        if ($geld > 0){
                            if ($player !== null){
                                Main::getInstance()->setMoney($player->getName(), $geld);
                                $player->sendMessage(Main::PREFIX . "§7Dein Kontostand wurde von §2{$sender->getName()}§7 auf §e{$geld}§2$ §7gesetzt.");
                            }else{
                                if (!empty($args[1])){
                                $moneysender = Main::getInstance()->getMoney($sender->getName());
                                if (is_numeric($args[1])){
                                    if (round($args[1], 0) > 0) {
                                        if ($moneysender > round($args[1])) {
                                            $money = round($args[1]);
                                            Server::getInstance()->getAsyncPool()->submitTask(new mysqlTask("SELECT * FROM geld WHERE playername = '$args[0]'", function (\mysqli_result $result, string $extra) {
                                                if (mysqli_num_rows($result) <= 0) {
                                                    return false;
                                                } else {
                                                    return true;
                                                }
                                            }, function ($bool, string $extra) {
                                                if ($bool === true) {
                                                    Main::getInstance()->addMoney(explode(":", $extra)[1], explode(":", $extra)[2]);
                                                    Main::getInstance()->reduceMoney(explode(":", $extra)[0], explode(":", $extra)[2]);
                                                    $sender = Server::getInstance()->getPlayerExact(explode(":", $extra)[0]);
                                                    if ($sender !== null) {
                                                        $info = explode(":", $extra);
                                                        $sender->sendMessage(Main::PREFIX . "§7Du hast §2{$info[1]}§7 §e{$info[2]}§2$ §7gegeben.");
                                                        #$player->sendMessage(Main::PREFIX . "§7Du hast von §2{$sender->getDisplayName()} §e{$info[2]}§2$ §7bekommen.");
                                                    }
                                                } else {
                                                    $sender = Server::getInstance()->getPlayerExact(explode(":", $extra)[0]);
                                                    if ($sender !== null) {
                                                        $sender->sendMessage(Main::PREFIX . "§7Dieser Spieler ist nicht registriert");
                                                    }
                                                }
                                            }, $sender->getName() . ":" . $args[0] . ":" . $money));
                                        }
                                    }else{
                                        $money = round($args[1], 0) - $moneysender;
                                        $sender->sendMessage(Main::PREFIX . "§7Du brauchst mindestens §e{$money}§2$");
                                    }
                                }else{
                                    $sender->sendMessage(Main::PREFIX . "§7Bitte gebe ein Zahl ein.");
                                }
                            }else{
                                    $sender->sendMessage(Main::PREFIX . "§2/pay §aname money");
                                }
                            }
                        }else{
                            $sender->sendMessage(Main::PREFIX . "§7Die Zahl muss größer als §e0§2$ §7betragen");
                        }
                    }else{
                      $sender->sendMessage(Main::PREFIX . "§7Bitte gebe eine Zahl an.");
                    }
                }else{
                    $sender->sendMessage(Main::PREFIX . "§2/setmoney §aname money");
                }
            }else{
                $sender->sendMessage(Main::PREFIX . "§2/setmoney §aname money");
            }
       }else{
           $sender->sendMessage(Main::PREFIX . "§7Du hast keine Rechte für diesen Command");
       }
    }
}