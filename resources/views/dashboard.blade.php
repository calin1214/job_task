<style>
    #set_flash_text {
        margin: 0;
        padding: 8px;
        color: white;
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My application') }}
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
                        <option value="0">Choose the time</option>
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
            <div class="col-4">
                <label class="mb-4" style="font-weight: bold">My Appointments</label>
                <div id="my_appointments_card" style="height: 220px; overflow-y: auto;">
                    @foreach($myAppointments as $appointment)
                        <div>
                            on {{explode(" ", $appointment->appointment_on)[0]}} at {{substr(explode(" ", $appointment->appointment_on)[1], 0, -3)}}
                        </div>
                        <hr>
                    @endforeach
                </div>
            </div>
        </div>
        <div id="set_flash_text" class="row mt-4">
        </div>
    </div>
</x-app-layout>

<script>
    let token = $('meta[name="csrf-token"]').attr('content');

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
                    disabled: ['2022-08-27', '2022-08-19'],
                },
            },

            actions: {
                clickDay(event, date) {
                    disableHolidayDays()
                    appointmentDataFunction.saveDate(date[0])
                    appointmentDataFunction.saveTime(true)
                    getFreeTime(date[0])
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
        }

        function saveTime(isUndefined = false) {
            appointmentData.time = !isUndefined && $('#time_select_id').val() !== '0' ? $('#time_select_id').val() : undefined
            checkAppointmentData()
        }

        function checkAppointmentData() {
            if (appointmentData.date === undefined || appointmentData.time === undefined) {
                $('#save_appointment_btn').attr('disabled', true)
                $('#appointment_label').text('{{$appointmentLabelText}}')
            } else {
                $('#save_appointment_btn').attr('disabled', false)
                $('#appointment_label').text('appointment on ' + appointmentData.date + ' at ' + appointmentData.time)
            }
        }

        function getData() {
            return appointmentData
        }

        return {
            saveDate,
            saveTime,
            getData
        }
    })()

    /**
     * Get free time for making an appointment for selected day
     */
    function getFreeTime(date) {
        $.ajax({
            type: "POST",
            url: 'get-free-time',
            data: {
                _token: token,
                date: date,
            },
            success: function (response) {
                let options = '<option value="0">Choose the time</option>';
                for (let x in response) {
                    options += '<option value="' + x + '">' + response[x] + '</option>'
                }

                $('#time_select_id').empty().append(options)
            },
            error: function (XHR) {
                let error = JSON.parse(XHR.responseText)
                $('#set_flash_text').empty().text(error.message).removeClass('bg-success').addClass('bg-danger')
            }
        });
    }

    /**
     * Confirm the appointment
     */
    function makeAppointment() {
        $.ajax({
            type: "POST",
            url: 'set-appointment',
            data: {
                _token: token,
                appointmentData: JSON.stringify(appointmentDataFunction.getData()),
            },
            success: function (response) {
                $('#time_select_id').empty().append('<option value="0">Choose the time</option>');
                $('.vanilla-calendar-day__btn_selected').removeClass('vanilla-calendar-day__btn_selected');
                $('#set_flash_text').empty().text('You have made an appointment ' + response + ' with success')
                    .removeClass('bg-danger').addClass('bg-success')
                $('#my_appointments_card').prepend('<hr>').prepend(response)

                // REMOVE THE SUCCESS MESSAGE FLASH AFTER SOME TIME
                // IN OUR CASE AFTER 5 SECONDS
                setTimeout(function () {
                    $('#set_flash_text').empty().removeClass('bg-success');
                }, 5000)
            },
            error: function (XHR) {
                let error = JSON.parse(XHR.responseText);
                $('#set_flash_text').empty().text(error.message).removeClass('bg-success').addClass('bg-danger');
            }
        });
    }
</script>
