const awaitTimeout = delay => new Promise(resolve => setTimeout(resolve, delay));

class ProgressBar {
    constructor($markup) {
        this._$markup = $markup;
        this._progress = 0;
    }

    /**
     * @return jQuery {}
     */
    get $markup() {
        return this._$markup
    }

    // startFake(estimate, interval = 500) {
    //
    //     let steps = estimate / interval;
    //     let step = 0;
    //     let stepIncrement = 1;
    //
    //     console.log('steps', steps);
    //
    //     this._interval = setInterval(() => {
    //         const progress = step / steps;
    //
    //         if (progress > 0.4) {
    //             stepIncrement = 0.5;
    //         }
    //         if (progress > 0.7) {
    //             stepIncrement = 0.3;
    //         }
    //         if (progress > 0.8) {
    //             stepIncrement = 0.05;
    //         }
    //
    //         step += Math.random() * stepIncrement + stepIncrement / 2;
    //
    //         if (progress >= 1) {
    //             clearInterval(this._interval);
    //             this.setPercent(1)
    //             return;
    //         }
    //         console.log('step / steps', progress);
    //         this.setPercent(progress);
    //     }, interval);
    //
    //     return this;
    // }

    runFake(estimate) {
        let steps = estimate / 750; // 300 is average from [500, 1000] range
        let stepIncrement = 1;
        let currentStep = steps * this._progress;

        const run = () => {
            const timeout = (Math.random() * 500) + 500 // [500, 1000] range
            // console.log('timeout', timeout);
            this._timeout = setTimeout(() => {
                const progress = currentStep / steps;

                if (progress > 0.4) {
                    stepIncrement = 0.5;
                }
                if (progress > 0.7) {
                    stepIncrement = 0.3;
                }
                if (progress > 0.8) {
                    stepIncrement = 0.05;
                }
                if (progress > 0.9) {
                    stepIncrement = 0.001;
                }

                currentStep += Math.random() * stepIncrement + stepIncrement / 2;
                // console.log('progress', progress);
                this.setPercent(progress);
                if (progress < 1) {
                    run();
                }
            }, timeout);
        }

        run();

        return this;
    }

    stop() {
        clearTimeout(this._timeout);
        return this;
    }

    /**
     * @param {number}value 0-1 range
     * @return {ProgressBar}
     */
    setPercent(value) {
        value = Number(value);
        value = Math.max(0, Math.min(1, value));
        this._progress = value;

        this.$markup.find('.js-wpshop-settings-progress-bar').css('width', Number(this._progress * 100).toFixed(2) + '%');
        return this;
    }

    async destroy(delay) {
        await awaitTimeout(delay);
        this.$markup.remove();
    }
}

/**
 *
 * @param {jQuery} $
 * @param {string} text
 * @return {ProgressBar}
 */
export default function createProgressBar($, text) {
    let html = '<div class="wpshop-settings-progress-container js-wpshop-settings-progress-container">';
    if (text) {
        html = html.concat(
            '<div class="wpshop-settings-progress-container__description js-wpshop-settings-progress-container-description">',
            '<span>',
            text,
            '</span>',
            '</div>'
        );
    }
    html = html.concat(
        '<div class="wpshop-settings-progress js-wpshop-settings-progress">',
        '<div class="wpshop-settings-progress__bar js-wpshop-settings-progress-bar" style="width: 0"></div>',
        '</div>'
    );
    html = html.concat('</div><!-- /.wpshop-settings-progress-container -->');

    return new ProgressBar($(html));
}
