<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/05/16
 * Time: 11:32
 */

namespace Hos\Stats;

use DateTime;
use Hos\Option;
use stdClass;

class Visitor
{
    CONST STAT_FILE = Option::STAT_DIR . "visitor.json";

    static $visitor;
    static function addVisitor() {
        $visitors = self::get();
        $key = $_SERVER['REMOTE_ADDR'];
        if (isset($visitors[$key]))
            return false;
        $visitors[$key] = [
            'time' => date('c')
        ];
        file_put_contents(self::STAT_FILE, json_encode($visitors));
    }

    static function get() {
        if (!self::$visitor) {
            if (!file_exists(self::STAT_FILE))
                self::$visitor = [];
            else
                self::$visitor = json_decode(file_get_contents(self::STAT_FILE), true);
        }
        return self::$visitor;
    }

    static function getStats() {
        return [
            'perMonth' => function () {
                $years = [];
                foreach (self::get() as $visitor) {
                    $time = new DateTime($visitor['time']);
                    $year = $time->format('Y');
                    $month = $time->format('m');
                    if (!isset($years[$year]))
                        $years[$year] = [];
                    if (!isset($years[$year][$month]))
                        $years[$year][$month] = [];
                    $years[$year][$month][] = $visitor;
                }

                foreach ($years as &$year)
                    $year = count($year) + 1;

                return array_sum($years) / count($years);
            }
        ];
    }
}