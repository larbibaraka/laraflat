<?php

namespace App\Application\Model;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{

  public $table = "categories";

  public $timestamps = false;

   protected $fillable = [
        'name'
   ];

   public function validation($id){
        return [
            'name.*' => 'required|max:90'
        ];
   }

   public function updateValidation($id){
        return [
            'name.*' => 'required|max:90'
        ];
   }

}
