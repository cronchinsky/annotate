<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * annotate module admin settings and defaults
 *
 * @package    mod
 * @subpackage annotate
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    
    
    $settings->add(new admin_setting_configmultiselect('annotate/displayoptions',
        'Display Options', '',
        $defaultdisplayoptions, $displayoptions));

    $settings->add(new admin_setting_configselect_with_advanced('annotate/display',
        'Default Display', 'Sets the default display type',
        array('value'=>RESOURCELIB_DISPLAY_AUTO, 'adv'=>false), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('annotate/popupwidth',
        'Pop-Up Width', 'Default Pop-Up Width in Pixels',
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('annotate/popupheight',
        'Pop-Up Height', 'Default Pop-Up Height in Pixels',
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
}
