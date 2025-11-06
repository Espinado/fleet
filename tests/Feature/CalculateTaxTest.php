<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Helpers\CalculateTax;
use PHPUnit\Framework\Attributes\Test;

class CalculateTaxTest extends TestCase
{
    #[Test]
    public function it_calculates_correct_values_for_21_percent_vat()
    {
        $items = [
            ['price_with_tax' => 121, 'tax_percent' => 21],
            ['price_with_tax' => 242, 'tax_percent' => 21],
        ];

        $result = CalculateTax::forItems($items);

        $this->assertEquals(300.0, $result['subtotal']);
        $this->assertEquals(63.0, $result['total_tax_amount']);
        $this->assertEquals(363.0, $result['price_with_tax']);
    }

    #[Test]
    public function it_calculates_correct_values_for_12_percent_vat()
    {
        $items = [
            ['price_with_tax' => 112, 'tax_percent' => 12],
            ['price_with_tax' => 224, 'tax_percent' => 12],
        ];

        $result = CalculateTax::forItems($items);

        $this->assertEquals(300.0, $result['subtotal']);
        $this->assertEquals(36.0, round($result['total_tax_amount'], 2));
        $this->assertEquals(336.0, $result['price_with_tax']);
    }

    #[Test]
    public function it_calculates_correct_values_for_zero_vat()
    {
        $items = [
            ['price_with_tax' => 100, 'tax_percent' => 0],
            ['price_with_tax' => 200, 'tax_percent' => 0],
        ];

        $result = CalculateTax::forItems($items);

        $this->assertEquals(300.0, $result['subtotal']);
        $this->assertEquals(0.0, $result['total_tax_amount']);
        $this->assertEquals(300.0, $result['price_with_tax']);
    }
}
