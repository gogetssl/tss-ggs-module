<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Credit extends EloquentModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date'];

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['clientid', 'date', 'description', 'amount', 'relid'];

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
    protected $table = 'tblcredit';
}
