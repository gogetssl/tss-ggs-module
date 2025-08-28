<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of Product
 * @deprecated - use ServiceConfigOption instead
 */
class HostingConfigOption extends EloquentModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = [];

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = ['id'];
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblhostingconfigoptions';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function configOption()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOption", 'configid');
    }

    public function subOption()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ProductConfigOption", 'optionid');
    }
}
