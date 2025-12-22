<?php

namespace App\Models;

use App\Lib\Model;

class Image extends Model
{
    protected static $table_name = "images";
    protected $id = 0;
    protected $item_id;
    protected $name;

    /**
     * @param $item_id
     * @param $name
     */
    public function __construct($item_id, $name)
    {
        $this->item_id = $item_id;
        $this->name = $name;
    }

    public static $errorArray = [
        'nophoto' => 'You did not select a photo to upload.',
        'exists' => 'A photo with the same name already exists',
        'large' => 'The photo you selected is too large',
        'invalid' => 'The photo you selected is not a valid image file'
    ];


}