import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { url: String, label: String };

    delete(event) {
        event.preventDefault();
        if (window.confirm(`Etes-vous sûr·e de vouloir supprimer ${this.labelValue} ?`)) {
            location.assign(this.urlValue);
        }
    }
}
