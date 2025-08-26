<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ModelTrait
{
    /**
     * Boot function from Laravel.
     */
    protected static function bootModelTrait()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Initialize the trait for the model.
     */
    public function initializeModelTrait()
    {
        $this->guarded = ['id']; // Prevent mass-assignment of 'id'
        $this->setIncrementing(false); // Disable auto-incrementing
        $this->setKeyType('string');   // Set the key type to string (UUIDs are strings)
        $this->setPrimaryKey('uuid');  // Set primary key to 'uuid'
    }

    /**
     * Set the incrementing property.
     */
    public function setIncrementing($value)
    {
        $this->incrementing = $value;
    }

    /**
     * Set the keyType property.
     */
    public function setKeyType($value)
    {
        $this->keyType = $value;
    }

    /**
     * Set the primary key.
     */
    public function setPrimaryKey($value)
    {
        $this->primaryKey = $value;
    }
}
