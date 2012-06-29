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
 * @package local_mr
 * @author Mark Nielsen
 */

/**
 * Base availability condition class
 *
 * These classes contain logic for various criteria
 * that determine availability.
 *
 * Warning: these are called during the building
 * of course_modinfo instance.
 *
 * Warning, this class does get serialized and stored
 * into the database, so must be able to survive that.
 */
abstract class condition_base extends stdClass {
    /**
     * Generate an information string that informs the
     * user how to meet this condition.
     *
     * @abstract
     * @param course_modinfo $modinfo Must be set to proper course and user
     * @return string
     */
    abstract public function get_information(course_modinfo $modinfo);

    /**
     * Determine if the condition has been met by the user or not.
     *
     * @abstract
     * @param course_modinfo $modinfo Must be set to proper course and user
     * @param bool $grabthelot Performance flag - grab things in bulk if true
     * @return array 0 index is boolean for available, 1 index is information string - used as reason why not available
     */
    abstract public function has_been_met(course_modinfo $modinfo, $grabthelot = false);
}

/**
 * This condition is based on a date range.  It is only
 * satisfied if the current time is within the limits.
 */
class condition_daterange extends condition_base {
    /**
     * Available after this date.  If zero, ignored.
     *
     * @var int
     */
    protected $availablefrom = 0;

    /**
     * Available before this date.  If zero, ignored.
     *
     * @var int
     */
    protected $availableuntil = 0;

    /**
     * @param int $availablefrom Available after this date.  If zero, ignored.
     * @param int $availableuntil Available before this date.  If zero, ignored.
     */
    public function __construct($availablefrom, $availableuntil) {
        $this->availablefrom  = $availablefrom;
        $this->availableuntil = $availableuntil;
    }

    /**
     * @return int
     */
    public function get_availablefrom() {
        return $this->availablefrom;
    }

    /**
     * @return int
     */
    public function get_availableuntil() {
        return $this->availableuntil;
    }

    /**
     * {@inheritdoc}
     *
     * Generate a string to inform the user of the date limits.
     */
    public function get_information(course_modinfo $modinfo) {
        $from  = $this->get_availablefrom();
        $until = $this->get_availableuntil();

        if ($from and $until) {
            return get_string('requires_date_both', 'condition', (object) array(
                'from'  => $this->get_display_from(),
                'until' => $this->get_display_until()
            ));
        } else if ($from) {
            return get_string('requires_date', 'condition', $this->get_display_from());
        } else if ($until) {
            return get_string('requires_date_before', 'condition', $this->get_display_until());
        }
    }

    /**
     * {@inheritdoc}
     *
     * See if the current time is within our limits.
     */
    public function has_been_met(course_modinfo $modinfo, $grabthelot = false) {
        $from  = $this->get_availablefrom();
        $until = $this->get_availableuntil();

        if ($from and time() < $from) {
            return array(false, get_string('requires_date', 'condition', $this->get_display_from()));
        }
        if ($until and time() >= $until) {
            // But we don't display any information about this case. This is
            // because the only reason to set a 'disappear' date is usually
            // to get rid of outdated information/clutter in which case there
            // is no point in showing it...

            // Note it would be nice if we could make it so that the 'until'
            // date appears below the item while the item is still accessible,
            // unfortunately this is not possible in the current system. Maybe
            // later, or if somebody else wants to add it.
            return array(false, '');
        }
        return array(true, '');
    }

    /**
     * Human readable display of available from date.
     *
     * @return string
     */
    protected function get_display_from() {
        $userdate = usergetdate($this->availablefrom);
        $dateonly = $userdate['hours'] == 0 and $userdate['minutes'] == 0 and $userdate['seconds'] == 0;

        return userdate(
            $this->availablefrom, get_string($dateonly ? 'strftimedate' : 'strftimedatetime', 'langconfig')
        );
    }

    /**
     * Human readable display of available until date.
     *
     * @return string
     */
    protected function get_display_until() {
        $userdate = usergetdate($this->availableuntil);
        $dateonly = $userdate['hours'] == 23 and $userdate['minutes'] == 59 and $userdate['seconds'] == 59;

        return userdate(
            $this->availableuntil, get_string($dateonly ? 'strftimedate' : 'strftimedatetime', 'langconfig')
        );
    }
}

/**
 * This condition is based on a grade item and it's score
 */
class condition_grade extends condition_base {
    /**
     * The grade item ID
     *
     * @var int
     */
    protected $gradeitemid;

    /**
     * The minimum percent score needed in grade item
     * Null means this limit is ignored.
     *
     * @var int|null
     */
    protected $min;

    /**
     * The maximum percent score needed in grade item.
     * Null means this limit is ignored.
     *
     * @var int|null
     */
    protected $max;

    /**
     * The grade item's name
     *
     * @var string
     */
    protected $name;

    /**
     * @param int|object $gradeitem The grade item ID or object
     * @param int|null $min
     * @param int|null $max
     */
    public function __construct($gradeitem, $min, $max) {
        global $DB, $CFG;

        require_once($CFG->libdir.'/gradelib.php');

        if (is_object($gradeitem)) {
            $this->gradeitemid = $gradeitem->id;
        } else {
            $this->gradeitemid = $gradeitem;
            $gradeitem         = $DB->get_record('grade_items', array('id' => $this->gradeitemid), '*', MUST_EXIST);
        }
        if ($min === '') {
            $min = NULL;
        }
        if ($max === '') {
            $max = NULL;
        }
        $this->min = $min;
        $this->max = $max;

        $item       = new grade_item($gradeitem, false);
        $this->name = $item->get_name();
    }

    /**
     * @return int
     */
    public function get_gradeitemid() {
        return $this->gradeitemid;
    }

    /**
     * @return int|null
     */
    public function get_max() {
        return $this->max;
    }

    /**
     * @return int|null
     */
    public function get_min() {
        return $this->min;
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     *
     * Tell the user in a neutral way what they need to score.
     */
    public function get_information(course_modinfo $modinfo) {
        $min = $this->get_min();
        $max = $this->get_max();

        if (is_null($min) && is_null($max)) {
            $string = 'any';
        } else if (is_null($max)) {
            $string = 'min';
        } else if (is_null($min)) {
            $string = 'max';
        } else {
            $string = 'range';
        }
        return get_string('requires_grade_'.$string, 'condition', $this->get_name());
    }

    /**
     * {@inheritdoc}
     *
     * See if the user has scored within our limits.
     */
    public function has_been_met(course_modinfo $modinfo, $grabthelot = false) {
        $min   = $this->get_min();
        $max   = $this->get_max();
        $score = $this->get_cached_grade_score($modinfo, $this->get_gradeitemid(), $grabthelot);
        if ($score === false or (!is_null($min) and $score < $min) or (!is_null($max) and $score >= $max)) {
            return array(false, $this->get_information($modinfo));
        }
        return array(true, '');
    }

    /**
     * Obtains a grade score. Note that this score should not be displayed to
     * the user, because gradebook rules might prohibit that. It may be a
     * non-final score subject to adjustment later.
     *
     * @global object
     * @global object
     * @global object
     * @param course_modinfo $modinfo
     * @param int $gradeitemid Grade item ID we're interested in
     * @param bool $grabthelot If true, grabs all scores for current user on
     *   this course, so that later ones come from cache
     * @return float Grade score as a percentage in range 0-100 (e.g. 100.0
     *   or 37.21), or false if user does not have a grade yet
     */
    private function get_cached_grade_score(course_modinfo $modinfo, $gradeitemid, $grabthelot = false) {
        global $USER, $DB, $SESSION;

        $userid = $modinfo->get_user_id();

        if ($userid == 0 || $userid == $USER->id) {
            // For current user, go via cache in session
            if (empty($SESSION->gradescorecache) || $SESSION->gradescorecacheuserid != $USER->id) {
                $SESSION->gradescorecache       = array();
                $SESSION->gradescorecacheuserid = $USER->id;
            }
            if (!array_key_exists($gradeitemid, $SESSION->gradescorecache)) {
                if ($grabthelot) {
                    // Get all grades for the current course
                    $rs = $DB->get_recordset_sql("
SELECT
    gi.id,gg.finalgrade,gg.rawgrademin,gg.rawgrademax
FROM
    {grade_items} gi
    LEFT JOIN {grade_grades} gg ON gi.id=gg.itemid AND gg.userid=?
WHERE
    gi.courseid=?", array($USER->id, $modinfo->get_course_id()));
                    foreach ($rs as $record) {
                        $SESSION->gradescorecache[$record->id] =
                            is_null($record->finalgrade)
                                // No grade = false
                                ? false
                                // Otherwise convert grade to percentage
                                : (($record->finalgrade - $record->rawgrademin) * 100) /
                                ($record->rawgrademax - $record->rawgrademin);

                    }
                    $rs->close();
                    // And if it's still not set, well it doesn't exist (eg
                    // maybe the user set it as a condition, then deleted the
                    // grade item) so we call it false
                    if (!array_key_exists($gradeitemid, $SESSION->gradescorecache)) {
                        $SESSION->gradescorecache[$gradeitemid] = false;
                    }
                } else {
                    // Just get current grade
                    $record = $DB->get_record('grade_grades', array(
                        'userid'=> $USER->id, 'itemid'=> $gradeitemid));
                    if ($record && !is_null($record->finalgrade)) {
                        $score = (($record->finalgrade - $record->rawgrademin) * 100) /
                            ($record->rawgrademax - $record->rawgrademin);
                    } else {
                        // Treat the case where row exists but is null, same as
                        // case where row doesn't exist
                        $score = false;
                    }
                    $SESSION->gradescorecache[$gradeitemid] = $score;
                }
            }
            return $SESSION->gradescorecache[$gradeitemid];
        } else {
            // Not the current user, so request the score individually
            $record = $DB->get_record('grade_grades', array(
                'userid'=> $userid, 'itemid'=> $gradeitemid));
            if ($record && !is_null($record->finalgrade)) {
                $score = (($record->finalgrade - $record->rawgrademin) * 100) /
                    ($record->rawgrademax - $record->rawgrademin);
            } else {
                // Treat the case where row exists but is null, same as
                // case where row doesn't exist
                $score = false;
            }
            return $score;
        }
    }

    /**
     * For testing only. Wipes information cached in user session.
     *
     * @global object
     */
    static function wipe_session_cache() {
        global $SESSION;

        unset($SESSION->gradescorecache);
        unset($SESSION->gradescorecacheuserid);
    }
}

/**
 * This condition is based on the completion of a course activity
 */
class condition_completion extends condition_base {
    /**
     * The course module ID
     *
     * @var int
     */
    protected $cmid;

    /**
     * The required completion status
     *
     * This is set to a completion constant. EG:
     * COMPLETION_INCOMPLETE
     * COMPLETION_COMPLETE
     * COMPLETION_COMPLETE_PASS
     * COMPLETION_COMPLETE_FAIL
     *
     * @var int
     */
    protected $requiredcompletion;

    /**
     * @param int $cmid The course module ID
     * @param int $requiredcompletion The required completion status
     */
    public function __construct($cmid, $requiredcompletion) {
        $this->cmid               = $cmid;
        $this->requiredcompletion = $requiredcompletion;
    }

    /**
     * @return int
     */
    public function get_cmid() {
        return $this->cmid;
    }

    /**
     * @return int
     */
    public function get_requiredcompletion() {
        return $this->requiredcompletion;
    }

    /**
     * {@inheritdoc}
     *
     * Let the user know what condition must be met on which activity
     */
    public function get_information(course_modinfo $modinfo) {
        try {
            $cm = $modinfo->get_cm($this->get_cmid());

            return get_string(
                'requires_completion_'.$this->get_requiredcompletion(),
                'condition',
                $cm->name
            );
        } catch (moodle_exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     *
     * See if the user has met the completion condition
     */
    public function has_been_met(course_modinfo $modinfo, $grabthelot = false) {
        $completion = new completion_info($modinfo->get_course());

        // The completion system caches its own data
        $completiondata = $completion->get_data(
            (object) array('id' => $this->get_cmid()), $grabthelot, $modinfo->get_user_id(), $modinfo
        );

        $met = true;
        if ($this->get_requiredcompletion() == COMPLETION_COMPLETE) {
            // 'Complete' also allows the pass, fail states
            switch ($completiondata->completionstate) {
                case COMPLETION_COMPLETE:
                case COMPLETION_COMPLETE_FAIL:
                case COMPLETION_COMPLETE_PASS:
                    break;
                default:
                    $met = false;
            }
        } else {
            // Other values require exact match
            if ($this->get_requiredcompletion() != $completiondata->completionstate) {
                $met = false;
            }
        }
        $information = '';
        if (!$met) {
            $information = $this->get_information($modinfo);
        }
        return array($met, $information);
    }
}

/**
 * Given conditions, determine if the user has met those conditions.
 *
 * Also gives information about why the user has not met conditions
 * and verbose information about all of the conditions that must be met.
 */
class condition_info_controller {
    /**
     * @var array|condition_base[]
     */
    protected $conditions;

    /**
     * @var boolean
     */
    protected $processed = false;

    /**
     * @var boolean
     */
    protected $useravailable;

    /**
     * @var string
     */
    protected $useravailableinfo;

    /**
     * @var string
     */
    protected $conditioninfo;

    /**
     * @param array|condition_base[] $conditions The conditions to work with
     */
    public function __construct(array $conditions) {
        foreach ($conditions as $condition) {
            if (!$condition instanceof condition_base) {
                throw new coding_exception('Invalid condition class passed, must extend condition_base');
            }
        }
        $this->conditions = $conditions;
    }

    /**
     * Get conditions, filterable.
     *
     * This is the same as condition_availability::get_conditions
     * but allows for filtering down to a specific set of conditions.
     *
     * @param null $conditionclass Can set this to a condition class name to filter
     *                             conditions to a specific type.
     * @return array|condition_base[]
     */
    public function get_conditions($conditionclass = null) {
        if (!is_null($conditionclass)) {
            $conditions = array();
            foreach ($this->conditions as $condition) {
                if ($condition instanceof $conditionclass) {
                    $conditions[] = $condition;
                }
            }
            return $conditions;
        }
        return $this->conditions;
    }

    /**
     * Get if the conditions have been processed yet or not
     *
     * @return bool
     */
    public function get_processed() {
        return $this->processed;
    }

    /**
     * Process the conditions
     *
     * @param course_modinfo $modinfo Set to the proper course and user
     * @param bool $grabthelot Performance flag: if true then try to do things as efficient
     *                         as possible (works only for current user)
     * @return void
     */
    public function process_conditions(course_modinfo $modinfo, $grabthelot = false) {
        if (!$this->get_processed()) {
            $this->processed     = true;
            $this->useravailable = true;
            $useravailableinfo   = array();
            $conditioninfo       = array();

            foreach ($this->get_conditions() as $condition) {
                if ($info = $condition->get_information($modinfo)) {
                    $conditioninfo[] = $info;
                }

                list($met, $info) = $condition->has_been_met($modinfo, $grabthelot);

                if (!$met) {
                    $this->useravailable = false;
                    if (!empty($info)) {
                        $useravailableinfo[] = $info;
                    }
                }
            }
            $this->useravailableinfo = implode(' ', $useravailableinfo);
            $this->conditioninfo     = implode(' ', $conditioninfo);
        }
    }

    /**
     * Get if the conditions have been met by the user or not.
     *
     * Must call process_conditions before calling this.
     *
     * @return boolean
     */
    public function get_user_available() {
        $this->require_processed(__FUNCTION__);
        return $this->useravailable;
    }

    /**
     * Get information string about user availability.  Will
     * be blank when the user has met all conditions.
     *
     * Must call process_conditions before calling this.
     *
     * @return string
     */
    public function get_user_available_info() {
        $this->require_processed(__FUNCTION__);
        return $this->useravailableinfo;
    }

    /**
     * Get verbose condition restriction information.
     *
     * Must call process_conditions before calling this.
     *
     * @return string
     */
    public function get_condition_info() {
        $this->require_processed(__FUNCTION__);
        return $this->conditioninfo;
    }

    /**
     * Helper method, make sure we have processed the conditions
     *
     * @throws coding_exception
     * @param string $function The function being called
     * @return void
     */
    protected function require_processed($function) {
        if (!$this->get_processed()) {
            throw new coding_exception("Must call process_conditions before calling $function");
        }
    }
}
