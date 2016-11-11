<?php
namespace MyDate;

use MyDate\MyDate;

class Date {
    /**
     * @var int
     */
    protected $day;

    /**
     * @var int
     */
    protected $month;

    /**
     * @var int
     */
    protected $year;

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function __construct($year, $month, $day) {
        MyDate::validateDate($year, $month, $day);
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    /**
     * Implement getter so year, month, day variables are easily accessible
     * @param string $property
     * @return int
     */
    public function __get($property) {
        if (in_array($property, ["year", "month", "day"])) {
            return $this->$property;
        }
    }

    /**
     * Instantiates new object from string in YYYY/MM/DD format
     * @param string $date
     * @return MyDate\Date
     * @throws MyDate\Exception\ValidationException
     */
    public static function fromString($date) {
        $parsed = self::parseDate($date);
        return new self($parsed[0], $parsed[1], $parsed[2]);
    }

    /**
     * Compares to another Date object. Returns 1 if bigger, 0 if equal, -1 if smaller.
     * @param MyDate\Date $date
     * @return int
     * @throws InvalidArgumentException
     */
    public function compareTo($date) {
        if (!is_a($date, "MyDate\Date")) {
            throw new \InvalidArgumentException("Can only compare to another MyDate\Date object");
        }
        // make the comparison a bit easier by expressing the date as an integer
        $thisScore = $this->year*10000 + $this->month*100 + $this->day*1;
        $otherScore = $date->year*10000 + $date->month*100 + $date->day*1;
        if ($thisScore > $otherScore) {
            return 1;
        } elseif ($thisScore < $otherScore) {
            return -1;
        }
        return 0;
    }

    /**
     * Returns number of days until the end of the month
     * @return int
     */
    public function daysUntilMonthEnd() {
        $daysInMonth = MyDate::daysInMonth($this->year, $this->month);
        return $daysInMonth - $this->day;
    }

    /**
     * Returns number of days until the end of the year
     * @return int
     */
    public function daysUntilYearEnd() {
        $result = $this->daysUntilMonthEnd();
        for ($x = $this->month+1; $x<=12; $x++) {
            $result += MyDate::daysInMonth($this->year, $x);
        }
        return $result;
    }

    /**
     * Returns number of days since the start of the year
     * @return int
     */
    public function daysSinceYearStart() {
        $result = $this->day;
        for ($x = 1; $x<=$this->month-1; $x++) {
            $result += MyDate::daysInMonth($this->year, $x);
        }
        return $result;
    }

    /**
     * Parses and validates date in YYYY/MM/DD. Returns it as an array.
     * @param  string  $date
     * @return array  [year, month, day]
     * @throws MyDate\Exception\ValidationException
     */
    protected static function parseDate($date) {
        // break the input into individual parts
        $parts = explode('/', $date);

        // check number of parts
        if (count($parts) != 3) {
            throw new ValidationException("Date must consist of three parts (YYYY/MM/DD)");
        }

        // validate
        $year = (int)$parts[0];
        $month = (int)$parts[1];
        $day = (int)$parts[2];
        MyDate::validateDate($year, $month, $day);

        return array($year, $month, $day);
    }
}
?>