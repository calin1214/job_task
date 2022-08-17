<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use  SoftDeletes;

    protected $table = 'appointment';

    protected $fillable = array('*');

    protected $dates = ['created_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }

    /**
     * Get all appointments for selected Month
     * @param $selectedDate
     */
    public static function getAppointmentsForSelectedMonth($selectedDate)
    {
        $selectedDateList = explode("-", $selectedDate);
        if (empty($selectedDateList[0]) || empty($selectedDateList[1])) {
            throw new \Exception('There was an error with the date we got. Please contact an administrator!');
        }

        $selectedYear = $selectedDateList[0];
        $selectedMonth = $selectedDateList[1];

        $appointments = Appointment::select('appointment_on')->where('appointment_on', '>=', date('Y-m-d'))
            ->where('appointment_on', 'like', "{$selectedYear}-%")
            ->where('appointment_on', 'like', "%-{$selectedMonth}-%")
            ->get();

        $appointmentsListByDay = [];
        foreach ($appointments as $appointment) {
            $appointmentDateTime = explode(" ", $appointment->appointment_on);
            // one hour and half
            $timeStep = 5400;
            $startTime = strtotime($appointmentDateTime[1]) - $timeStep;
            $endTime = strtotime($appointmentDateTime[1]) + $timeStep;
            $appointmentsListByDay[$appointmentDateTime[0]][] = "$startTime - $endTime";
        }

        return $appointmentsListByDay;
    }

    /**
     * Get free intervals for each selected day
     * @param $appointments
     * @param $selectedDate
     */
    public static function getFreeTimeIntervals($appointments, $selectedDate)
    {
        $timeList = [];
        $startTime = '9:00';
        $endTime = '21:00';
        $pauseIntervalTimeStampStart = '13:00';
        $pauseIntervalTimeStampEnd = '15:30';
        // half hour time interval to choose
        $timeStep = 1800;
        // one hour one session duration
        $sessionDuration = 3600;

        $startTimeStamp = strtotime($startTime);
        while ($startTimeStamp <= strtotime($endTime) - $sessionDuration) {
            $busyInterval = false;

            // Pause time
            if (
                $startTimeStamp > strtotime($pauseIntervalTimeStampStart) - $sessionDuration &&
                $startTimeStamp < strtotime($pauseIntervalTimeStampEnd)
            ) {
                $startTimeStamp += $timeStep;
                continue;
            }

            // Busy time interval
            if (!empty($appointments[$selectedDate])) {
                foreach ($appointments[$selectedDate] as $appointment) {
                    $timeInterval = explode(" - ", $appointment);

                    if ($startTimeStamp > (int)$timeInterval[0] && $startTimeStamp < (int)$timeInterval[1]) {
                        $busyInterval = true;
                        break;
                    }
                }
                if ($busyInterval) {
                    $startTimeStamp += $timeStep;
                    continue;
                }
            }

            $timeList[date("H:i", $startTimeStamp)] = date("H:i", $startTimeStamp);
            $startTimeStamp += $timeStep;
        }

        return $timeList;
    }

    public static function validateAppointment($data)
    {
        $appointments = Appointment::select('appointment_on')->where('appointment_on', 'like', "{$data->date} %")->get();

        foreach ($appointments as $appointment) {
            $appointmentDateTime = explode(" ", $appointment->appointment_on);
            // one hour and half
            $timeStep = 5400;
            $startTime = strtotime($appointmentDateTime[1]) - $timeStep;
            $endTime = strtotime($appointmentDateTime[1]) + $timeStep;

            if (strtotime($data->time) > $startTime && strtotime($data->time) < $endTime) {
                throw new \Exception('The date and time you want to choose was already taken. Please select another date');
            }
        }

        return true;
    }
}
