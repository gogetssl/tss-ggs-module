<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of TldCategory
 */
class TldCategory extends EloquentModel
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
    protected $fillable = ['category', 'is_primary', 'display_order'];

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
    protected $table = 'tbltld_categories';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
