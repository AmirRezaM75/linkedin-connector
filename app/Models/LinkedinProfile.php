<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property string $username
 * @property string $status
 */
class LinkedinProfile extends Model
{
    use HasFactory;

    protected $guarded = [];
}
