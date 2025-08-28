<?php

namespace ModulesGarden\TTSGGSModule\App\Models;

use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;

class Request extends ExtendedEloquentModel
{
    public    $timestamps = true;
    protected $table      = 'Requests';
    protected $fillable   = ['invoiceid','serviceid', 'name', 'api_price', 'rate', 'whmcs_price', 'diff_price', 'request', 'status'];
    protected $primaryKey = 'id';

    public static function getOrder($serviceId)
    {
        $results = self::where('serviceid', $serviceId)->where('name', 'certificate')->first();
        return json_decode(\decrypt($results->request), true);
    }

    public static function updateOrderData($serviceId, $request)
    {
        self::where('serviceid', $serviceId)->where('name', 'certificate')->update([
            'request' => \encrypt(json_encode($request)),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
