<?php

namespace Kevins\Calendar;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ColombiaHolidays
{

    const FIXED_HOLIDAYS = [
        '01-01',
        '05-01',
        '07-20',
        '08-07',
        '12-08',
        '12-25'
    ];

    const NEXT_MONDAY_HOLIDAYS = [
        '01-06',
        '03-19',
        '06-29',
        '08-15',
        '10-12',
        '11-01',
        '11-11',
    ];

    const NEXT_FROM_NUM_WEEKS = [
        6,
        9,
        10
    ];

    public static function getHolidays (string $year): Collection
    {
        $fixedDates = [];
        foreach (self::FIXED_HOLIDAYS as $key => $value) {
            $fixedDates[] = Carbon::parse("$year-$value");
        }

        $nextMondayDates = [];
        foreach (self::NEXT_MONDAY_HOLIDAYS as $key => $value) {
            $nextMondayDates[] = self::calcNextMondayFromDate(Carbon::parse("$year-$value"));
        }

        $nextFromNumWeeksDates = [];
        foreach (self::NEXT_FROM_NUM_WEEKS as $key => $value) {
            $nextFromNumWeeksDates[] = self::calcNextMondayFromDomingoPascua($year, $value);
        }

        $holyWeekDates = self::getHolyWeekDays($year);

        $dates = collect([
            ...$fixedDates,
            ...$nextMondayDates,
            ...$nextFromNumWeeksDates,
            ...$holyWeekDates
        ]);

        return $dates->sortBy(function ($fecha) {
            return $fecha->timestamp;
        })->values();
    }

    private static function calcularDomingoPascua($year)
    {
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }

    private static function calcNextMondayFromDomingoPascua ($year, $numWeeks)
    {
        $date = self::calcularDomingoPascua($year);
        return $date->addWeeks($numWeeks)->next(Carbon::MONDAY);
    }

    private static function calcNextMondayFromDate (Carbon $date)
    {
        // Si ya es lunes, devuelve la misma fecha
        if ($date->dayOfWeek === Carbon::MONDAY) {
            return $date;
        }

        // Si no es lunes, calcula el siguiente lunes
        return $date->next(Carbon::MONDAY);
    }

    private static function getHolyWeekDays($year): array
    {
        $domingoPascua = self::calcularDomingoPascua($year);
        // return [
        //     // $domingoPascua->copy()->subDays(7),
        //     // $domingoPascua->copy()->subDays(6),
        //     // $domingoPascua->copy()->subDays(5),
        //     // $domingoPascua->copy()->subDays(4),
        //     $domingoPascua->copy()->subDays(3),
        //     $domingoPascua->copy()->subDays(2),
        //     // $domingoPascua->copy()->subDay(),
        //     // $domingoPascua,
        // ];
        return [
            $domingoPascua->copy()->subDays(3),
            $domingoPascua->copy()->subDays(2)
        ];
    }

}