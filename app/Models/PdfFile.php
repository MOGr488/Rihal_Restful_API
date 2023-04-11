<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'page_count',
        'size',
    ];


    
    public function sentences(){
        return $this->hasMany(PdfSentence::class);
    }
}
