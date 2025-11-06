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
        $totalTaxAmount = 0;
        $priceWithTax = 0;

        foreach ($items as &$item) {
            $priceWithTaxItem = (float)($item['price_with_tax'] ?? 0);
            $taxPercent = (float)($item['tax_percent'] ?? 0);

            if ($taxPercent > 0) {
                $itemPrice = $priceWithTaxItem / (1 + $taxPercent / 100);
                $taxAmount = $priceWithTaxItem - $itemPrice;
            } else {
                $itemPrice = $priceWithTaxItem;
                $taxAmount = 0;
            }

            $subtotal += $itemPrice;
            $totalTaxAmount += $taxAmount;
            $priceWithTax += $priceWithTaxItem;

            $item['price'] = round($itemPrice, 2);
            $item['tax_amount'] = round($taxAmount, 2);
            $item['price_with_tax'] = round($priceWithTaxItem, 2);
        }

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'total_tax_amount' => round($totalTaxAmount, 2),
            'price_with_tax' => round($priceWithTax, 2),
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
