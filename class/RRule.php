<?php

namespace XoopsModules\Tad_cal;

/*
require_once "rrule.php"
$rule = new \XoopsModules\Tad_cal\RRule('20111104T190000' , 'RRULE:FREQ=WEEKLY;UNTIL=20111230;INTERVAL=2;BYDAY=FR');
while($date = $rule->GetNext()){
echo "<p>".$date->Render()."</p>";
}

for($i=0;$i<=31;$i++){
$time=strtotime('2011-11-01')+86400*$i;
echo date("Y-m-d(w)",$time)."=".getWeekOfTheMonth($time)."<br>";
}
//算一下該日是該月的第幾週
function getWeekOfTheMonth($dateTimestamp=''){
$weekNum=date("W",$dateTimestamp)-date("W",strtotime(date("Y-m-01", $dateTimestamp)))+1;
return $weekNum ;
}
 */

/**
 * A Class for handling Events on a calendar which repeat
 *
 * Here's the spec, from RFC2445:
 *
recur      = "FREQ"=freq *(

; either UNTIL or COUNT may appear in a 'recur',
; but UNTIL and COUNT MUST NOT occur in the same 'recur'

( ";" "UNTIL" "=" enddate ) /
( ";" "COUNT" "=" 1*DIGIT ) /

; the rest of these keywords are optional,
; but MUST NOT occur more than once

( ";" "INTERVAL" "=" 1*DIGIT )          /
( ";" "BYSECOND" "=" byseclist )        /
( ";" "BYMINUTE" "=" byminlist )        /
( ";" "BYHOUR" "=" byhrlist )           /
( ";" "BYDAY" "=" bywdaylist )          /
( ";" "BYMONTHDAY" "=" bymodaylist )    /
( ";" "BYYEARDAY" "=" byyrdaylist )     /
( ";" "BYWEEKNO" "=" bywknolist )       /
( ";" "BYMONTH" "=" bymolist )          /
( ";" "BYSETPOS" "=" bysplist )         /
( ";" "WKST" "=" weekday )              /
( ";" x-name "=" text )
)

freq       = "SECONDLY" / "MINUTELY" / "HOURLY" / "DAILY"
/ "WEEKLY" / "MONTHLY" / "YEARLY"

enddate    = date
enddate    =/ date-time            ;An UTC value

byseclist  = seconds / ( seconds *("," seconds) )

seconds    = 1DIGIT / 2DIGIT       ;0 to 59

byminlist  = minutes / ( minutes *("," minutes) )

minutes    = 1DIGIT / 2DIGIT       ;0 to 59

byhrlist   = hour / ( hour *("," hour) )

hour       = 1DIGIT / 2DIGIT       ;0 to 23

bywdaylist = weekdaynum / ( weekdaynum *("," weekdaynum) )

weekdaynum = [([plus] ordwk / minus ordwk)] weekday

plus       = "+"

minus      = "-"

ordwk      = 1DIGIT / 2DIGIT       ;1 to 53

weekday    = "SU" / "MO" / "TU" / "WE" / "TH" / "FR" / "SA"
;Corresponding to SUNDAY, MONDAY, TUESDAY, WEDNESDAY, THURSDAY,
;FRIDAY, SATURDAY and SUNDAY days of the week.

bymodaylist = monthdaynum / ( monthdaynum *("," monthdaynum) )

monthdaynum = ([plus] ordmoday) / (minus ordmoday)

ordmoday   = 1DIGIT / 2DIGIT       ;1 to 31

byyrdaylist = yeardaynum / ( yeardaynum *("," yeardaynum) )

yeardaynum = ([plus] ordyrday) / (minus ordyrday)

ordyrday   = 1DIGIT / 2DIGIT / 3DIGIT      ;1 to 366

bywknolist = weeknum / ( weeknum *("," weeknum) )

weeknum    = ([plus] ordwk) / (minus ordwk)

bymolist   = monthnum / ( monthnum *("," monthnum) )

monthnum   = 1DIGIT / 2DIGIT       ;1 to 12

bysplist   = setposday / ( setposday *("," setposday) )

setposday  = yeardaynum
 *
 * At this point we are going to restrict ourselves to parts of the RRULE specification
 * seen in the wild.  And by "in the wild" I don't include within people's timezone
 * definitions.  We always convert time zones to canonical names and assume the lower
 * level libraries can do a better job with them than we can.
 *
 * We will concentrate on:
 *  FREQ=(YEARLY|MONTHLY|WEEKLY|DAILY)
 *  UNTIL=
 *  COUNT=
 *  INTERVAL=
 *  BYDAY=
 *  BYMONTHDAY=
 *  BYSETPOS=
 *  WKST=
 *  BYYEARDAY=
 *  BYWEEKNO=
 *  BYMONTH=
 *
 *
 * @package awl
 */
class RRule
{
    /**#@+
     * @access private
     */

    /** The first instance */
    public $_first;

    /** The current instance pointer */
    public $_current;

    /** An array of all the dates so far */
    public $_dates;

    /** Whether we have calculated any of the dates */
    public $_started;

    /** Whether we have calculated all of the dates */
    public $_finished;

    /** The rule, in all it's glory */
    public $_rule;

    /** The rule, in all it's parts */
    public $_part;

    /**#@-*/

    /**
     * The constructor takes a start date and an RRULE definition.  Both of these
     * follow the iCalendar standard.
     * @param mixed $start
     * @param mixed $rrule
     */
    public function __construct($start, $rrule)
    {
        //die("aa:".$start);
        $this->_first = new \XoopsModules\Tad_cal\IcalDate($start);
        $this->_finished = false;
        $this->_started = false;
        $this->_dates = [];
        $this->_current = -1;

        $this->_rule = preg_replace('/\s/m', '', $rrule);
        if ('RRULE:' === mb_substr($this->_rule, 0, 6)) {
            $this->_rule = mb_substr($this->_rule, 6);
        }

        //echo sprintf("<br> new RRule: Start: %s, RRULE: %s", $start->Render(), $this->_rule );

        $parts = explode(';', $this->_rule);
        $this->_part = ['INTERVAL' => 1];
        foreach ($parts as $k => $v) {
            list($type, $value) = explode('=', $v, 2);
            //echo sprintf("<br> Parts of %s explode into %s and %s", $v, $type, $value );
            $this->_part[$type] = $value;
        }

        // A little bit of validation
        if (!isset($this->_part['FREQ'])) {
            //echo sprintf("<br> RRULE MUST have FREQ=value (%s)", $rrule );
        }
        if (isset($this->_part['COUNT']) && isset($this->_part['UNTIL'])) {
            //echo sprintf("<br> RRULE MUST NOT have both COUNT=value and UNTIL=value (%s)", $rrule );
        }
        if (isset($this->_part['COUNT']) && (int) $this->_part['COUNT'] < 1) {
            //echo sprintf("<br> RRULE MUST NOT have both COUNT=value and UNTIL=value (%s)", $rrule );
        }
        if (!preg_match('/(YEAR|MONTH|WEEK|DAI)LY/', $this->_part['FREQ'])) {
            //echo sprintf("<br> RRULE Only FREQ=(YEARLY|MONTHLY|WEEKLY|DAILY) are supported at present (%s)", $rrule );
        }
        if ('YEARLY' === $this->_part['FREQ']) {
            $this->_part['INTERVAL'] *= 12;
            $this->_part['FREQ'] = 'MONTHLY';
        }
    }

    /**
     * Processes the array of $relative_days to $base and removes any
     * which are not within the scope of our rule.
     * @param mixed $base
     * @param mixed $relative_days
     * @return array
     */
    public function WithinScope($base, $relative_days)
    {
        //$base=array( '_text' => '20111104T190000', '_epoch' => 1320404400, '_yy' => 2011, '_mo' => 11, '_dd' => 4, '_hh' => 19, '_mi' => 0, '_ss' => 0, '_tz' => NULL, '_wkst' => NULL, )
        //$relative_days=array ( -1 => -1, );

        /*
        var_export($base);
        echo "<p>";
        var_export($relative_days);
        echo "</p>";
        echo "<hr>";
         */

        $ok_days = [];

        $ptr = $this->_current;

        //echo sprintf("<br> WithinScope: Processing list of %d days relative to %s", count($relative_days), $base->Render() );
        foreach ($relative_days as $day => $v) {
            $test = new \XoopsModules\Tad_cal\IcalDate($base);

            //找出該月天數
            $days_in_month = $test->DaysInMonth();

            //echo sprintf("<br> WithinScope: Testing for day %d based on %s, with %d days in month", $day, $test->Render(), $days_in_month );
            if ($day > $days_in_month) {
                $test->SetMonthDay($days_in_month);
                $test->AddDays(1);
                $day -= $days_in_month;
                $test->SetMonthDay($day);
            } elseif ($day < 1) {
                $test->SetMonthDay(1);
                $test->AddDays(-1);
                $days_in_month = $test->DaysInMonth();
                $day += $days_in_month;
                $test->SetMonthDay($day);
            } else {
                $test->SetMonthDay($day);
            }

            //echo sprintf("<br> WithinScope: Testing if %s is within scope", count($relative_days), $test->Render() );

            if (isset($this->_part['UNTIL']) && $test->GreaterThan($this->_part['UNTIL'])) {
                $this->_finished = true;

                return $ok_days;
            }

            // if ( $this->_current >= 0 && $test->LessThan($this->_dates[$this->_current]) ) continue;

            if (!$test->LessThan($this->_first)) {
                //echo sprintf("<br> WithinScope: Looks like %s is within scope", $test->Render() );
                $ok_days[$day] = $test;
                $ptr++;
            }

            if (isset($this->_part['COUNT']) && $ptr >= $this->_part['COUNT']) {
                $this->_finished = true;

                return $ok_days;
            }
        }

        return $ok_days;
    }

    /**
     * This is most of the meat of the RRULE processing, where we find the next date.
     * We maintain an
     */
    public function &GetNext()
    {
        if ($this->_current < 0) {
            $next = new \XoopsModules\Tad_cal\IcalDate($this->_first);
            $this->_current++;
        } else {
            $next = new \XoopsModules\Tad_cal\IcalDate($this->_dates[$this->_current]);
            $this->_current++;

            /**
             * If we have already found some dates we may just be able to return one of those.
             */
            if (isset($this->_dates[$this->_current])) {
                //echo sprintf("<br> GetNext: Returning %s, (%d'th)", $this->_dates[$this->_current]->Render(), $this->_current );
                return $this->_dates[$this->_current];
            }
            if (isset($this->_part['COUNT']) && $this->_current >= $this->_part['COUNT']) {
                // >= since _current is 0-based and COUNT is 1-based
                $this->_finished = true;
            }
        }

        if ($this->_finished) {
            $next = null;

            return $next;
        }

        $days = [];
        if (isset($this->_part['WKST'])) {
            $next->SetWeekStart($this->_part['WKST']);
        }

        if ('MONTHLY' === $this->_part['FREQ']) {
            //echo sprintf("<br> GetNext: Calculating more dates for MONTHLY rule" );
            $limit = 200;
            do {
                $limit--;
                do {
                    $limit--;
                    if ($this->_started) {
                        $next->AddMonths($this->_part['INTERVAL']);
                    } else {
                        $this->_started = true;
                    }
                } while (isset($this->_part['BYMONTH']) && $limit > 0 && !$next->TestByMonth($this->_part['BYMONTH']));

                if (isset($this->_part['BYDAY'])) {
                    $days = $next->GetMonthByDay($this->_part['BYDAY']);
                } elseif (isset($this->_part['BYMONTHDAY'])) {
                    $days = $next->GetMonthByMonthDay($this->_part['BYMONTHDAY']);
                } else {
                    $days[$next->_dd] = $next->_dd;
                }

                if (isset($this->_part['BYSETPOS'])) {
                    $days = $next->applyBySetPos($this->_part['BYSETPOS'], $days);
                }

                $days = $this->WithinScope($next, $days);
            } while ($limit && count($days) < 1 && !$this->_finished);
            //echo sprintf("<br> GetNext: Found %d days for MONTHLY rule", count($days) );
        } elseif ('WEEKLY' === $this->_part['FREQ']) {
            //echo sprintf("<br> GetNext: Calculating more dates for WEEKLY rule" );
            $limit = 200;

            do {
                $limit--;
                if ($this->_started) {
                    $next->AddDays($this->_part['INTERVAL'] * 7);
                } else {
                    $this->_started = true;
                }

                if (isset($this->_part['BYDAY'])) {
                    $days = $next->GetWeekByDay($this->_part['BYDAY'], false);
                    //die(var_export($days));
                } else {
                    $days[$next->_dd] = $next->_dd;
                }

                if (isset($this->_part['BYSETPOS'])) {
                    $days = $next->applyBySetPos($this->_part['BYSETPOS'], $days);
                }

                $days = $this->WithinScope($next, $days);
            } while ($limit && count($days) < 1 && !$this->_finished);

            //echo sprintf("<br> GetNext: Found %d days for WEEKLY rule", count($days) );
        } elseif ('DAILY' === $this->_part['FREQ']) {
            //echo sprintf("<br> GetNext: Calculating more dates for DAILY rule" );
            $limit = 100;
            do {
                $limit--;
                if ($this->_started) {
                    $next->AddDays($this->_part['INTERVAL']);
                }

                if (isset($this->_part['BYDAY'])) {
                    $days = $next->GetWeekByDay($this->_part['BYDAY'], $this->_started);
                } else {
                    $days[$next->_dd] = $next->_dd;
                }

                if (isset($this->_part['BYSETPOS'])) {
                    $days = $next->applyBySetPos($this->_part['BYSETPOS'], $days);
                }

                $days = $this->WithinScope($next, $days);
                $this->_started = true;
            } while ($limit && count($days) < 1 && !$this->_finished);

            //echo sprintf("<br> GetNext: Found %d days for DAILY rule", count($days) );
        }

        $ptr = $this->_current;
        foreach ($days as $k => $v) {
            $this->_dates[$ptr++] = $v;
        }

        if (isset($this->_dates[$this->_current])) {
            //echo sprintf("<br> GetNext: Returning %s, (%d'th)", $this->_dates[$this->_current]->Render(), $this->_current );
            return $this->_dates[$this->_current];
        }
        //echo sprintf("<br> GetNext: Returning null date" );
        $next = null;

        return $next;
    }
}

