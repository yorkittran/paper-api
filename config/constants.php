<?php

return [
    'user' => [
        'role' => [
            '0'       => 'Admin',
            '1'       => 'Manager',
            '2'       => 'Member',
            'admin'   => '0',
            'manager' => '1',
            'member'  => '2',
        ],
    ],
    'task' => [
        'status' => [
            '0'                => 'Pending Approval',
            '1'                => 'Approved',
            '2'                => 'Rejected',
            '3'                => 'Not Started',
            '4'                => 'Ongoing',
            '5'                => 'Committed',
            '6'                => 'Completed',
            '7'                => 'Incompleted',
            '8'                => 'Overdue',
            'pending_approval' => '0',
            'approved'         => '1',
            'rejected'         => '2',
            'not_started'      => '3',
            'ongoing'          => '4',
            'committed'        => '5',
            'completed'        => '6',
            'incompleted'      => '7',
            'overdue'          => '8',
        ],
        'mark' => [
            '1' => 'Very Bad',
            '2' => 'Bad',
            '3' => 'Average',
            '4' => 'Mostly Cover',
            '5' => 'Excellent',
        ]
    ]
];
