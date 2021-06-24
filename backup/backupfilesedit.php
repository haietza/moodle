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
 * Manage backup files
 * @package   moodlecore
 * @copyright 2010 Dongsheng Cai <dongsheng@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once(__DIR__ . '/backupfilesedit_form.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/repository/lib.php');

// current context
$contextid = required_param('contextid', PARAM_INT);
$currentcontext = required_param('currentcontext', PARAM_INT);
// file parameters
$component  = optional_param('component', null, PARAM_COMPONENT);
$filearea   = optional_param('filearea', null, PARAM_AREA);
$returnurl  = optional_param('returnurl', null, PARAM_LOCALURL);

list($context, $course, $cm) = get_context_info_array($currentcontext);
$filecontext = context::instance_by_id($contextid, IGNORE_MISSING);

$url = new moodle_url('/backup/backupfilesedit.php', array('currentcontext'=>$currentcontext, 'contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea));

require_login($course, false, $cm);
require_capability('moodle/restore:uploadfile', $context);
if ($filearea == 'automated' && !can_download_from_backup_filearea($filearea, $context)) {
    throw new required_capability_exception($context, 'moodle/backup:downloadfile', 'nopermissions', '');
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('managefiles', 'backup'));
$PAGE->set_heading(get_string('managefiles', 'backup'));
$PAGE->set_pagelayout('admin');
$browser = get_file_browser();

$maxbytes = USER_CAN_IGNORE_FILE_SIZE_LIMITS;
$maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
if (!empty($CFG->userbackupquota) && $CFG->userbackupquota > 0) {
    $maxbytes = $CFG->userbackupquota;
    $maxareabytes = $CFG->userbackupquota;
}
if (has_capability('moodle/user:ignoreuserquota', $context)) {
    $maxbytes = USER_CAN_IGNORE_FILE_SIZE_LIMITS;
    $maxareabytes = FILE_AREA_MAX_BYTES_UNLIMITED;
}

$data = new stdClass();
$options = array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => -1, 'accepted_types' => '*',
    'return_types' => FILE_INTERNAL, 'areamaxbytes' => $maxareabytes);
file_prepare_standard_filemanager($data, 'files', $options, $filecontext, $component, $filearea, 0);
$form = new backup_files_edit_form(null, array('data' => $data, 'contextid' => $contextid, 'currentcontext' => $currentcontext, 'filearea' => $filearea,
    'component' => $component, 'returnurl' => $returnurl, 'maxbytes' => $maxbytes, 'areamaxbytes' => $maxareabytes));

if ($form->is_cancelled()) {
    redirect($returnurl);
}

$data = $form->get_data();
if ($data) {
    $formdata = file_postupdate_standard_filemanager($data, 'files', $options, $filecontext, $component, $filearea, 0);
    redirect($returnurl);
}

echo $OUTPUT->header();

echo $OUTPUT->container_start();
if ($maxareabytes != FILE_AREA_MAX_BYTES_UNLIMITED) {
    $fileareainfo = file_get_file_area_info($contextid, $component, $filearea);
    // Display message only if we have files.
    if ($fileareainfo['filecount']) {
        $a = (object) [
            'used' => display_size($fileareainfo['filesize_without_references']),
            'total' => display_size($maxareabytes)
        ];
        $quotamsg = get_string('quotausage', 'moodle', $a);
        $notification = new \core\output\notification($quotamsg, \core\output\notification::NOTIFY_INFO);
        echo $OUTPUT->render($notification);
    }
}
$form->display();
echo $OUTPUT->container_end();

echo $OUTPUT->footer();
