<?php
/**
 * Created by PhpStorm.
 * User: masterocding
 * Date: 27.02.19
 * Time: 19:26
 */
namespace Mastercoding\Money\Commands;

use Mastercoding\Money\Main;
use Mastercoding\Money\Task\mysqlTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class PayMoneyCommand extends Command
{

    public function __construct(string $name, string $description = "", string $usageMessage = null, $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if (!empty($args[0])) {
                if ($args[0] !== "*") {
                    $player = Server::getInstance()->getPlayer($args[0]);
                    if ($player !== null) {
                        if (!empty($args[1])) {
                            $moneysender = Main::getInstance()->getMoney($sender->getName());
                            if (is_numeric($args[1])) {
                                if (round($args[1], 0) > 0) {
                                    if ($moneysender > round($args[1])) {
                                        $money = round($args[1]);
                                        Main::getInstance()->reduceMoney($sender->getName(), $money);
                                        Main::getInstance()->addMoney($player->getName(), $money);
                                        $sender->sendMessage(Main::PREFIX . "§7Du hast §2{$player->getDisplayName()}§7 §e{$money}§2$ §7gegeben.");
                                        $player->sendMessage(Main::PREFIX . "§7Du hast von §2{$sender->getDisplayName()} §e{$money}§2$ §7bekommen.");
                                    } else {
                                        $money = round($args[1], 0) - $moneysender;
                                        $sender->sendMessage(Main::PREFIX . "§7Du brauchst mindestens §e{$money}§2$");
                                    }
                                } else {
                                    $sender->sendMessage(Main::PREFIX . "§7Die Zahl muss größer als §l0 §r§7betragen.");
                                }
                            } else {
                                $sender->sendMessage(Main::PREFIX . "§7Bitte gebe ein Zahl ein.");
                            }
                        } else {
                            $sender->sendMessage(Main::PREFIX . "§2/pay §aname money");
                        }
                    } else {
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
                } else {
                    if (!empty($args[1])) {
                        if (is_numeric($args[1])) {
                            if (round($args[1], 0) > 0) {
                                if (count(Server::getInstance()->getOnlinePlayers()) > 1) {
                                    $preis = round($args[1], 0) * count(Server::getInstance()->getOnlinePlayers() - 1);
                                    $moneysender = Main::getInstance()->getMoney($sender->getName());
                                    if ($moneysender >= $preis) {
                                        Main::getInstance()->reduceMoney($sender->getName(), $preis);
                                        $money = round($args[1]);
                                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                                            if ($player !== $sender) {
                                                Main::getInstance()->addMoney($player->getName(), $money);
                                                $player->sendMessage(Main::PREFIX . "§7Du hast von §2{$sender->getDisplayName()} §e{$money}§2$ §7bekommen.");
                                            }
                                            $sender->sendMessage(Main::PREFIX . "§7Du hast §2{$player->getDisplayName()} §e{$money}§2$ §7gegeben.");
                                        }
                                    } else {
                                        $money = round($preis, 0) - $moneysender;
                                        $sender->sendMessage(Main::PREFIX . "§7Du brauchst mindestens §e{$money}§2$");
                                    }
                                } else {
                                    $sender->sendMessage(Main::PREFIX . "§7Es müssen mindestens §e2 §7Spieler online sein.");
                                }
                            } else {
                                $sender->sendMessage(Main::PREFIX . "§7Die Zahl muss größer als §l0 §r§7betragen.");
                            }
                        } else {
                            $sender->sendMessage(Main::PREFIX . "§7Bitte gebe ein Zahl ein.");
                        }
                    } else {
                        $sender->sendMessage(Main::PREFIX . "§2/pay §a* money");
                    }
                }
            } else {
                $sender->sendMessage(Main::PREFIX . "§2/pay §aname §7money");
            }
        }
    }
}