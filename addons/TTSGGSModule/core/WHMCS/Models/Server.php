<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Servers
 * /**
 * @property int $id
 * @property string $name
 * @property string $ipaddress
 * @property string $assignedips
 * @property string $hostname
 * @property float $monthlycost
 * @property string $noc
 * @property string $statusaddress
 * @property string $nameserver1
 * @property string $nameserver1ip
 * @property string $nameserver2
 * @property string $nameserver2ip
 * @property string $nameserver3
 * @property string $nameserver3ip
 * @property string $nameserver4
 * @property string $nameserver4ip
 * @property string $nameserver5
 * @property string $nameserver5ip
 * @property int $maxaccounts
 * @property string $type
 * @property string $username
 * @property string $password
 * @property string $accesshash
 * @property string $secure
 * @property int $port
 * @property int $active
 * @property int $disabled
 */
class Server extends \WHMCS\Product\Server
{
    public function groups()
    {
        return $this->belongsToMany(ServersGroups::class, "tblservergroupsrel", "serverid", "groupid");
    }

    public function services()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service", "server");
    }

    public function addons()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ServiceAddon", "server");
    }

    public function serverRelationPivot()
    {
        return $this->hasMany(ServersRelations::class, "serverid");
    }
}
