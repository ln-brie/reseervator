import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static values = {url: String};

    approve() {
        console.log('yup');
        let url = this.urlValue;

        fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.approved == true) {
                this.element.textContent = 'Approuvé !';
                this.element.setAttribute('disabled', 'true');
            }
        }) 
    }
}
