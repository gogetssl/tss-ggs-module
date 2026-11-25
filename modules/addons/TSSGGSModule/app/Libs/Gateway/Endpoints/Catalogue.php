<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Gateway\Endpoints;

use ModulesGarden\TSSGGSModule\App\Libs\Gateway\GatewayException;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Models\Request;

/**
 * Account and catalogue: profile, products, credentials check, account manager, announcements.
 * Part of SSLTrustCenterApi (used there as a trait): $this is the API client with its credentials and category.
 */
trait Catalogue
{
    /**
     * GET /profile: the reseller account - status (null for TheSSLStore), organization / contact / address and
     * billing {currency, balance, terms prepaid|credit|invoice, credit_limit, plan}. Used by App\Libs\BalanceMonitor.
     */
    public function getProfile()
    {
        return $this->call('/profile', [], 'GET');
    }

    public function getProducts()
    {
        return $this->call('/products');
    }

    /**
     * Connection and credential check: GET /authentication/validate (204, no body) - a fraction of the product list's
     * time and size. Throws like any call when the credentials are refused. An API without that call (404) is checked
     * with the product list instead.
     */
    public function validate()
    {
        try
        {
            $this->call('/authentication/validate', [], 'GET');
        }
        catch(GatewayException $e)
        {
            if($e->getHttpCode() !== 404)
            {
                throw $e;
            }
            $this->getProducts();
        }

        return true;
    }

    public function getProduct($id)
    {
        return $this->call('/products/' . $id);
    }

    public function getManager($countryCode, $forceAPI = false)
    {
        if($forceAPI === false)
        {
            $request = Request::where('serviceid', '0')->where('name', 'manager')->first();

            if(isset($request->request) && !empty($request->request))
            {
                return json_decode(\decrypt($request->request), true);
            }
            else
            {
                $response = $this->call('/profile/manager?country=' . $countryCode);

                $request = Request::where('serviceid', '0')->where('name', 'manager')->first();

                if(!isset($request->id))
                {
                    Request::insert([
                                        'serviceid'  => '0',
                                        'name'       => 'manager',
                                        'request'    => \encrypt(json_encode($response)),
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                }
                else
                {
                    Request::where('id', $request->id)->update([
                                                                   'request'    => \encrypt(json_encode($response)),
                                                                   'updated_at' => date('Y-m-d H:i:s')
                                                               ]);
                }

                return $response;
            }

        }
        else
        {
            $response = $this->call('/profile/manager?country=' . $countryCode);

            $request = Request::where('serviceid', '0')->where('name', 'manager')->first();

            if(!isset($request->id))
            {
                Request::insert([
                                    'serviceid'  => '0',
                                    'name'       => 'manager',
                                    'request'    => \encrypt(json_encode($response)),
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
            }
            else
            {
                Request::where('id', $request->id)->update([
                                                               'request'    => \encrypt(json_encode($response)),
                                                               'updated_at' => date('Y-m-d H:i:s')
                                                           ]);
            }

            return $response;
        }
    }

    public function getAnnouncements($forceAPI = false)
    {
        if($forceAPI === false)
        {
            $request = Request::where('serviceid', '0')->where('name', 'announcements')->first();

            if(isset($request->request) && !empty($request->request))
            {
                return json_decode(\decrypt($request->request), true);
            }
            else
            {
                $response = $this->call('/dashboard/announcements');

                $request = Request::where('serviceid', '0')->where('name', 'announcements')->first();

                if(!isset($request->id))
                {
                    Request::insert([
                                        'serviceid'  => '0',
                                        'name'       => 'announcements',
                                        'request'    => \encrypt(json_encode($response)),
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                }
                else
                {
                    Request::where('id', $request->id)->update([
                                                                   'request'    => \encrypt(json_encode($response)),
                                                                   'updated_at' => date('Y-m-d H:i:s')
                                                               ]);
                }

                return $response;
            }

        }
        else
        {
            $response = $this->call('/dashboard/announcements');

            $request = Request::where('serviceid', '0')->where('name', 'announcements')->first();

            if(!isset($request->id))
            {
                Request::insert([
                                    'serviceid'  => '0',
                                    'name'       => 'announcements',
                                    'request'    => \encrypt(json_encode($response)),
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
            }
            else
            {
                Request::where('id', $request->id)->update([
                                                               'request'    => \encrypt(json_encode($response)),
                                                               'updated_at' => date('Y-m-d H:i:s')
                                                           ]);
            }

            return $response;
        }
    }

    /** a product id as the gateway expects it: a number (GoGetSSL) or a product code (TheSSLStore) */
    public static function productId($id)
    {
        $id = trim((string)$id);

        return ctype_digit($id) ? (int)$id : $id;
    }
}
