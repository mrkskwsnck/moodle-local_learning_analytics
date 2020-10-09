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
 * Weekly Heatmap report
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\plot;
use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use lareport_weekheatmap\query_helper;
use local_learning_analytics\router;
use local_learning_analytics\settings;

class lareport_weekheatmap extends report_base {

    public function run(array $params): array {
        global $USER, $OUTPUT, $DB;
        $courseid = $params['course'];
        $privacythreshold = settings::get_config('dataprivacy_threshold');

        $plotdata = [];
        $textdata = [];
        $xstrs = [];
        $texts = [];

        $days = array('sunday', 'saturday', 'friday', 'thursday', 'wednesday', 'tuesday', 'monday');
        $ystrs = [];
        foreach ($days as $day) {
            $ystrs[] = get_string($day, 'calendar');
        }
        
        // not able to use 12/24h format for now, as 12h format does not work with heatmap (as there are the same x axis values twice)
        // $timeformat = get_user_preferences('calendar_timeformat');
        // if (empty($timeformat)) {
        //     $timeformat = get_config(null, 'calendar_site_timeformat');
        // }

        $hitsstr = get_string('hits', 'lareport_weekheatmap');
        $heatpoints = query_helper::query_heatmap($courseid);
        for ($d = 0; $d < 7; $d += 1) {
            // we need to start the plot at the bottom (sun -> sat -> fri -> ...)
            $startpos = (6 - $d) * 24;
            $daydata = [];
            $textdata = [];
            for ($h = 0; $h < 24; $h += 1) {
                $datapoint = empty($heatpoints[$startpos + $h]) ? 0 : $heatpoints[$startpos + $h]->value;
                $text = $datapoint;
                if ($datapoint < $privacythreshold) {
                    $text = '< ' . $privacythreshold;
                }
                $daydata[] = $datapoint;
                $hourstr = str_pad($h, 2, '0', STR_PAD_LEFT);
                $x = "{$hourstr}:00 - {$hourstr}:59";
                $xstrs[] = $x;
                $textdata[] = "<b>{$text} {$hitsstr}</b><br>{$ystrs[$d]}, {$x}";
            }
            $plotdata[] = $daydata;
            $texts[] = $textdata;
        }

        $plot = new plot();
        $plot->add_series([
            'type' => 'heatmap',
            'z' => $plotdata,
            'x' => $xstrs,
            'y' => $ystrs,
            'text' => $texts,
            'hoverinfo' => 'text',
            'colorscale' => [ // reversed "YlGnBu"
                [0,"rgb(255,255,217)"],
                [.125,"rgb(237,248,217)"],
                [.25,"rgb(199,233,180)"],
                [.375,"rgb(127,205,187)"],
                [.5,"rgb(65,182,196)"],
                [.625,"rgb(29,145,192)"],
                [.75,"rgb(34,94,168)"],
                [.875,"rgb(37,52,148)"],
                [1,"rgb(8,29,88)"],
            ]
        ]);
        $layout = new stdClass();
        $layout->margin = [ 't' => 10, 'r' => 20, 'l' => 80, 'b' => 80 ];
        $plot->set_layout($layout);
        $plot->set_height(450);

        return [
            self::heading(get_string('pluginname', 'lareport_weekheatmap')),
            '<p>' . get_string('introduction', 'lareport_weekheatmap') . '</p>',
            $plot
        ];
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }
}