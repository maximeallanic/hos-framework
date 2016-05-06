<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 07/04/16
 * Time: 20:33
 */

require_once __DIR__."/../vendor/autoload.php";

use Framework\Library\Backup;
use Hos\Log;
use Hos\Option;

function getEvents()
{
    $events = [
        "hourly" => [
            "date" => "i",
            "equal" => "00"
        ],
        "daily" => [
            "date" => "G",
            "equal" => "0"
        ],
        "monthly" => [
            "date" => "d",
            "equal" => "1"
        ],
        "yearly" => [
            "date" => "z",
            "equal" => "0"
        ]
    ];

    $previous = ["minutely"];
    $eventsProcess = ["minutely"];
    foreach ($events as $name => $f) {
        if (date($f['date']) === $f['equal']
            && count($previous) > 0 && count(array_diff_key($previous, $eventsProcess)) === 0)
            $eventsProcess[] = $name;
        $previous[] = $name;
    }
    return $eventsProcess;
}

$events = Option::get()['events'];
$eventsProcess = getEvents();
try {
    foreach ($events as $name => $classes) {
        if (in_array($name, $eventsProcess))
            foreach ($classes as $eventClasses) {
                $class = new ReflectionClass($eventClasses['class']);
                $method = $class->getMethod($eventClasses['method']);
                Log::info(json_encode($method->invokeArgs($class->newInstance(), $eventClasses['arguments'])));
            }
    }
} catch (ReflectionException $e) {
    Log::error($e->getMessage());
}