<?php

namespace ModulesGarden\TTSGGSModule\App\Models;

use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;

/**
 * Description of Configuration
 *
 * @var varchar(255) setting
 * @var text value
 */
class Configuration extends ExtendedEloquentModel
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
    protected $table = 'Configruation';
    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['vendor', 'credentials', 'products', 'date'];

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $primaryKey = 'id';
}
