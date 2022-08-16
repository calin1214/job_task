<?php
$appointmentLabelText = 'Choose date and time to make an appointment';
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
?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="appointment-page" style="margin: 40px;">
        <div class="row">
            <div class="col-4">
                <div class="appointment-calendar"></div>
            </div>
            <div class="col-4 justify-content-center align-items-center" style="display: grid">
                <div class="appointment-time">
                    <label for="time_select_id">Choose the time</label>
                    <select id="time_select_id" onchange="appointmentDataFunction.saveTime()">
                        @foreach($timeList as $key => $time)
                            <option value="{{$key}}">{{$time}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label id="appointment_label">
                        {{$appointmentLabelText}}
                    </label>
                </div>
                <div>
                    <button id="save_appointment_btn" class="btn btn-md btn-success" disabled="disabled"
                            onclick="makeAppointment()">
                        Make appointment
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let appointmentCalendar = drawCalendar()
        appointmentCalendar.init();

        disableHolidayDays()
    });

    /**
     * Get and draw the calendar
     */
    function drawCalendar() {
        return new VanillaCalendar('.appointment-calendar', {
            date: {
                min: $.datepicker.formatDate('yy-mm-dd', new Date()),
                max: '2037-12-31',
            },

            settings: {
                lang: 'en',
                range: {
                    disabled: ['2022-08-17', '2022-08-19'],
                },
            },

            actions: {
                clickDay(event, date) {
                    disableHolidayDays()
                    appointmentDataFunction.saveDate(date[0])
                },
                clickMonth() {
                    disableHolidayDays()
                },
                clickYear() {
                    disableHolidayDays()
                },
            },

            popups: {
                '2022-08-18': {
                    modifier: 'bg-red',
                    html: 'Meeting at 9:00 PM',
                },
            }
        });
    }

    /**
     * Disable the holiday days from calendar
     */
    function disableHolidayDays() {
        // USED 'setTimeout' FUNCTION, TO MAKE SURE THE FUNCTION IS EXECUTED AFTER CALENDAR DATA WAS LOADED IN DOM
        // OTHERWISE WE COULD DO NOT HAVE YET 'vanilla-calendar-day__btn_weekend' CLASS
        setTimeout(function () {
            $('.vanilla-calendar-day__btn_weekend').addClass('vanilla-calendar-day__btn_disabled').removeClass('vanilla-calendar-day__btn_weekend')

            $('.vanilla-calendar-arrow').on("click", function () {
                disableHolidayDays()
            })
        }, 0)
    }

    /**
     * Save data to a variable
     */
    let appointmentDataFunction = (function saveAppointmentData() {
        let appointmentData = {
            date: undefined,
            time: undefined
        }

        function saveDate(date) {
            appointmentData.date = date || undefined
            checkAppointmentData()
            console.log(appointmentData)
        }

        function saveTime() {
            appointmentData.time = $('#time_select_id').val() || undefined
            checkAppointmentData()
            console.log(appointmentData)
        }

        function checkAppointmentData() {
            if (appointmentData.date === undefined || appointmentData.time === undefined) {
                $('#save_appointment_btn').attr('disabled', true)
                $('#appointment_label').text('<?php echo $appointmentLabelText ?>')
            } else {
                $('#save_appointment_btn').attr('disabled', false)
                $('#appointment_label').text('appointment on ' + appointmentData.date + ' at ' + appointmentData.time)
            }
        }

        return {
            saveDate,
            saveTime
        }
    })()

    /**
     * Confirm the appointment
     */
    function makeAppointment() {

    }
</script>
