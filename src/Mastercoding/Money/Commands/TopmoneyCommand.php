<?php
/**
 * Created by PhpStorm.
 * User: masterocding
 * Date: 27.02.19
 * Time: 14:16
 */
namespace Mastercoding\Money\Commands;

use Mastercoding\Money\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class TopmoneyCommand extends Command {
    public function __construct(string $name, string $description = '', string $usageMessage = null, $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        Main::getInstance()->getTopMoney($sender->getName());
    }
}