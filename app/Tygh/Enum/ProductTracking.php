<?php
namespace Tygh\Enum;

/**
 * ProductTracking contains possible values for `products`.`tracking` DB field.
 *
 * @package Tygh\Enum
 */
class ProductTracking
{
    /**
     * Track product amount for every option combination
     */
    const TRACK_WITH_OPTIONS = 'O';

    /**
     * Track product amount
     */
    const TRACK_WITHOUT_OPTIONS = 'B';

    /**
     * Do not track product amount
     */
    const DO_NOT_TRACK = 'D';
}
