<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Providers;

use Exception;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\WHMCS\API\API;
use WHMCS\User\Client;

class UserProvider extends CrudProvider
{
    public function create()
    {
        API::run('AddClient', [
            'firstname' => $this->formData['firstname'],
            'lastname'  => $this->formData['lastname'],
            'address1'  => $this->formData['address1'],
            'email'     => $this->formData['email'],
            'city'      => $this->formData['city'],
            'state'     => $this->formData['state'],
            'postcode'  => $this->formData['postcode'],
        ]);
    }

    public function delete()
    {
//        echo '<pre>';
//        print_r("test FROM PROVIDER ");
//        exit();
//        throw new Exception('Delete option is disabled');
    }

    public function read()
    {
        parent::read();
    }

    public function update()
    {
        Client::where('id', $this->formData['id'])
            ->update([
                'firstname' => $this->formData['firstname'],
                'lastname'  => $this->formData['lastname'],
                'address1'  => $this->formData['address1'],
                'email'     => $this->formData['email'],
            ]);
    }
}
