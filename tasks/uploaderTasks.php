<?php

use Crunz\Schedule;

$schedule = new Schedule();
$task = $schedule->run(PHP_BINARY .' mysql.php');
$task->everyMinute();

return $schedule;