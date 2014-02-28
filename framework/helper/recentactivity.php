<?php
/**
 * Moodlerooms Framework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @copyright Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @package mr
 * @author Mark Nielsen
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * @see mr_helper_abstract
 */
require_once($CFG->dirroot.'/local/mr/framework/helper/abstract.php');

/**
 * MR Helper Recent Activity
 *
 * This helper is used to get recent activity after
 * time X for a course or for a list of courses.
 *
 * @author Mark Nielsen
 * @package mr
 */
class mr_helper_recentactivity extends mr_helper_abstract {
    /**
     * Get recent activity for course(s)
     *
     * The passed course(s) will get populated with recent activity
     * and returned.  EG: $course->recentactivity = array(...of activity...);
     *
     * @param int $timestart Look for activity after this time
     * @param array|stdClass $courses An array of course objects or a single course object
     * @param mixed $otheruser The user who will view the list of activity.  If NULL, then currently logged in user is used.
     * @throws Exception
     * @return array
     */
    public function direct($timestart, $courses, $otheruser = NULL) {
        global $DB, $CFG, $USER;

        $recentactivity = array();

        // Param checks
        if (is_null($timestart) or empty($courses)) {
            return $recentactivity;
        }
        if (!is_array($courses)) {
            $courses = array($courses->id => $courses);
        }
        if (!is_null($otheruser)) {
            $currentuser = clone($USER);
            enrol_check_plugins($otheruser);
            \core\session\manager::set_user($otheruser);
        } else {
            $currentuser = false;
        }
        $timestart = clean_param($timestart, PARAM_INT);

        if ($allmods = $DB->get_records('modules')) {
            foreach ($allmods as $mod) {
                if ($mod->visible) {
                    $modnames[$mod->name] = get_string('modulename', $mod->name);
                }
            }
        } else {
            throw new Exception('No modules are installed!');
        }

        // Gather recent activity
        foreach ($courses as $course) {
            $modinfo       = get_fast_modinfo($course);
            $viewfullnames = has_capability('moodle/site:viewfullnames', context_course::instance($course->id));
            $activities    = array();
            $index         = 0;

            $recentactivity[$course->id] = $course;
            $recentactivity[$course->id]->recentactivity = array();

            $logs = $DB->get_records_sql("SELECT l.*, u.firstname, u.lastname, u.picture
                                            FROM {log} l
                                 LEFT OUTER JOIN {user} u ON l.userid = u.id
                                           WHERE time > ?
                                             AND course = ?
                                             AND module = 'course'
                                             AND (action = 'add mod' OR action = 'update mod' OR action = 'delete mod')
                                        ORDER BY id ASC", array($timestart, $course->id));

            if ($logs) {
                $changelist = array();
                $newgones   = array(); // added and later deleted items
                foreach ($logs as $key => $log) {
                    $info = explode(' ', $log->info);

                    // Labels are ignored in recent activity
                    if ($info[0] == 'label') {
                        continue;
                    }
                    // Check for incorrect entry
                    if (count($info) != 2) {
                        continue;
                    }

                    $modname    = $info[0];
                    $instanceid = $info[1];

                    $userinfo = new stdClass;
                    $userinfo->id       = $log->userid;
                    $userinfo->userid   = $log->userid;
                    $userinfo->fullname = '';
                    $userinfo->picture  = $log->picture;

                    if (!empty($log->firstname) and !empty($log->lastname)) {
                        $a = new stdClass;
                        $a->fullname = fullname($log, $viewfullnames);
                        $a->modname  = get_string('modulename', $modname);
                        $userinfo->fullname = $a->fullname;
                    } else {
                        $a = false;
                    }

                    if ($log->action == 'delete mod') {
                        // unfortunately we do not know if the mod was visible
                        if (!array_key_exists($log->info, $newgones)) {
                            if ($a) {
                                $strdeleted = get_string('deletedactivity', 'local_mr', $a);
                            } else {
                                $strdeleted = get_string('deletedactivity', 'moodle', get_string('modulename', $modname));
                            }
                            $changelist[$log->info] = (object) array(
                                'cmid' => $log->cmid,
                                'type' => $modname,
                                'name' => '',
                                'action' => 'delete',
                                'timestamp' => $log->time,
                                'description_html' => $strdeleted,
                                'description_text' => $strdeleted,
                                'user' => $userinfo,
                            );
                        }
                    } else {
                        if (!isset($modinfo->instances[$modname][$instanceid])) {
                            if ($log->action == 'add mod') {
                                // do not display added and later deleted activities
                                $newgones[$log->info] = true;
                            }
                            continue;
                        }
                        $cm = $modinfo->instances[$modname][$instanceid];
                        if (!$cm->uservisible) {
                            continue;
                        }

                        if ($log->action == 'add mod') {
                            if ($a) {
                                $stradded = get_string('addedactivity', 'local_mr', $a);
                            } else {
                                $stradded = get_string('added', 'moodle', get_string('modulename', $modname));
                            }
                            $changelist[$log->info] = (object) array(
                                'cmid' => $cm->id,
                                'type' => $modname,
                                'name' => $cm->name,
                                'action' => 'add',
                                'timestamp' => $log->time,
                                'description_html' => "$stradded:<br /><a href=\"$CFG->wwwroot/mod/$cm->modname/view.php?id={$cm->id}\">".format_string($cm->name, true).'</a>',
                                'description_text' => "$stradded: ".format_string($cm->name, true),
                                'user' => $userinfo,
                            );
                        } else if ($log->action == 'update mod' and empty($changelist[$log->info])) {
                            if ($a) {
                                $strupdated = get_string('updatedactivity', 'local_mr', $a);
                            } else {
                                $strupdated = get_string('updated', 'moodle', get_string('modulename', $modname));
                            }
                            $changelist[$log->info] = (object) array(
                                'cmid' => $cm->id,
                                'type' => $modname,
                                'name' => $cm->name,
                                'action' => 'update',
                                'timestamp' => $log->time,
                                'description_html' => "$strupdated:<br /><a href=\"$CFG->wwwroot/mod/$cm->modname/view.php?id={$cm->id}\">".format_string($cm->name, true).'</a>',
                                'description_text' => "$strupdated: ".format_string($cm->name, true),
                                'user' => $userinfo,
                            );
                        }
                    }
                }
                // Add to main recentactivity array
                $recentactivity[$course->id]->recentactivity = array_values($changelist);
            }

            foreach ($modinfo->cms as $cm) {
                if (!$cm->uservisible) {
                    continue;
                }
                $lib = "$CFG->dirroot/mod/$cm->modname/lib.php";
                if (file_exists($lib)) {
                    require_once($lib);

                    $get_recent_mod_activity = "{$cm->modname}_get_recent_mod_activity";
                    if (function_exists($get_recent_mod_activity)) {
                        $get_recent_mod_activity($activities, $index, $timestart, $course->id, $cm->id, 0, 0);
                    }
                }
            }

            foreach ($activities as $activity) {
                $print_recent_mod_activity = "{$activity->type}_print_recent_mod_activity";

                if (function_exists($print_recent_mod_activity)) {
                    ob_start();
                    $print_recent_mod_activity($activity, $course->id, true, $modnames, true);
                    $description = ob_get_contents();
                    ob_end_clean();

                    $activity->description_html = $description;
                    $activity->description_text = trim(strip_tags(str_replace(array('</td>', '</div>'), array(' </td>', ' </div>'), $description)));

                    if (empty($activity->timestamp)) {
                        $activity->timestamp = 0;
                    }
                    $recentactivity[$course->id]->recentactivity[] = $activity;
                }
            }
        }

        // Sort recent activity
        foreach ($recentactivity as $courseid => $course) {
            uasort($course->recentactivity, create_function('$a, $b', 'return ($a->timestamp == $b->timestamp) ? 0 : (($a->timestamp > $b->timestamp) ? -1 : 1);'));
            $recentactivity[$courseid]->recentactivity = array_values($course->recentactivity);
        }
        if ($currentuser !== false) {
            \core\session\manager::set_user($currentuser);
        }
        return $recentactivity;
    }
}