<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Addon
 *
 * @var int id
 * @var string uuid
 * @var int roleid
 * @var string username
 * @var string password
 * @var string passwordhash
 * @var string authmodule
 * @var string authdata
 * @var string firstname
 * @var string lastname
 * @var string email
 * @var string signature
 * @var string notes
 * @var string template
 * @var string language
 * @var int disabled
 * @var int loginattempts
 * @var string supportdepts
 * @var string ticketnotifications
 * @var string homewidgets
 * @var string password_reset_key
 * @var string password_reset_data
 * @var timestamp password_reset_expiry
 * @var timestamp created_at
 * @var timestamp updated_at
 */
class Admins extends \WHMCS\User\Admin
{
    public function flaggedTickets()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Ticket", "flag");
    }
}
