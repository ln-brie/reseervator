import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

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
                        info.jsEvent.preventDefault();
                        let title = document.getElementById('modalTitle');
                        let start = document.getElementById('modalStart');
                        let end = document.getElementById('modalEnd');

                        title.textContent = info.event.title;
                        start.textContent = getEventDate(info.event.start);
                        end.textContent = getEventDate(info.event.end);

                        info.el.click();
                    },
                    eventDidMount: function (data) {
                        data.el.setAttribute("data-bs-toggle", "modal");
                        data.el.setAttribute("data-bs-target", "#calendarModal");
                    },
                    plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin, bootstrap5Plugin],
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

                function getEventDate(date) {
                    let months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                    let minutes = date.getMinutes();
                    if (minutes < 10) {
                        minutes = '0' + minutes;
                    }
                    let stringDate = date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear() + ' ' + date.getHours() + ':' + minutes;

                    return stringDate;
                }

                calendar.render();
            });
    }

    check() {
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
