<?php
/**
 * Haravan Integration — Administration action → view mapping.
 *
 * Tells SuiteCRM's dispatcher that the URL action "HaravanSettings"
 * (case-insensitive; stored lower-case in the map) should be routed to
 * custom/modules/Administration/views/view.haravansettings.php
 * which defines class AdministrationViewHaravansettings.
 *
 * Merged by Quick Repair & Rebuild into
 *   custom/modules/Administration/Ext/ActionViewMap.ext.php
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$action_view_map['haravansettings'] = 'haravansettings';
