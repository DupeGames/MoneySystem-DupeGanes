<?php

namespace Mastercoding\Money;

#Main
use Mastercoding\Money\Commands\GiveMoneyCommand;
use Mastercoding\Money\Commands\MyMoneyCommand;
use Mastercoding\Money\Commands\PayMoneyCommand;
use Mastercoding\Money\Commands\setMoneyCommand;
use Mastercoding\Money\Commands\TopmoneyCommand;
use Mastercoding\Money\Task\mysqlTask;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;


#Player
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerExhaustEvent;


#Command
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;

#Task
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\scheduler\TaskHandler;


class Main extends PluginBase implements Listener
{

    public const PREFIX = "§4Money§7§l|§r ";

    public static $instance;

    public static $money = [];

    public $config;

    public function onEnable()
    {
        $instance = $this;
        $this->getServer()->getPluginManager()->registerEvents($instance, $this);
        self::$instance = $instance;

        /*$db = new db();
        $con = $db->connect();
        foreach ($con->query("SELECT * FROM money") as $row) {
             var_dump($row);
             $con->query("INSERT IGNORE INTO geld (playername, Geld) VALUES ('{$row["Name"]}', '{$row["Money"]}')");
        }*/

        $mymoney = new MyMoneyCommand("mymoney", "Money Status");
        $this->getServer()->getCommandMap()->register("money", $mymoney);

        $pay = new PayMoneyCommand("pay", "Pay");
        $this->getServer()->getCommandMap()->register("pay", $pay);

        $give = new GiveMoneyCommand("givemoney", "Give money to player");
        $this->getServer()->getCommandMap()->register("givemoney", $give);

        $set = new setMoneyCommand("setmoney", "set players money");
        $this->getServer()->getCommandMap()->register("setmoney", $set);

        $top = new TopmoneyCommand("topmoney", "topmoney");
        $this->getServer()->getCommandMap()->register("topmoney", $top);

        $this->getServer()->getAsyncPool()->submitTask(new mysqlTask("CREATE TABLE IF NOT EXISTS `geld`(
            `playername` VARCHAR (15) PRIMARY KEY,
            `Geld` INT DEFAULT 1000
            ) ENGINE = InnoDB;
         "));
    }

    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        Main::$money[$name] = 0;

        $this->getServer()->getAsyncPool()->submitTask(new mysqlTask("SELECT * FROM geld WHERE playername = '$name'", function (\mysqli_result $result, string $extra){
            if (mysqli_num_rows($result) <= 0){
                $db = new db();
                $con = $db->connect();
                $con->query("INSERT INTO geld (playername, Geld) VALUES ('$extra', 1000)");
                return false;
            }else{
                return mysqli_fetch_array($result)["Geld"];
            }
        }, function ($money, string $extra){
            Main::$money[$extra] = $money;
        }, $name));

        $this->addMoney($name, 12);
    }

    public function setMoney(string $name, int $money){
        if ($money > 0){
            $this->getServer()->getAsyncPool()->submitTask(new mysqlTask("UPDATE geld SET Geld = $money WHERE playername = '$name'"));
            Main::$money[$name] = $money;
        }
    }

    public function addMoney(string $name, int $money){
        if ($money > 0){
            $this->getServer()->getAsyncPool()->submitTask(new mysqlTask("UPDATE geld SET Geld = Geld + $money WHERE playername = '$name'"));
            if (isset(self::$money[$name])) {
                Main::$money[$name] += $money;
            }
        }
    }

    public function reduceMoney(string $name, int $money){
        if($money > 0){
            if ($this->getMoney($name) >= $money) {
                $this->getServer()->getAsyncPool()->submitTask(new mysqlTask("UPDATE geld SET Geld = Geld - $money WHERE playername = '$name'"));
                Main::$money[$name] -= $money;
            }
        }
    }

    public function getMoney(string $name){
        return Main::$money[$name];
    }

    public function getTopMoney(string $name) {
        $this->getServer()->getAsyncPool()->submitTask(new mysqlTask("SELECT * FROM geld", function (\mysqli_result $result, string $extra){

            while ($row = mysqli_fetch_assoc($result)) {
                $array[$row["playername"]] =  $row["Geld"];
            }

            #var_dump($array);

            arsort($array);
            $newarray = $array;
            $pos = 1;

            $topmoneyarray = [];
            foreach ($newarray as $wert => $info){
                if ($pos <= 10){
                    $topmoneyarray[$wert] = $info;
                    $pos++;
                }
            }
            return $topmoneyarray;
        }, function ($result, string $extra){
            $player = Server::getInstance()->getPlayerExact($extra);
            if($player !== null) {
                Main::sendTopMoney($player, $result);
            }
        }, $name));
    }

    public static function sendTopMoney(Player $player, array $result){

        $fdata = [];

        $fdata['title'] = "§7Topmoney Liste";
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = "form";

        $pos = 1;
        foreach ($result as $key => $info){
            $fdata["buttons"][] = ["text" => "$pos. §2$key §a{$info}"];
            $pos++;
        }

        $pk = new ModalFormRequestPacket();
        $pk->formId = 8946385;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
    }

    /**
     * @return mixed
     */
    public static function getInstance() : self
    {
        return self::$instance;
    }
}
