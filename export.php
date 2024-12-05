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

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/excellib.class.php');

// Ensure the user is logged in and has necessary capabilities
require_login();

$courseid = required_param('courseid', PARAM_INT);
$format = required_param('format', PARAM_ALPHA);

// Ensure the user has the necessary capabilities in the course context
$context = context_course::instance($courseid);
require_capability('moodle/course:viewparticipants', $context);

// Get data from the query parameter
$data = required_param('data', PARAM_RAW);
$assign_results = json_decode(urldecode($data), false);

if ($format == 'csv') {
    // Generate CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="agreement_results.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array(get_string('idnumber', 'report_agtreport'), get_string('fullname'), get_string('email'), get_string('agree', 'report_agtreport')));

    if(!empty($assign_results)){
        foreach ($assign_results as $result) {
            $fullname = $result->firstname. ' '. $result->lastname ;
            $data = array($result->idnumber, $fullname, $result->email, $result->agree);
            fputcsv($output, $data);
        }
    }
    fclose($output);
    exit;
} else if ($format == 'excel') {
    // Generate Excel file using PHPExcel library
    $filename = 'agreement_results.xlsx';
    $workbook = new MoodleExcelWorkbook($filename);
    $worksheet = $workbook->add_worksheet('Assignment Results');
    $worksheet->write_string(0, 0, get_string('idnumber', 'report_agtreport'));
    $worksheet->write_string(0, 1, get_string('fullname'));
    $worksheet->write_string(0, 2, get_string('email'));
    $worksheet->write_string(0, 3, get_string('agree', 'report_agtreport'));

    $row = 1;
    if(!empty($assign_results)){
        foreach ($assign_results as $result) {
            $fullname = $result->firstname. ' '. $result->lastname;
            $worksheet->write_string($row, 0, $result->idnumber);
            $worksheet->write_string($row, 1, $fullname);
            $worksheet->write_string($row, 2, $result->email);
            $worksheet->write_number($row, 3, $result->agree);
            $row++;
        }
    }

    $workbook->close();
    exit;
}
