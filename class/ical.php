<?php
################################################################################
#                                                           DATE : 01.08.2006  #
#  Short description :                                                         #
#                                                                              #
#       Internet Calendaring Specification Parser                              #
#      (http://www.ietf.org/rfc/rfc2445.txt)                                   #
#                                                                              #
#  Author info :                                                               #
#                                                                              #
#      ROMAN OŽANA (c) 2006                                                    #
#              ICQ (99950132)                                                  #
#              WWW (www.nabito.net)                                            #
#           E-mail (admin@nabito.net)                                          #
#                                                                              #
#  Country:                                                                    #
#                                                                              #
#      CZECH REPUBLIC                                                          #
#                                                                              #
#  Licence:                                                                    #
#                                                                              #
#      IF YOU WANT USE THIS CODE PLEASE CONTACT AUTHOR, Thank You              #
#                                                                              #
#                                                    it was written in SCITE   #
################################################################################
/**
 * This class Parse iCal standard. Is prepare to iCal feature version. Now is testing with apple iCal standard 2.0.
 * @author  Roman Ožana (Cz)
 * @copyright Roman Ožana (Cz)
 * @link www.nabito.net
 * @example
 *     $ical = new ical();
 *     $ical->parse('./calendar.ics');
 *     echo "<pre>";
 *     $ical->get_all_data();
 *  echo "</pre>";
 * @version 1.0
 * @todo get sort todo list
 */
class ical
{
    /**
     * Text in file
     *
     * @var string
     */
    public $file_text;
    /**
     * This array save iCalendar parse data
     *
     * @var array
     */
    public $cal;
    /**
     * Number of Events
     *
     * @var int
     */
    public $event_count;
    /**
     * Number of ToDos
     *
     * @var unknown_type
     */
    public $todo_count;
    /**
     * Help variable save last key (multiline string)
     *
     * @var unknown_type
     */
    public $last_key;

    /**
     * Read text file, icalender text file
     *
     * @param string $file
     * @return string
     */
    public function read_file($file)
    {
        $this->file = $file;
        $file_text = implode('', file($file)); //load file

        # next line withp preg_replace is because Mozilla Calendar save values wrong, like this ->

        #SUMMARY
        # :Text of sumary

        # good way is, for example in SunnyBird. SunnyBird save iCal like this example ->

        #SUMMARY:Text of sumary

        $file_text = preg_replace("/[\r\n]{1,} ([:;])/", '\\1', $file_text);

        return $file_text; // return all text
    }

    /**
     * Vraci pocet udalosti v kalendari
     *
     * @return unknown
     */
    public function get_event_count()
    {
        return $this->event_count;
    }

    /**
     * Vraci pocet ToDo uloh
     *
     * @return unknown
     */
    public function get_todo_count()
    {
        return $this->todo_count;
    }

    /**
     * Prekladac kalendare
     *
     * @param mixed $text
     * @return unknown
     */
    public function parse($text)
    {
        $this->cal = []; // new empty array

        $this->event_count = -1;

        // read FILE text
        $this->file_text = $text;

        $this->file_text = explode("\n", $this->file_text);

        // is this text vcalendar standart text ? on line 1 is BEGIN:VCALENDAR
        //if (!stristr($this->file_text[0],'BEGIN:VCALENDAR')) return 'error not VCALENDAR';

        foreach ($this->file_text as $text) {
            $text = trim($text); // trim one line
            if (!empty($text)) {
                // get Key and Value VCALENDAR:Begin -> Key = VCALENDAR, Value = begin
                list($key, $value) = $this->retun_key_value($text);

                switch ($text) { // search special string
                    case 'BEGIN:VTODO':
                        $this->todo_count = $this->todo_count + 1; // new todo begin
                        $type = 'VTODO';
                        break;
                    case 'BEGIN:VEVENT':
                        $this->event_count = $this->event_count + 1; // new event begin
                        $type = 'VEVENT';
                        break;
                    case 'BEGIN:VCALENDAR': // all other special string
                    case 'BEGIN:DAYLIGHT':
                    case 'BEGIN:VTIMEZONE':
                    case 'BEGIN:STANDARD':
                        $type = $value; // save tu array under value key
                        break;
                    case 'END:VTODO': // end special text - goto VCALENDAR key
                    case 'END:VEVENT':

                    case 'END:VCALENDAR':
                    case 'END:DAYLIGHT':
                    case 'END:VTIMEZONE':
                    case 'END:STANDARD':
                        $type = 'VCALENDAR';
                        break;
                    default: // no special string
                        $this->add_to_array($type, $key, $value); // add to array
                        break;
                }
            }
        }

        return $this->cal;
    }

    /**
     * Add to $this->ical array one value and key. Type is VTODO, VEVENT, VCALENDAR ... .
     *
     * @param string $type
     * @param string $key
     * @param string $value
     */
    public function add_to_array($type, $key, $value)
    {
        // die("$type, $key, $value");
        if (false == $key) {
            $key = $this->last_key;
            switch ($type) {
                case 'VEVENT':$value = $this->cal[$type][$this->event_count][$key] . $value;
                    break;
                case 'VTODO':$value = $this->cal[$type][$this->todo_count][$key] . $value;
                    break;
            }
        }

        if (('DTSTAMP' == $key) or ('LAST-MODIFIED' == $key) or ('CREATED' == $key)) {
            $value = $this->ical_date_to_unix($value);
        }

        if ('RRULE' == $key) {
            $value = $this->ical_rrule($value);
        }

        if (mb_stristr($key, 'DTSTART') or mb_stristr($key, 'DTEND')) {
            list($key, $value) = $this->ical_dt_date($key, $value);
            // var_export($value);
            // die("$key, $value");
        }

        switch ($type) {
            case 'VTODO':
                $this->cal[$type][$this->todo_count][$key] = $value;
                break;
            case 'VEVENT':
                $this->cal[$type][$this->event_count][$key] = $value;
                break;
            default:
                $this->cal[$type][$key] = $value;
                break;
        }
        $this->last_key = $key;
    }

    /**
     * Parse text "XXXX:value text some with : " and return array($key = "XXXX", $value="value");
     *
     * @param unknown_type $text
     * @return unknown
     */
    public function retun_key_value($text)
    {
        preg_match("/([^:]+)[:]([\w\W]+)/", $text, $matches);
        if (empty($matches)) {
            return [false, $text];
        }
        $matches = array_splice($matches, 1, 2);
        // die(var_export($matches));
        return $matches;
    }

    /**
     * Parse RRULE  return array
     *
     * @param unknown_type $value
     * @return unknown
     */
    public function ical_rrule($value)
    {
        $rrule = explode(';', $value);
        foreach ($rrule as $line) {
            $rcontent = explode('=', $line);
            $result[$rcontent[0]] = $rcontent[1];
        }

        return $result;
    }

    /**
     * Return Unix time from ical date time fomrat (YYYYMMDD[T]HHMMSS[Z] or YYYYMMDD[T]HHMMSS)
     *
     * @param mixed $icalDate
     * @return unknown
     */
    public function ical_date_to_unix($icalDate)
    {
        $icalDate = str_replace('T', '', $icalDate);
        $icalDate = str_replace('Z', '', $icalDate);

        $pattern = '/([0-9]{4})'; // 1: YYYY
        $pattern .= '([0-9]{2})'; // 2: MM
        $pattern .= '([0-9]{2})'; // 3: DD
        $pattern .= '([0-9]{0,2})'; // 4: HH
        $pattern .= '([0-9]{0,2})'; // 5: MM
        $pattern .= '([0-9]{0,2})/'; // 6: SS
        preg_match($pattern, $icalDate, $date);
        // die(var_export($date));
        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return false;
        }
        // Unix timestamps after 03:14:07 UTC 2038-01-19 might cause an overflow
        // if 32 bit integers are used.
        $timestamp = mktime((int) $date[4], (int) $date[5], (int) $date[6], (int) $date[2], (int) $date[3], (int) $date[1]);

        return $timestamp;
    }

    /**
     * Return unix date from iCal date format
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    public function ical_dt_date($key, $value)
    {
        $value = $this->ical_date_to_unix($value);

        // zjisteni TZID
        $temp = explode(';', $key);

        if (empty($temp[1])) { // neni TZID
            $data = str_replace('T', '', $data);

            return [$key, $value];
        }
        // pridani $value a $tzid do pole
        $key = $temp[0];
        $temp = explode('=', $temp[1]);
        $return_value[$temp[0]] = $temp[1];
        $return_value['unixtime'] = $value;

        return [$key, $return_value];
    }

    /**
     * Return sorted eventlist as array or false if calenar is empty
     *
     * @return unknown
     */
    public function get_sort_event_list()
    {
        $temp = $this->get_event_list();
        if (!empty($temp)) {
            usort($temp, [&$this, 'ical_dtstart_compare']);

            return $temp;
        }

        return false;
    }

    /**
     * Compare two unix timestamp
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function ical_dtstart_compare($a, $b)
    {
        return strnatcasecmp($a['DTSTART']['unixtime'], $b['DTSTART']['unixtime']);
    }

    /**
     * Return eventlist array (not sort eventlist array)
     *
     * @return array
     */
    public function get_event_list()
    {
        return $this->cal['VEVENT'];
    }

    /**
     * Return todo arry (not sort todo array)
     *
     * @return array
     */
    public function get_todo_list()
    {
        return $this->cal['VTODO'];
    }

    /**
     * Return base calendar data
     *
     * @return array
     */
    public function get_calender_data()
    {
        return $this->cal['VCALENDAR'];
    }

    /**
     * Return array with all data
     *
     * @return array
     */
    public function get_all_data()
    {
        return $this->cal;
    }
}
