<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public const GROUP_GENERAL = 'general';

    public const GROUP_RECEIPT = 'receipt';

    public const GROUP_PRICING = 'pricing';

    public const GROUP_FEATURES = 'features';

    public const GROUP_PAYMENT = 'payment';

    protected $fillable = ['key', 'value', 'label', 'group', 'is_encrypted'];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }
}
