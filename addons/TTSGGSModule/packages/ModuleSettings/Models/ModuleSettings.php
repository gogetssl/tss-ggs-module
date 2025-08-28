<?php

namespace ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models;

use ModulesGarden\TTSGGSModule\Core\Models\ExtendedEloquentModel;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of ModuleSettings
 *
 * @var varchar(255) setting
 * @var text value
 */
class ModuleSettings extends ExtendedEloquentModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    public $incrementing = false;
    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['setting', 'value'];

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $primaryKey = 'setting';

    protected $casts = [
        'value' => 'array'
    ];

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ModuleSettings';

    public function scopeOfSetting($query, $setting)
    {
        return $query->where('setting', $setting);
    }

    public function scopeOfSettingBinary($query, $setting)
    {
        return $query->where(DB::raw('BINARY `setting`'), $setting);
    }

    public function hasDuplicatedSetting($setting):bool
    {
        return !$this->ofSettingBinary($setting)->first()->exists &&
               $this->ofSetting($setting)->first()->exists;
    }
}
