import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { url: String };

    delete(event) {
        event.preventDefault();
        if (window.confirm('Etes-vous sûr·e de vouloir supprimer cette réservation ?')) {
            location.assign(this.urlValue);
        }

    }
}
