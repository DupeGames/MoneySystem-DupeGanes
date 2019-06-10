<?php
/**
 * Created by PhpStorm.
 * User: mastercoding
 * Date: 04.03.19
 * Time: 12:49
 */
namespace Mastercoding\Money\Task;

use Mastercoding\Money\Main;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class updateMoneyTask extends Task {

    public function onRun(int $currentTick)
    {
      foreach (Server::getInstance()->getOnlinePlayers() as $player){
         if (isset(Main::$money[$player->getName()])){
             $name = $player->getName();
             Server::getInstance()->getAsyncPool()->submitTask(new mysqlTask("SELECT * FROM money WHERE playername = '$name'", function (\mysqli_result $result, string $extra){
                return mysqli_fetch_array($result);
             }, function ($result, string $extra){
                Main::$money[$extra] = $result["Geld"];
             }, $name));
         }
      }
    }

}