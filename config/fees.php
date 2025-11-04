<?php

/**
 * DEPRECATED: This config file is no longer used.
 * Fee values are now managed through the database (fees table).
 * To update fees, use the Settings page (Settings → Fees tab).
 *
 * Fee values are retrieved using: Fee::getAmount('sticker_fee', 15.00)
 */

return [
    // Deprecated - Use database fees table instead
    'sticker' => 15.00,
];
