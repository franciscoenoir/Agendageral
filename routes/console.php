<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:verificar-alertas')->dailyAt('08:00');
