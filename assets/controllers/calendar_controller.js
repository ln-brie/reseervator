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

    static values = { events: Array};
    static targets = ["cal"];

    connect() {

        let cal = document.getElementById('calendar');
        let calendar = new Calendar(cal, {
            eventClick: function (info) {
                alert('Event: ' + info.event.title);
                
                // change the border color just for fun
                info.el.style.borderColor = 'red';
            },
            plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin, bootstrap5Plugin],
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            
            initialDate: '2023-01-01',
            navLinks: true, // can click day/week names to navigate views
            editable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            events: this.eventsValue,
            timeFormat: 'H(:mm)',
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

        calendar.render();
    }
}
