<?php

/* Класс для ограничения частоты запросов к нашему API */
/* Код взят отсюда: https://github.com/maicolbruski/php-rate-limit/blob/master/Ratelimiter.php */

class Ratelimiter
{
    protected static $allow;
    protected static $lastCheck;
	

    /**
     * @param int $rate
     * @param int $per
     *
     * @return void
     */
    public static function check($rate = 5, $per = 1)
    {
        self::$lastCheck = self::$lastCheck ?? microtime(true);
        self::$allow = self::$allow ?? $rate;

        $consumed = 1;
        $current = microtime(true);
        $timePassed = $current - self::$lastCheck;
        self::$lastCheck = $current;

        self::$allow += $timePassed * ($rate / $per);

        if (self::$allow > $rate) {
            self::$allow = $rate;
        }

        if (self::$allow < $consumed) {
            $duration = ($consumed - self::$allow) * ($per / $rate - 1);
            self::$lastCheck += $duration;
            usleep($per * 1000000);
            self::$allow = $rate;
        } else {
            self::$allow -= $consumed;
        }

        return;
    }
}