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
        try {
            $appointments = Appointment::getAppointmentsForSelectedMonth(date('Y-m-d H:i:s'));
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }

        $appointmentLabelText = 'Choose date and time to make an appointment(here is shown just available time)';
        $timeList = [];

        for ($i = 9; $i <= 20; $i = $i + 0.5) {
            if ($i > 12 && $i < 15.5) {
                continue;
            }

            if (strpos($i, '.') === false) {
                $time = '00';
            } else {
                $time = '30';
            }
            $timeList[(int)$i . ":{$time}"] = (int)$i . ":{$time}";
        }

        return view('dashboard')
            ->with('appointments', $appointments)
            ->with('appointmentLabelText', $appointmentLabelText)
            ->with('timeList', $timeList);
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
}
