<?php

namespace App\Enums;

final class Ranking
{
    const RANKING_MALE_DOUBLES = 'male-doubles';
    const RANKING_FEMALE_DOUBLES = 'female-doubles';
    const RANKING_MALE_SINGLES = 'male-singles';
    const RANKING_FEMALE_SINGLES = 'female-singles';
    const RANKING_MIXED_DOUBLES = 'mixed-doubles';

    const RANKING_ARRAY_TYPE = [
        'male-doubles', 'female-doubles', 'male-singles', 'female-singles', 'mixed-doubles'
    ];

    const RANKING_ARRAY_FORMAT = [
        'league-single-limination', 'league-round'
    ];

    const RANKING_ARRAY_ROUND = [
        'round 1',  'round 2', 'quarter-finals', 'semi-finals', 'finals'
    ];

    const TYPE_OF_LEAGUE = [
        'male-doubles' => 'Male Doubles',
        'female-doubles' => 'Female Doubles',
        'male-singles' => 'Male Singles',
        'female-singles' => 'Female Singles',
        'mixed-doubles' => 'Mixed Doubles',
    ];
}
