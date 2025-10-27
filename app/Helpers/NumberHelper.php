<?php

if (!function_exists('numberToWords')) {
    /**
     * Convert number to words in English
     */
    function numberToWords($number) {
        $ones = [
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];
        
        $tens = [
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];
        
        $thousands = ['', 'Thousand', 'Lakh', 'Crore'];
        
        if ($number == 0) return 'Zero Taka Only';
        
        $result = '';
        $thousandIndex = 0;
        
        while ($number > 0) {
            $group = $number % 1000;
            if ($group != 0) {
                $groupWords = convertGroup($group, $ones, $tens);
                $result = $groupWords . ' ' . $thousands[$thousandIndex] . ' ' . $result;
            }
            $number = intval($number / 1000);
            $thousandIndex++;
        }
        
        return trim($result) . ' Taka Only';
    }
}

if (!function_exists('convertGroup')) {
    function convertGroup($number, $ones, $tens) {
        $result = '';
        
        if ($number >= 100) {
            $result .= $ones[intval($number / 100)] . ' Hundred ';
            $number %= 100;
        }
        
        if ($number >= 20) {
            $result .= $tens[intval($number / 10) * 10] . ' ';
            $number %= 10;
        }
        
        if ($number > 0) {
            $result .= $ones[$number] . ' ';
        }
        
        return trim($result);
    }
}

