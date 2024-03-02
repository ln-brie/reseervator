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

    static values = { events: Array };

    connect() {

        let cal = document.getElementById('calendar');
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
            navLinks: true, // can click day/week names to navigate views
            editable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            events: this.eventsValue,
            themeSystem: 'bootstrap5',
            locale: 'fr',
            buttonText: {
                today: "aujourd'hui",
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
    }
}
