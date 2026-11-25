<?php

namespace ModulesGarden\TSSGGSModule\App\Models;

/** A value of App\Libs\Support\ModuleSettings (table <prefix>_ModuleSettings: setting, value as JSON) */
class ModuleSetting extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = ['setting', 'value'];
    protected $primaryKey = 'setting';
    protected $keyType = 'string';
    protected $casts = ['value' => 'array'];
    protected $table = 'ModuleSettings';
}
