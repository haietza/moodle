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
require_once($CFG->libdir.'/formslib.php');

class backup_files_edit_form extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform =& $this->_form;

        $maxbytes = $this->_customdata['maxbytes'];
        $maxareabytes = $this->_customdata['areamaxbytes'];

        $types = (FILE_INTERNAL | FILE_REFERENCE | FILE_CONTROLLED_LINK);
        $options = array('subdirs' => 0, 'maxfiles' => -1, 'accepted_types' => '*', 'return_types' => $types, 'maxbytes' => $maxbytes, 'areamaxbytes' => $maxareabytes);

        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);

        $mform->addElement('hidden', 'contextid', $this->_customdata['contextid']);
        $mform->setType('contextid', PARAM_INT);

        $mform->addElement('hidden', 'currentcontext', $this->_customdata['currentcontext']);
        $mform->setType('currentcontext', PARAM_INT);

        $mform->addElement('hidden', 'filearea', $this->_customdata['filearea']);
        $mform->setType('filearea', PARAM_AREA);

        $mform->addElement('hidden', 'component', $this->_customdata['component']);
        $mform->setType('component', PARAM_COMPONENT);

        $mform->addElement('hidden', 'returnurl', $this->_customdata['returnurl']);
        $mform->setType('returnurl', PARAM_URL);

        $this->add_action_buttons(true, get_string('savechanges'));
        $this->set_data($this->_customdata['data']);
    }

    /**
     * Validate update settings form submissions.
     * {@inheritDoc}
     * @see moodleform::validation()
     *
     * @param array $data Array of ("fieldname"=>value) of submitted data.
     * @param array $files Array of uploaded files "element_name"=>tmp_file_path.
     * @return array Array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = array();
        $draftitemid = $data['files_filemanager'];
        if (file_is_draft_area_limit_reached($draftitemid, $this->_customdata['areamaxbytes'])) {
            $errors['files_filemanager'] = get_string('userbackupquotalimit', 'error');
        }

        return $errors;
    }
}
