<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Daylog
 *
 * @package App\Models
 * @property boolean $isComplete
 */
class Daylog extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daylog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_alcohol'            => 'boolean',
        'has_alcohol_in_evening' => 'boolean',
        'has_smoked'             => 'boolean',
        'wake_at'                => 'datetime',
        'first_meal_at'          => 'datetime',
        'last_meal_at'           => 'datetime',
        'sleep_at'               => 'datetime',
        'log_date'               => 'date',
    ];

    /**
     * @return bool
     */
    public function getIsCompleteAttribute()
    {
        return $this->log_date &&
            $this->sleep_at !== null &&
            $this->last_meal_at !== null &&
            $this->first_meal_at !== null &&
            $this->wake_at !== null &&
            $this->has_smoked !== null &&
            $this->has_alcohol_in_evening !== null &&
            $this->has_alcohol !== null;
    }
}
