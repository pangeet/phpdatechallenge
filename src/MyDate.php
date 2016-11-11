<?php
namespace MyDate;

use MyDate\Exception\ValidationException;

class MyDate {

    /**
     * Number of days in months
     * @var array
     */
    public static $daysInMonths = array(
        1 => 31,
        2 => 28,
        3 => 31,
        4 => 30,
        5 => 31,
        6 => 30,
        7 => 31,
        8 => 31,
        9 => 30,
        10 => 31,
        11 => 30,
        12 => 31);

    /**
     * Calculates the difference between two dates
     * @param  string  $start  Start date (YYYY/MM/DD)
     * @param  string  $end  End date (YYYY/MM/DD)
     * @return object
     * @throws MyDate\Exception\ParsingException
     */
    public static function diff($start, $end) {
        $start = Date::fromString($start);
        $end = Date::fromString($end);

        // check if $start is before $end - if not, swap
        $invert = false;
        if ($start->compareTo($end) == 1) {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
            $invert = true;
        }

        // Compute the dirrerence in years, months and days
        $ymd = self::diffYMD($start, $end);

        // Compute the difference in days
        $totalDays = self::diffDays($start, $end);

        // return result
        return (object)array(
            'years' => $ymd[0],
            'months' => $ymd[1],
            'days' => $ymd[2],
            'total_days' => $totalDays,
            'invert' => $invert);
    }

    /**
     * Calculates the difference in days between two dates
     * @param  MyDate\Date  $start
     * @param  MyDate\Date  $end
     * @return int
     */
    protected static function diffDays($start, $end) {
        // If the dates are the same
        if ($start->compareTo($end) == 0) {
            $totalDays = 0;
        }
        // If years and months are the same
        elseif ($start->year == $end->year && $start->month == $end->month) {
            $days = $end->day - $start->day;
            $totalDays = $days;
        }
        // If years are the same (but not months)
        elseif ($start->year == $end->year) {
            // take days until the end of month of the start month
            $totalDays = $start->daysUntilMonthEnd();
            // add days in the end month
            $totalDays += $end->day;
            // add days in months in between
            for ($x = $start->month; $x<$end->month-1; $x++) {
                $totalDays += self::daysInMonth($start->year, $x);
            }
        }
        // Other cases
        else {
            // take days until the end of the year of the start date
            $totalDays = $start->daysUntilYearEnd();
            // add days since the start of the year of the end date
            $totalDays += $end->daysSinceYearStart();
            // add days in years in between
            for ($x = $start->year+1; $x<=$end->year-1; $x++) {
                $totalDays += self::daysInYear($x);
            }
        }
        return $totalDays;
    }

    /**
     * Calculates the difference in days between two dates
     * @param  MyDate\Date  $start
     * @param  MyDate\Date  $end
     * @return array [years, months, days]
     */
    protected static function diffYMD($start, $end) {
        // Days
        if ($start->day == $end->day) {
            $days = 0;
        } elseif ($start->day < $end->day) {
            $days = $end->day - $start->day;
        } else {
            $days = $start->daysUntilMonthEnd() + $end->day;
        }
        // Months
        if ($start->month == $end->month) {
            if ($start->day <= $end->day) {
                $months = 0;
            } else {
                $months = 11;
            }
        } elseif ($start->month < $end->month) {
            $months = $end->month - $start->month;
            if ($start->day > $end->day) {
                $months--;
            }
        } else {
            $months = 12 - $start->month + $end->month;
            if ($start->day > $end->day) {
                $months--;
            }
        }
        // Years
        $years = $end->year - $start->year;
        if ($start->month > $end->month) {
            $years--;
        } elseif ($start->month == $end->month) {
            if ($start->day > $end->day) {
                $years--;
            }
        }
        return [$years, $months, $days];
    }

    /**
     * Check if the given year is a leap year
     * @param  int  $year
     * @return bool
     */
    protected static function isLeapYear($year) {
        // if the year is evenly divisible by 4
        if ($year % 4 == 0) {
            // if the year is evenly divisible by 100
            if ($year % 100 == 0) {
                // if the year is evenly divisible by 400
                if ($year % 400 == 0) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Return number of days in the given month
     * @param  int  $year
     * @param  int  $month
     * @return int
     * @throws InvalidArgumentException
     */
    public static function daysInMonth($year, $month) {
        self::validateDate($year, $month);
        if (self::isLeapYear($year) && $month == 2) {
            return 29;
        }
        return self::$daysInMonths[$month];
    }

    /**
     * Return number of days in the given year
     * @param  int  $year
     * @return int
     * @throws InvalidArgumentException
     */
    public static function daysInYear($year) {
        self::validateDate($year);
        if (self::isLeapYear($year)) {
            return 366;
        }
        return 365;
    }

    /**
     * Validates date. Returns true if valid, otherwise throws an exception.
     * @param int $year
     * @param int $month
     * @param int $day
     * @return bool
     * @throws MyDate\Exception\ValidationException
     */
    public static function validateDate($year, $month = null, $day = null) {
        // check year
        if ($year == 0) {
            throw new ValidationException("Year zero doesn't exist");
        } elseif ($year < 0) {
            throw new ValidationException("Sorry, B.C. is not suported");
        }

        // check month
        if ($month !== null) {
            if ($month < 1 || $month > 12) {
                throw new ValidationException("Month number seems to be incorrect");
            }
        }

        // check day
        if ($month !== null && $day !== null) {
            if ($day < 1) {
                throw new ValidationException("Month number seems to be incorrect");
            }
            if ($day > MyDate::$daysInMonths[$month]) {
                // wait, it could be because of the leap year
                if ($month != 2 || $day != 29 || !self::isLeapYear($year)) {
                    throw new ValidationException("Day number seems to be incorrect");
                }
            }
        }
        return true;
    }
}