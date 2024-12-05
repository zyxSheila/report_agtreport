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
 * @package   report_agtreport
 * @copyright 2024, Yixuan ZHANG <Yixuan.Zhang@xjtlu.edu.cn>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/adminlib.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($course->id);
require_capability('moodle/course:viewparticipants', $context);

$PAGE->set_url(new moodle_url('/report/agtreprot/index.php', array('courseid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('pluginname', 'report_agtreport'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo html_writer::div(get_string('displayalert', 'report_agtreport'), 'alert alert-info');

//form definition for the quiz selection
class report_agreeresults_form extends moodleform
{
    function definition()
    {
        global $DB;

        $mform = $this->_form;

        // Fetch quiz activities available in the course
        // $courseid = optional_param('id', SITEID, PARAM_INT);
        // Retrieve course ID from custom data
        $courseid = $this->_customdata['courseid'];

        // Add courseid as a hidden field
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $assign_options = array();
        $assigns = $DB->get_records('assign', array('course' => $courseid));

        if (!empty($assigns)) {
            foreach ($assigns as $assign) {
                $assign_options[$assign->id] = format_string($assign->name);
            }
            // Add quiz selection dropdown
            $mform->addElement('select', 'assignid', get_string('selectassignment', 'report_agtreport'), $assign_options);

            // Add submit button
            $this->add_action_buttons(false, get_string('viewresults', 'report_agtreport'));
        } else {
            // No quizzes found in the course
            $mform->addElement('static', '', '', get_string('noassigns', 'report_agtreport'));
        }


    }
}

$mform = new report_agreeresults_form(null, array('courseid' => $courseid));
// $mform->display();
if ($mform->is_cancelled()) {
    // Handle form cancellation
} else if ($formdata = $mform->get_data()) {
    // Form submitted, process data if needed
    // Handle form submission and display results
    if (!empty($formdata->assignid)) {
        $assignid = $formdata->assignid;
        $sql = "
            SELECT u.firstname, u.lastname, u.email, u.idnumber, ax.agree
            FROM {assign} a
            JOIN {course_modules} cm ON cm.instance = a.id AND a.id = :assignid
            JOIN {assignsubmission_xagree} ax ON cm.id = ax.cmid
            JOIN {user} u ON ax.userid = u.id
        ";

        // $sql = "
        //     SELECT u.firstname, u.lastname, u.email, u.idnumber, 
        //     CASE 
        //             WHEN ax.cmid IS NOT NULL THEN 'Agreed' 
        //             ELSE 'Not Agreed' 
        //     END AS agree
        //     FROM {user} u
        //     JOIN {user_enrolments} ue ON ue.userid = u.id
        //     JOIN {enrol} e ON e.id = ue.enrolid
        //     JOIN {course_modules} cm ON cm.course = e.courseid
        //     JOIN {assign} a ON cm.instance = a.id
            
        //     LEFT JOIN {assignsubmission_xagree} ax ON cm.id = ax.cmid AND ax.userid = u.id
        //     WHERE e.courseid = :courseid AND a.id = :assignid
        // ";

        $params = ['assignid' => $assignid, 'courseid' => $courseid];
        $assign_results = $DB->get_records_sql($sql, $params);

        // Convert the results into JSON
        $assign_results_json = json_encode($assign_results);
        

        if (!empty($assign_results)) {
            // Initialize the table
            $table = new flexible_table('agree-results-table');

            // Define columns and their headers
            $columns = array('idnumber', 'fullname', 'email', 'agree');
            $headers = array(
                get_string('idnumber', 'report_agtreport'),
                get_string('fullname'),
                get_string('email'),
                get_string('agree', 'report_agtreport')
            );

            // Set up the table
            $table->define_columns($columns);
            $table->define_headers($headers);
            $table->define_baseurl($PAGE->url);

            // Set table attributes
            $table->set_attribute('cellspacing', '0');
            $table->set_attribute('id', 'agree-results-table');
            $table->set_attribute('class', 'generaltable generalbox');
            $table->setup();
        
            // Populate the table
            foreach ($assign_results as $result) {
                $fullname = $result->firstname. ' '. $result->lastname ;
                $data = array(
                    $result->idnumber,
                    $fullname,
                    $result->email,
                    $result->agree
                );
                $table->add_data($data);
            }

            // Display the table
            $table->finish_output();
            $table->print_html();
                                                                        
            echo '<p><hr></p>';
            echo html_writer::start_tag('div');
            echo '<h5>Export the table: </h5>';
            echo html_writer::link(new moodle_url('/report/agtreport/export.php', array('courseid' => $courseid, 'format' => 'csv', 'assignid' => $assignid, 'data' => urlencode($assign_results_json))), get_string('exportcsv', 'report_agtreport'), array('class' => 'btn btn-primary'));
            echo '<span>&nbsp;</span>';
            echo html_writer::link(new moodle_url('/report/agtreport/export.php', array('courseid' => $courseid, 'format' => 'excel', 'assignid' => $assignid, 'data' => urlencode($assign_results_json))), get_string('exportexcel', 'report_agtreport'), array('class' => 'btn btn-secondary'));
            echo html_writer::end_tag('div');
        } else {
            // No quiz results found
            echo html_writer::div(get_string('noassignresults', 'report_agtreport'), 'alert alert-info');
        }
    }
} else {
    $mform->display();
}
echo $OUTPUT->footer();
?>