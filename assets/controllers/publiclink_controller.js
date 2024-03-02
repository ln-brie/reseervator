import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static values = { link: String }

    copyToClipboard(event) {
        event.preventDefault();
        navigator.clipboard.writeText(this.linkValue);
        document.getElementById('base-info-message').textContent = "Copié !";
        setTimeout(() => {
            document.getElementById('base-info-message').textContent = '';
        }, 1500);


    }
}
