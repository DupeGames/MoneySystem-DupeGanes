<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.11.2018
 * Time: 15:27
 */

namespace Mastercoding\Money;


class db {
    const HOST = "Ip";
    const USER = "user";
    const PASS = "password";
    const DB   = "database";

    public function connect():\mysqli {
        return mysqli_connect(self::HOST, self::USER, self::PASS, self::DB);
    }
}