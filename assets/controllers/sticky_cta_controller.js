import { Controller } from '@hotwired/stimulus';

/**
 * Shows the sticking-out "Gratis kennismaking" CTA after a scroll threshold (HANDOFF §3).
 * The items are visible by default so the CTA still works without JavaScript.
 * It steps aside while an element marked [data-sticky-cta-hide] (the framed page CTA) is on screen,
 * so the same call to action is never shown twice on top of itself.
 */
export default class extends Controller {
    static targets = ['item'];
    static values = { threshold: { type: Number, default: 400 } };

    connect() {
        this.blockers = new Set();
        this.onScroll = this.onScroll.bind(this);
        window.addEventListener('scroll', this.onScroll, { passive: true });

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.blockers.add(entry.target);
                } else {
                    this.blockers.delete(entry.target);
                }
            });
            this.update();
        });
        document.querySelectorAll('[data-sticky-cta-hide]').forEach((el) => this.observer.observe(el));

        this.update();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
        this.observer?.disconnect();
        cancelAnimationFrame(this.frame);
    }

    onScroll() {
        cancelAnimationFrame(this.frame);
        this.frame = requestAnimationFrame(() => this.update());
    }

    update() {
        const visible = window.scrollY > this.thresholdValue && this.blockers.size === 0;
        this.itemTargets.forEach((item) => {
            item.dataset.visible = visible ? 'true' : 'false';
        });
    }
}
