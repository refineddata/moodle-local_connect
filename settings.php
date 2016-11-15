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
 * Local Connect Settings
 *
 * @package    local_connect
 * @copyright  2016 onwards Refined Data Solutions Inc {@link http://www.refineddata.com}
 * @author     Elvis Li <elvis.li@refineddata.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ( $hassiteconfig ) {
    $settings = new admin_settingpage('local_connect', get_string('settings', 'local_connect'));
    $ADMIN->add('localplugins', $settings);

    $message = '';
    if (!$DB->get_record('config_plugins', array('plugin' => 'local_refinedservices', 'name' => 'version'))) {
        $message .= get_string('localrefinedservicesnotinstalled', 'local_connect') . '<br />';
    }
    $rs_plugin_link = new moodle_url('/admin/settings.php?section=local_refinedservices');
    if (empty($CFG->connect_service_username)) {
        $message .= get_string('connectserviceusernamenotgiven', 'local_connect', array('url' => $rs_plugin_link->out())) . '<br />';
    }

    if (empty($CFG->connect_service_password)) {
        $message .= get_string('connectservicepasswordnotgiven', 'local_connect', array('url' => $rs_plugin_link->out())) . '<br />';
    }

    if (!empty($message)) {
        $caption = html_writer::tag('div', $message, array('class' => 'notifyproblem'));
        $setting = new admin_setting_heading('refined_services_warning', $caption, '<strong>' . get_string('connectsettingsrequirement', 'local_connect') . '</strong>');
        $settings->add($setting);
    }
}

if ($hassiteconfig && !empty($CFG->connect_service_username) && !empty($CFG->connect_service_password)) {

    $settings->add(new admin_setting_heading('generalhdr', new lang_string('general', 'local_connect'), ''));

    $setting = new admin_setting_configcheckbox('refinedservices_debug', get_string('refinedservices_debug', 'local_connect'), null, 0);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox('connect_override_password', get_string('override_password', 'local_connect'), get_string('override_password_description', 'local_connect'), 0);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox('connect_update', get_string('update', 'local_connect'), get_string('configupdate', 'local_connect'), 1);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);


    $setting = new admin_setting_configcheckbox('connect_updatedts', get_string('updatedts', 'local_connect'), get_string('configupdatedts', 'local_connect'), 0);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox('connect_instant_regrade', get_string('instantgrade', 'local_connect'), get_string('configinstantgrade', 'local_connect'), 0);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    //$settings->add(new admin_setting_configcheckbox('connect_mouseovers', get_string('mouseovers', 'local_connect'), get_string('mouseovers_hint', 'local_connect'), '1'));

    $settings->add(new admin_setting_configtext('connect_popup_height', get_string('popup_height', 'local_connect'), get_string('popup_height_hint', 'local_connect'), '800'));
    $settings->add(new admin_setting_configtext('connect_popup_width', get_string('popup_width', 'local_connect'), get_string('popup_width_hint', 'local_connect'), '800'));

    $setting = new admin_setting_configtext('connect_maxviews', get_string('cfgmaxviews', 'local_connect'), get_string('configmaxviews', 'local_connect'), -1, PARAM_INT);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    

    $settings->add(new admin_setting_heading('disphdr', new lang_string('disphdr', 'local_connect'), ''));

    $setting = new admin_setting_configcheckbox('connect_icondisplay', get_string('icondisplay', 'local_connect'), get_string('configicondisplay', 'local_connect'), 1);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox('connect_displayoncourse', get_string('displayoncourse', 'local_connect'), get_string('configdisplayoncourse', 'local_connect'), 1);
    $setting->set_updatedcallback('connect_update_config');
    $settings->add($setting);

    // Standard icon placement
    $szopt = array();
    $szopt['large'] = get_string('large', 'local_connect');
    $szopt['medium'] = get_string('medium', 'local_connect');
    $szopt['small'] = get_string('small', 'local_connect');
    $szopt['block'] = get_String('block', 'local_connect');
    $szopt['custom'] = get_String('custom', 'local_connect');
    $settings->add(new admin_setting_configselect('connect_iconsize', new lang_string('iconsize', 'local_connect'), '', 'medium', $szopt));
    // Icon position
    $posopt = array();
    $posopt['l'] = get_string('left', 'local_connect');
    $posopt['c'] = get_string('center', 'local_connect');
    $settings->add(new admin_setting_configselect('connect_iconpos', new lang_string('iconpos', 'local_connect'), '', 'left', $posopt));

    // Suppress iconic text
    $settings->add(new admin_setting_configcheckbox('connect_iconsilent', new lang_string('iconsilent', 'local_connect'), '', 0));
}

if (!function_exists('connect_update_config')) {

    function connect_update_config() {
        global $CFG;
        //die('connect_update_config');
        $params = array();
        foreach ($CFG as $name => $value) {
            if (preg_match('/connect\_/', $name) || $name == 'refinedservices_debug') {
                $params[] = array('name' => $name, 'value' => $value);
            }
        }
        //var_dump($params);
        //die('connect_update_config');
        if (!empty($params)) {
            require_once( $CFG->dirroot . '/local/connect/lib.php' );
            $connect = _connect_get_instance();
            return $connect->connect_call('setconfig', $params);
        }
    }

}
