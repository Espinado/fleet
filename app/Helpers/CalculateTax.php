<?php

namespace App\Helpers;

class CalculateTax
{
    /**
     * 🔹 Пересчитывает все налоговые данные для набора позиций (items).
     * Возвращает обновлённые позиции и суммы по грузу.
     */
    public static function forItems(array $items): array
    {
        $subtotal = 0;
        $totalTax = 0;
        $totalWithTax = 0;

        foreach ($items as &$item) {
            $price = (float)($item['price'] ?? 0);
            $taxPercent = (float)($item['tax_percent'] ?? 0);
            $taxAmount = round($price * $taxPercent / 100, 2);
            $priceWithTax = round($price + $taxAmount, 2);

            $item['tax_amount'] = $taxAmount;
            $item['price_with_tax'] = $priceWithTax;

            $subtotal += $price;
            $totalTax += $taxAmount;
            $totalWithTax += $priceWithTax;
        }

        return [
            'items'            => $items,
            'subtotal'         => round($subtotal, 2),
            'total_tax_amount' => round($totalTax, 2),
            'price_with_tax'   => round($totalWithTax, 2),
        ];
    }

    /**
     * 🔹 Считает общие итоги для всех грузов (cargos).
     * Работает с коллекцией моделей TripCargo.
     */
    public static function forCargos($cargos): array
    {
        $subtotal = 0;
        $totalTax = 0;
        $totalWithTax = 0;

        foreach ($cargos as $cargo) {
            $subtotal += (float)($cargo->price ?? 0);
            $totalTax += (float)($cargo->total_tax_amount ?? 0);
            $totalWithTax += (float)($cargo->price_with_tax ?? 0);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'vat'      => round($totalTax, 2),
            'total'    => round($totalWithTax, 2),
        ];
    }

    /**
     * 🔹 Форматирует число как денежную сумму (например: 3 993.00)
     */
    public static function format($value): string
    {
        return number_format((float)$value, 2, '.', ' ');
    }
}
