<?php

namespace App\Http\Controllers\Api\V1;

/**
 * Backwards-compatible wrapper: keep `AlertsController` delegating
 * to the new `AlertController` to avoid breaking routes that reference
 * the old controller name.
 */
class AlertsController extends AlertController
{
    // empty - uses AlertController implementations
}
