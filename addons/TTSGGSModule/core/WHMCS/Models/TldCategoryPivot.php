<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of TldCategoryPivot
 */
class TldCategoryPivot extends EloquentModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['tld_id', 'category_id'];

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
    protected $table = 'tbltld_category_pivot';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function category()
    {
        return $this->hasOne('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\TldCategory', 'tld_id');
    }

    public function tld()
    {
        return $this->hasOne('ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Tld', 'tld_id');
    }
}
