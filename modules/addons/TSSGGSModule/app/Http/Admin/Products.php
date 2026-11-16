<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Libs\Edition;

use ModulesGarden\TSSGGSModule\App\Libs\CsrfToken;
use ModulesGarden\TSSGGSModule\App\Libs\ProductCatalog;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\ProductsContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Helper;
use WHMCS\Database\Capsule;

/**
 * Products page and the per-product lifecycle actions (links with the admin token)
 */
class Products extends AbstractController implements AdminAreaInterface
{
    const SESSION = 'tssggs_products';

    public function index()
    {
        return Helper\view()
            ->addElement(ProductsContainer::class);
    }

    public function hide()
    {
        return $this->run('hidden', function($pid) {
            ProductCatalog::setHidden($pid, true);
        });
    }

    public function show()
    {
        return $this->run('shown', function($pid) {
            ProductCatalog::setHidden($pid, false);
        });
    }

    public function retire()
    {
        return $this->run('retired', function($pid) {
            ProductCatalog::setRetired($pid, true);
        });
    }

    public function unretire()
    {
        return $this->run('unretired', function($pid) {
            ProductCatalog::setRetired($pid, false);
        });
    }

    public function fixOptions()
    {
        return $this->run('options_fixed', function($pid) {
            $product = Capsule::table('tblproducts')->where('id', $pid)->first(['configoption1', 'configoption2']);
            $remote  = $product ? Capsule::table(Edition::table('RemoteProducts'))->where('vendor', $product->configoption1)->where('remoteId', $product->configoption2)->value('rawData') : null;
            if(!$remote)
            {
                throw new \RuntimeException('The %%VENDOR%% product of this product is not synchronized');
            }

            return ProductCatalog::fixConfigurableOptions($pid, json_decode($remote, true) ?: []);
        });
    }

    public function cancelRenewals()
    {
        return $this->run('renewals_cancelled', function($pid) {
            return ProductCatalog::cancelAllRenewals($pid, 'Product discontinued');
        });
    }

    /**
     * Sells the product as a WHMCS product addon (M-14): the addon is created and opened in WHMCS, where the admin
     * picks the products it is offered with
     */
    public function sellAsAddon()
    {
        $addonId = 0;
        $this->run('addon_created', function($pid) use (&$addonId) {
            if(\ModulesGarden\TSSGGSModule\App\Libs\ProductAddons::addonsOf($pid))
            {
                throw new \RuntimeException('This product is already sold as a product addon');
            }
            $refused = \ModulesGarden\TSSGGSModule\App\Libs\ProductAddons::refusal($pid);
            if($refused !== '')
            {
                throw new \RuntimeException($refused);
            }
            $addonId = \ModulesGarden\TSSGGSModule\App\Libs\ProductAddons::create($pid);

            return '#' . $addonId;
        }, false);
        if($addonId)
        {
            // WHMCS's addon page opens next: the notice would only show up later, out of context
            unset($_SESSION[self::SESSION]['notice']);
            header('Location: configaddons.php?action=manage&id=' . $addonId);
            exit;
        }

        return Helper\redirect('products', 'index');
    }

    protected function run($notice, callable $action, bool $redirect = true)
    {
        try
        {
            if(!CsrfToken::valid($_REQUEST['token'] ?? ''))
            {
                throw new \RuntimeException('Invalid or expired security token. Reload the page and try again.');
            }
            $pid    = (int)($_REQUEST['pid'] ?? 0);
            $name   = (string)Capsule::table('tblproducts')->where('id', $pid)->where('servertype', 'TSSGGSModule')->value('name');
            if($name === '')
            {
                throw new \RuntimeException('Product not found');
            }
            $result = $action($pid);
            $_SESSION[self::SESSION]['notice'] = ['key' => $notice, 'product' => $name, 'detail' => is_array($result) ? implode(', ', $result) : (string)$result];
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION]['error'] = $e->getMessage();
        }

        return $redirect ? Helper\redirect('products', 'index') : null;
    }
}
