<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Your navigation icons data
// nav-icons.php
$icons = [
    [
        'id' => 'home',
        'emoji' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M120-200v-385q0-25 14-45.5t37-29.5l415-159q20-8 37 4t17 33v302h40q0-33 23.5-56.5T760-560q33 0 56.5 23.5T840-480v280q0 33-23.5 56.5T760-120H200q-33 0-56.5-23.5T120-200Zm80 0h160v-200q0-33 23.5-56.5T440-480h120v-244L200-585v385Zm240 0h120v-80q0-17 11.5-28.5T600-320q17 0 28.5 11.5T640-280v80h120v-200H440v200Zm-60-262Zm220 162Zm0 0Z"/></svg>',
        'active' => true
    ],
    [
        'id' => 'search',
        'emoji' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M320-516v-128q0-12 10.5-17.5t20.5.5l102 64q10 6 10 17t-10 17l-102 64q-10 6-20.5.5T320-516Zm60 196q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>',
        'active' => false
    ],
    [
        'id' => 'feed',
        'emoji' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M440-120H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240v720Zm-80-80v-560H200v560h160Zm160-320v-320h240q33 0 56.5 23.5T840-760v240H520Zm80-80h160v-160H600v160Zm-80 480v-320h320v240q0 33-23.5 56.5T760-120H520Zm80-80h160v-160H600v160ZM360-480Zm240-120Zm0 240Z"/></svg>',
        'active' => false
    ],
    [
        'id' => 'favorites',
        'emoji' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-147q-14 0-28.5-5T426-168l-69-63q-106-97-191.5-192.5T80-634q0-94 63-157t157-63q53 0 100 22.5t80 61.5q33-39 80-61.5T660-854q94 0 157 63t63 157q0 115-85 211T602-230l-68 62q-11 11-25.5 16t-28.5 5Zm-38-543q-29-41-62-62.5T300-774q-60 0-100 40t-40 100q0 52 37 110.5T285.5-410q51.5 55 106 103t88.5 79q34-31 88.5-79t106-103Q726-465 763-523.5T800-634q0-60-40-100t-100-40q-47 0-80 21.5T518-690q-7 10-17 15t-21 5q-11 0-21-5t-17-15Zm38 189Z"/></svg>',
        'active' => false
    ],
    [
        'id' => 'profile',
        'emoji' => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M480-440q-66 0-113-47t-47-113v-140q0-25 17.5-42.5T380-800q15 0 28.5 7t21.5 20q8-13 21.5-20t28.5-7q15 0 28.5 7t21.5 20q8-13 21.5-20t28.5-7q25 0 42.5 17.5T640-740v140q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T560-600v-100H400v100q0 33 23.5 56.5T480-520ZM160-200v-32q0-34 17.5-62.5T224-338q62-31 126-46.5T480-400q66 0 130 15.5T736-338q29 15 46.5 43.5T800-232v32q0 33-23.5 56.5T720-120H240q-33 0-56.5-23.5T160-200Zm80 0h480v-32q0-11-5.5-20T700-266q-54-27-109-40.5T480-320q-56 0-111 13.5T260-266q-9 5-14.5 14t-5.5 20v32Zm240 0Zm0-500Z"/></svg>',
        'active' => false
    ]
];

    echo json_encode($icons);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>