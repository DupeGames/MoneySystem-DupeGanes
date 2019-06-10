<?php
/**
 * Created by PhpStorm.
 * User: masterocding
 * Date: 27.02.19
 * Time: 15:09
 */

namespace Mastercoding\Money\Task;

use Mastercoding\Money\db;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class mysqlTask extends AsyncTask {

    protected $query;
    protected $runnintask;
    protected $runnouttask;
    protected $extra;

    public function __construct(string $query, ?\Closure $runnintask = null, ?\Closure $runnouttask = null, string $extra = "")
    {
        $this->query = $query;
        $this->runnintask = $runnintask;
        $this->runnouttask = $runnouttask;
        $this->extra = $extra;
    }

    public function onRun()
    {
        $db = new db();
        $con = $db->connect();
        $result = $con->query($this->query);
        $runn = $this->runnintask;
        if ($runn !== null){
            $this->setResult($runn($result, $this->extra));
        }else{
            $this->setResult($result);
        }
    }

    public function onCompletion(Server $server)
    {
        if ($this->runnouttask !== null){
            $runn = $this->runnouttask;
            $runn($this->getResult(), $this->extra);
        }
    }
}