<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

use ModulesGarden\TSSGGSModule\Core\Models\ExtendedEloquentModel;

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

    /**
     * The order request the service's current certificate was ordered with: the latest renewal, else the first order.
     * A reissue updates this row, a renewal starts from it.
     */
    public static function currentOrder($serviceId)
    {
        $renewal = self::where('serviceid', $serviceId)->where('name', 'renewOrder')->orderBy('id', 'desc')->first();
        if($renewal && !empty($renewal->request))
        {
            return $renewal;
        }

        return self::where('serviceid', $serviceId)->where('name', 'addOrder')->first();
    }

    public static function updateOrderData($serviceId, $request)
    {
        self::where('serviceid', $serviceId)->where('name', 'certificate')->update([
            'request' => \encrypt(json_encode($request)),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
