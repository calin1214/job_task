<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return view('dashboard')
            ->with('appointmentLabelText', 'Choose date and time to make an appointment(here is shown just available time)');
    }

    public function setAppointments(Request $request)
    {
        $data = json_decode($request->get('appointmentData'));

        $appointment = new Appointment();
        $appointment->user_id = Auth::id();
        $appointment->appointment_on = "{$data->date} {$data->time}";
        $appointment->created_at = date('Y-m-d H:i:s');
        if (!$appointment->save()) {
            throw new Exception('There was an error trying to make an appointment. Please contact the administrator!');
        }

        return "on {$data->date} at {$data->time}";
    }

    public function getFreeTime(Request $request)
    {
        try {
            $appointments = Appointment::getAppointmentsForSelectedMonth($request->get('date'));
            $timeList = Appointment::getFreeTimeIntervals($appointments, $request->get('date'));
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }

        return $timeList;
    }
}
