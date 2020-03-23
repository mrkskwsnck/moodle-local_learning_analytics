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
     * Main entry point for Learning Analytics UI
     *
     * @package     local_learning_analytics
     * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
     * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

    //define('CLI_SCRIPT', true);

    global $DB;
    $datas = $DB->get_records('local_learning_analytics_rep');
    $dataarray = array();
    foreach($datas as $data){
        array_push($dataarray, $data->reportname);
    }
    $insertarray = array();
    $reports = array('coursedashboard', 'activities', 'learners', 'browser_os');
    foreach($reports as $report) {
        if(!in_array($report, $dataarray)) {
            array_push($insertarray, set_entry($report));
        }
    }
    $DB->insert_records('local_learning_analytics_rep', $insertarray);

    function set_entry($report) {
        $entr = new \stdClass();
        $entr->reportname = $report;
        return $entr;
    }