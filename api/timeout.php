<?php

include "../inc/defines.php";
require_once "../inc/session.php";

header("Content-type: application/json; charset=utf-8");

/* Uses the same session settings as the rest of the panel.
 * panel_session_begin() also logs out after the idle timeout.
 * A session without a login is left alone: it may hold the
 * CSRF token of a login page that is open in another tab.
 * Does not count as activity.
 */
panel_session_begin();
if (panel_session_uid() !== NULL)
    die(json_encode(['session' => 'active']));
die(json_encode(['session' => 'none']));
