import { Controller } from '@hotwired/stimulus';

/**
 * Gallery lightbox (HANDOFF §6) on a native <dialog>: arrow keys, Escape (native), backdrop click.
 * Only steps through items that are currently visible (respects the gallery filter).
 * Shows the enlarged placeholder for now; set data-src on items to show real images later.
 */
export default class extends Controller {
    static targets = ['dialog', 'item', 'stage', 'caption', 'counter'];

    open(event) {
        this.opener = event.currentTarget;
        this.show(this.visibleItems.indexOf(event.currentTarget));
        if (!this.dialogTarget.open) this.dialogTarget.showModal();
    }

    close() {
        this.dialogTarget.close();
        this.opener?.focus();
    }

    backdrop(event) {
        if (event.target === this.dialogTarget) this.close();
    }

    prev(event) {
        event?.preventDefault();
        const count = this.visibleItems.length;
        this.show((this.index - 1 + count) % count);
    }

    next(event) {
        event?.preventDefault();
        this.show((this.index + 1) % this.visibleItems.length);
    }

    show(index) {
        const items = this.visibleItems;
        const item = items[index];
        if (!item) return;

        this.index = index;
        const label = item.dataset.label ?? '';
        const src = item.dataset.src;

        this.stageTarget.replaceChildren();
        if (src) {
            const image = document.createElement('img');
            image.src = src;
            image.alt = label;
            image.className = 'size-full object-contain';
            this.stageTarget.append(image);
            this.stageTarget.removeAttribute('aria-label');
        } else {
            this.stageTarget.textContent = label;
            this.stageTarget.setAttribute('aria-label', label);
        }
        this.captionTarget.textContent = label;
        this.counterTarget.textContent = `${index + 1} / ${items.length}`;
    }

    get visibleItems() {
        return this.itemTargets.filter((item) => !item.hidden);
    }
}
