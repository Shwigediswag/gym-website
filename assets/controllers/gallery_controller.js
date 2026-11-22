import { Controller } from '@hotwired/stimulus';

/**
 * Gallery filter chips (HANDOFF §4.3): Alles · De zaal · Een sessie · De mensen · 1-op-1.
 * Buttons carry data-category and aria-pressed; items carry data-category.
 */
export default class extends Controller {
    static targets = ['filter', 'item'];

    filter(event) {
        const category = event.currentTarget.dataset.category;

        this.filterTargets.forEach((button) => {
            button.setAttribute('aria-pressed', button === event.currentTarget ? 'true' : 'false');
        });
        this.itemTargets.forEach((item) => {
            item.hidden = category !== 'all' && item.dataset.category !== category;
        });
    }
}
