<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of OrderStatus
 */
class OrderStatus extends EloquentModel
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
    protected $fillable = ['title', 'color', 'showpending', 'showactive', 'showcancelled', 'sortorder'];

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var string
     */
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
    protected $table = 'tblorderstatuses';

    /**
     * OrderStatus constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
