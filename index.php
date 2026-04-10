<?php

require __DIR__ . "/vendor/autoload.php";

use App\Core\Session;
use App\Models\School;
use App\Models\User;

new Session();

require __DIR__ . "/routes/web.php";