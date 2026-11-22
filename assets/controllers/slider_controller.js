import { Controller } from '@hotwired/stimulus';

/**
 * Horizontal scroll-snap strip (HANDOFF §6). Native swipe/scroll does the moving;
 * this adds prev/next, arrow keys, the dash indicator and optional autoplay.
 */
export default class extends Controller {
    static targets = ['track', 'slide', 'dot'];
    static values = { autoplay: { type: Number, default: 0 } };

    connect() {
        this.index = 0;
        this.onScroll = this.onScroll.bind(this);
        this.trackTarget.addEventListener('scroll', this.onScroll, { passive: true });
        this.update();

        if (this.autoplayValue > 0 && !this.reducedMotion) {
            this.pause = () => this.stop();
            this.element.addEventListener('pointerenter', this.pause);
            this.element.addEventListener('focusin', this.pause);
            this.timer = setInterval(() => this.go(this.index + 1 >= this.slideTargets.length ? 0 : this.index + 1), this.autoplayValue);
        }
    }

    disconnect() {
        this.trackTarget.removeEventListener('scroll', this.onScroll);
        this.stop();
        cancelAnimationFrame(this.frame);
    }

    prev(event) {
        event?.preventDefault();
        this.go(Math.max(0, this.index - 1));
    }

    next(event) {
        event?.preventDefault();
        this.go(Math.min(this.slideTargets.length - 1, this.index + 1));
    }

    go(index) {
        const slide = this.slideTargets[index];
        if (!slide) return;
        const track = this.trackTarget;
        const padding = parseFloat(getComputedStyle(track).scrollPaddingLeft) || 0;
        track.scrollTo({ left: slide.offsetLeft - padding, behavior: this.reducedMotion ? 'auto' : 'smooth' });
    }

    onScroll() {
        cancelAnimationFrame(this.frame);
        this.frame = requestAnimationFrame(() => this.update());
    }

    update() {
        const track = this.trackTarget;
        const slides = this.slideTargets;
        if (slides.length === 0) return;

        const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
        let nearest = 0;
        if (atEnd && track.scrollLeft > 0) {
            nearest = slides.length - 1;
        } else {
            const padding = parseFloat(getComputedStyle(track).scrollPaddingLeft) || 0;
            let best = Infinity;
            slides.forEach((slide, i) => {
                const distance = Math.abs(slide.offsetLeft - padding - track.scrollLeft);
                if (distance < best) {
                    best = distance;
                    nearest = i;
                }
            });
        }

        this.index = nearest;
        this.dotTargets.forEach((dot, i) => {
            dot.dataset.active = i === nearest ? 'true' : 'false';
        });
    }

    stop() {
        clearInterval(this.timer);
        this.timer = null;
    }

    get reducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }
}
