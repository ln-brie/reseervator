import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import $ from 'jquery';

export default class extends Controller {

    static values = { events: Array, urlcheck: String };
    static targets = ["cal", "room", "start", "end"];

    connect() {

        this.get_calendar();
        this.check();
    }

    get_calendar() {
        let room = this.roomTarget.value;
        let startDate = this.startTarget.value;
        let initialDate = startDate.length > 0 ? new Date(startDate) : new Date();

        fetch('/calendar/ajax?id=' + room)
            .then(response => response.json())
            .then(data => {

                let reservations = [];
                data.reservations.forEach(element => {
                    reservations.push(element);

                });

                let cal = document.getElementById('room-calendar');
                let calendar = new Calendar(cal, {
                    eventClick: function (info) {
                        let start = new Date(info.event.start);
                        start = start.toLocaleString();
                        let end = new Date(info.event.end);
                        end = end.toLocaleString();
                        $('#staticBackdropLabel').html(info.event.title);
                        $('#modalBody').html(info.event.description);
                        $('#modalStart').html(start);
                        $('#modalEnd').html(end);
                        $('#calendarModal').show();
                        $('#closeModal').on('click', () => {
                            $('#calendarModal').hide();
                        })
                    },
                    plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, bootstrap5Plugin],
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },

                    initialDate: initialDate,
                    navLinks: true, // can click day/week names to navigate views
                    editable: true,
                    dayMaxEvents: true, // allow "more" link when too many events
                    events: reservations,
                    themeSystem: 'bootstrap5',
                    locale: 'fr',
                    buttonText: {
                        today: "auj.",
                        month: 'mois',
                        week: 'semaine',
                        day: 'jour',
                        list: 'liste'
                    }

                });

                calendar.render();
            });
    }

    check() {
        //TO-DO lors de l'update, exclure la réservation en cours d'update des blocages
        let room = this.roomTarget.value;
        let start = this.startTarget.value;
        let end = this.endTarget.value;

        let message = document.getElementById('message-validation');
        message.innerHTML = "";
        message.classList.remove('text-bg-warning');

        let button = document.getElementById('reservation_form_reserver');

        if (start !== '' || end !== '' || room !== null) {

            let url = this.urlcheckValue + '?room=' + room + '&start=' + start + '&end=' + end;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.validation == false) {
                        message.classList.add('text-bg-warning');
                        button.setAttribute('disabled', true);
                        message.innerHTML = "Le créneau demandé n'est pas disponible.";
                        message.classList.remove('d-none');
                    } else {
                        button.removeAttribute('disabled');
                    }
                });
        } else {
            button.setAttribute('disabled', true);
            message.innerHTML = "";
            message.classList.remove('text-bg-warning');
        }

    }
}
