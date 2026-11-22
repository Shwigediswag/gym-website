import { Controller } from '@hotwired/stimulus';

/**
 * 1-2-3 indicator on the mobile sign-up form; follows the scroll position (HANDOFF §6).
 * A step is "current" once its fieldset has passed the upper third of the viewport.
 */
export default class extends Controller {
    static targets = ['step', 'dot', 'label'];
    static values = { label: String };

    connect() {
        this.onScroll = this.onScroll.bind(this);
        window.addEventListener('scroll', this.onScroll, { passive: true });
        window.addEventListener('resize', this.onScroll, { passive: true });
        this.update();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
        window.removeEventListener('resize', this.onScroll);
        cancelAnimationFrame(this.frame);
    }

    onScroll() {
        cancelAnimationFrame(this.frame);
        this.frame = requestAnimationFrame(() => this.update());
    }

    update() {
        const line = window.innerHeight / 3;
        let current = 0;
        this.stepTargets.forEach((step, i) => {
            if (step.getBoundingClientRect().top <= line) current = i;
        });

        this.dotTargets.forEach((dot, i) => {
            dot.dataset.active = i <= current ? 'true' : 'false';
        });
        if (this.hasLabelTarget && this.labelValue) {
            this.labelTarget.textContent = this.labelValue.replace('%n%', String(current + 1));
        }
    }
}
