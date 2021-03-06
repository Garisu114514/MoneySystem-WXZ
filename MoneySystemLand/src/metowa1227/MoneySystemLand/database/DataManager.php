<?php

/*
* __  __       _                             __    ___    ___   _______
*|  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
*| |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
*| |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
*|_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
*All this program is made by hand of metowa 1227.
*I certify here that all authorities are in metowa 1227.
*Expiration date of certification: unlimited
*Secondary distribution etc are prohibited.
*The update is also done by the developer.
*This plugin is a developer API plugin to make it easier to write code.
*When using this plug-in, be sure to specify it somewhere.
*Warning if violation is confirmed.
*
*Developer: metowa 1227
*Development Team: metowa 1227 Plugin Development Team (Members: metowa 1227 only)
*/

/*
    PluginIntrodtion
    - CONTENTS
        - MoneySystemLand's database manager
    - AUTHOR
        - metowa1227 (MoneySystemAPI)
    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - 1.7dev-1001「[REDACTED]」Minecraft PE v1.4.0用実装APIバージョン3.0.0-ALPHA12(プロトコルバージョン261)
        - PHP 7.2.1 64bit supported version
        - MoneySystemAPI (SYSTEM) version 12.11 package version 12.00 API version 11.1 GREEN PAPAYA OX4 Edition (Released date: 2018/06/13)
*/

namespace metowa1227\MoneySystemLand\database;

use metowa1227\MoneySystemLand\MoneySystemLand;

use pocketmine\{ Player, Server };
use pocketmine\utils\{ Config, TextFormat };

class DataManager
{
    public function __construct(MoneySystemLand $main)
    {
        $this->main = $main;
    }

    public function here($startX, $endX, $startZ, $endZ, $level)
    {
        foreach ($this->main->land->getAll() as $land) {
            if ($level === $land["level"]) {
                if ($startX < $land["endX"] and $endX > $land["startX"]
                and $endZ > $land["startZ"] and $startZ < $land["endZ"]) {
                    return $land;
                }
            }
        }
        return false;
    }

    public function here2($x, $z, $level)
    {
        foreach ($this->main->land->getAll() as $land) {
            if ($level === $land["level"] and $land["startX"] <= $x and $land["endX"] >= $x and $land["startZ"] <= $z and $land["endZ"] >= $z) {
                return $land;
            }
        }
        return false;
    }

    public function getLands($name)
    {
        $return = [];
        foreach ($this->main->land->getAll() as $land) {
            if ($land["owner"] === $name) {
                $return[] = $land;
            }
        }
        return $return;
    }

    public function getAll()
    {
        return $this->main->land->getAll();
    }

    public function getAllIds()
    {
        return $this->main->land->getAll(true);
    }

    public function getLandById(int $id)
    {
        if ($this->main->land->exists($id)) {
            return $this->main->land->get($id);
        } else {
            return null;
        }
    }

    public function giveLand(int $id, $target, $player, bool $invite = false)
    {
        $land = $this->main->land->getAll();
        $land[$id]["owner"] = $target;
        if ($invite) {
            $land[$id]["invitee"][$player] = true;
        }
        $this->main->land->setAll($land);
        $this->main->land->save();
    }

    public function addInviteById(int $id, $player)
    {
        $land = $this->main->land->getAll();
        $land[$id]["invitee"][$player] = true;
        $this->main->land->setAll($land);
        $this->main->land->save();
    }

    public function removeInviteById(int $id, $player)
    {
        $land = $this->main->land->getAll();
        unset($land[$id]["invitee"][$player]);
        $this->main->land->setAll($land);
        $this->main->land->save();
    }

    public function isOwner($player, $id) : bool
    {
        if (!$this->main->land->exists($id)) {
            return false;
        }
        $land = $this->main->land->get($id);
        if ($player === $land["owner"]) {
            return true;
        } else {
            return false;
        }
    }

    public function addLand($startX, $endX, $startZ, $endZ, $level, $price, $owner) : bool
    {
        $this->main->land->set(
            $this->main->config->get("id"), [
                "ID" => $this->main->config->get("id"),
                "startX" => $startX,
                "endX" => $endX,
                "startZ" => $startZ,
                "endZ" => $endZ,
                "price" => $price,
                "owner" => $owner,
                "level" => $level,
                "invitee" => []
            ]
        );
        $this->main->land->save();
        $id = $this->main->config->get("id");
        $id = ++$id;
        $this->main->config->set("id", $id);
        $this->main->config->save();
        $counter = $this->main->config->get("counter");
        $counter = ++$counter;
        $this->main->config->set("counter", $counter);
        $this->main->config->save();
        return true;
    }

    public function getCounter()
    {
        return $this->main->config->get("counter");
    }

    public function isProtected($x, $z, $level, Player $player)
    {
        foreach ($this->main->land->getAll() as $land) {
            if ($level === $land["level"] and $land["startX"] <= $x and $land["endX"] >= $x and $land["startZ"] <= $z and $land["endZ"] >= $z) {
                if ($player->getName() === $land["owner"] or isset($land["invitee"][$player->getName()])) {
                    return true;
                } else {
                    return $land;
                }
            }
        }
        return false;
    }

    public function sellLandById(int $id) : bool
    {
        if (!$this->main->land->exists($id)) {
            return false;
        } else {
            $this->main->land->remove($id);
            $this->main->land->save();
            return true;
        }
    }
}
