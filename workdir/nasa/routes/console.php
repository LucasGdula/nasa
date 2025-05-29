<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('neo:fetch-today')->dailyAt('00:01');
