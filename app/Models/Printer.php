<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $branch_id
 * @property string $name
 * @property string $ip_address
 * @property int $port
 * @property int $timeout
 * @property int $character_table
 * @property string $character_encoding
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @method static \Database\Factories\PrinterFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCharacterEncoding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCharacterTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereTimeout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Printer extends Model
{
    /** @use HasFactory<\Database\Factories\PrinterFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'ip_address',
        'port',
        'timeout',
        'character_table',
        'character_encoding',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'timeout' => 'integer',
            'character_table' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
