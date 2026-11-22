import { Controller } from '@hotwired/stimulus';

/**
 * Full-screen mobile menu (HANDOFF §6): open/close, focus trap, Escape, scroll lock.
 */
export default class extends Controller {
    static targets = ['panel', 'toggle'];

    connect() {
        this.beforeCache = () => this.close();
        document.addEventListener('turbo:before-cache', this.beforeCache);
    }

    disconnect() {
        document.removeEventListener('turbo:before-cache', this.beforeCache);
        this.unlock();
    }

    open() {
        if (!this.hasPanelTarget) return;
        this.panelTarget.hidden = false;
        this.toggleTargets.forEach((t) => t.setAttribute('aria-expanded', 'true'));
        document.documentElement.style.overflow = 'hidden';
        this.focusable()[0]?.focus();
    }

    close() {
        if (!this.hasPanelTarget || this.panelTarget.hidden) return;
        this.panelTarget.hidden = true;
        this.toggleTargets.forEach((t) => t.setAttribute('aria-expanded', 'false'));
        this.unlock();
        this.toggleTargets[0]?.focus();
    }

    keydown(event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            this.close();
            return;
        }
        if (event.key !== 'Tab') return;

        const items = this.focusable();
        if (items.length === 0) return;
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    focusable() {
        return Array.from(this.panelTarget.querySelectorAll('a[href], button:not([disabled])'));
    }

    unlock() {
        document.documentElement.style.overflow = '';
    }
}
