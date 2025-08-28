<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of ServersGroups
 * @property int $id
 * @property string $name
 * @property int $filltype
 */
class ServersGroups extends \WHMCS\Product\Server\Group
{
    public function servers()
    {
        return $this->belongsToMany(Server::class, "tblservergroupsrel", "groupid", "serverid");
    }

    public function serverRelationPivot()
    {
        return $this->hasMany(ServersRelations::class, "groupid");
    }
}
