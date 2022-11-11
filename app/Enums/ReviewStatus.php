<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ReviewStatus extends Enum
{
    const Pending = 'Pending';
    const Canceled = "Canceled";
    const Published = "Published";
    const Archived = "Archived";
}
