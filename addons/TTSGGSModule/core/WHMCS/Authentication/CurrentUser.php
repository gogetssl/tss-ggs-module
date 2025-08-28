<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Authentication;

use \WHMCS\Authentication\CurrentUser as CurrentUserWhmcs;

/**
 * @method static isAuthenticatedUser ():bool
 * @method static isAuthenticatedAdmin ():bool
 * @method static isMasqueradingAdmin ():bool
 * @method static admin ():bool
 * @method static user ():bool
 * @method static client ():bool
 */
class CurrentUser extends CurrentUserWhmcs
{
    public function isCurrentLoggedAdmin(?int $adminId):bool
    {
        return $adminId == $this->admin()->id;
    }
}