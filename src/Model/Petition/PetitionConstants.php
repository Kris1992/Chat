<?php declare(strict_types=1);

namespace App\Model\Petition;

/**
 * Handler of all constants for petition
 */
class PetitionConstants
{
    const VALID_TYPES = ['Ban', 'Feature', 'Other'];
    const TYPES_DESC = [
        'Ban' => 'Ban',
        'New features' => 'Feature',
        'Other problems' => 'Other',
    ];
}
