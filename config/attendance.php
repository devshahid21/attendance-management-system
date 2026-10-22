<?php
return [
    'timezone' => 'Asia/Karachi',
    'office_start' => env('OFFICE_START', '09:00'),
    'grace_minutes' => (int) env('OFFICE_GRACE_MINUTES', 15),
    'working_days' => array_map('intval', explode(',', env('WORKING_DAYS', '1,2,3,4,5'))),
];
