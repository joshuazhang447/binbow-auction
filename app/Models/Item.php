<?php

namespace App\Models;

use App\Lib\Model;
use App\Exceptions\ClassException;

class Item extends Model
{
    protected static $table_name = "items";
    protected $id = 0;
    protected $user_id;
    protected $cat_id;
    protected $name;
    protected $price;
    protected $description;
    protected $date;
    protected $notified = 0;

    protected $imageObj = null;
    protected $bidObjs = [];

    /**
     * @param $user_id
     * @param $cat_id
     * @param $name
     * @param $price
     * @param $description
     * @param $date
     */
    public function __construct($user_id, $cat_id, $name, $price, $description, $date)
    {
        $this->user_id = $user_id;
        $this->cat_id = $cat_id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->date = $date;
    }

    public function getImage() {
        try {
            $this->imageObj = Image::findFirst(
                ["item_id" => $this->id]
            );
        } catch (ClassException $e) {
            $this->imageObj = null;
        }
        return $this->imageObj;
    }

    public function getBids() {
        $this->bidObjs = Bid::find(
            ["item_id" => $this->id],
            null,
            "amount DESC"
        );

        return $this->bidObjs;
    }

    public static $errorArray = array(
        'lowprice' => 'The bid entered is too low. Please enter another price.',
        'letter' => 'The value entered is not a number.'
    );

}