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
        return $this->belongsTo(User::class)->orderBy('id');
    }

    public function user1()
    {
        return $this->hasOne(User::class, 'user_id', 'id')->orderBy('id');
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

        return Appointment::where('appointment_on', '>', date('Y-m-d H:i:s'))
            ->where('appointment_on', 'like', "{$selectedYear}-%")
            ->where('appointment_on', 'like', "%-{$selectedMonth}-%")
            ->get();
    }
}
