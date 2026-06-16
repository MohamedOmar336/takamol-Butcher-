<?php

namespace App\Services\Scale;

class ScalePayload
{
    public string $sku;
    public float $weight; // Weight in KG
    public float $price;  // Calculated or embedded price
    public bool $isValid;
    public ?string $error;

    public function __construct(string $sku, float $weight, float $price = 0.00, bool $isValid = true, ?string $error = null)
    {
        $this->sku = $sku;
        $this->weight = $weight;
        $this->price = $price;
        $this->isValid = $isValid;
        $this->error = $error;
    }
}
