<?php

/**
 * @param int $idx
 * @return array
 */
function makeUserData_for_test( $idx=1 )
{
    $data = [
        'name' => 'test-' . $idx ,
        'age'  => 30 + $idx,
        'gender' => 1 + $idx%2,
        'status' => 1 + $idx%3,
        'bday' => (new \DateTime('1989-01-01'))->add(new \DateInterval('P1D'))->format('Y-m-d'),
        'no_null' => 'not null test: ' . mt_rand(1000,9999),
    ];
    return $data;
}

