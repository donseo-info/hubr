import {findParentsByClassName} from "./utils";

let observer = null;
let hasDomLoaded = false;
let hasCustomListener = false;

export function initEmbedResponsive() {
    const options = {
        ...{
            baseClass: 'wpsc-ratio',
            elementSelectors: [
                '.wpsc-ratio-item',
                '.responsive-iframe iframe',
                '.entry-content iframe',
                '.entry-content video',
                // '.entry-content embed',
                // ' .entry-content object'
            ],
            ratioMap: [
                {suffix: '16x9', ratio: 1.77},
                {suffix: '21x9', ratio: 2.33},
                {suffix: '4x3', ratio: 1.33},
                {suffix: '1x1', ratio: 1}
            ],
            preventResponsiveFn: null
        },
        ...(window.wpsc_embed_responsive_options || {})
    };

    if (null === observer) {
        observer = new MutationObserver(mutations => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (!(node instanceof HTMLElement)) {
                        continue;
                    }
                    if (node.classList.contains(options.baseClass)) {
                        continue;
                    }
                    if (!node.matches(options.elementSelectors.join(', '))) {
                        continue;
                    }

                    walkElements([node]);
                }
            }
        });

        // observer.observe(document.body, {
        //     childList: true,
        //     subtree: true
        // });
    }

    if (!hasDomLoaded) {
        document.addEventListener("DOMContentLoaded", function () {
            walkElements()
        });
        hasDomLoaded = true;
    }

    // if (!hasCustomListener) {
    //     document.addEventListener('wpsc_core.embed_responsive.apply_responsive', function () {
    //         walkElements();
    //     });
    //     hasCustomListener = true;
    // }

    function walkElements(elements) {
        (elements || document.querySelectorAll(options.elementSelectors.join(', '))).forEach(item => {
            if (preventResponsive(item)) {
                return;
            }

            const parent = item.parentNode;

            if (!parent.classList.contains(options.baseClass)) {
                const el = document.createElement('div');
                el.classList.add(options.baseClass);
                el.classList.add(getAspectRatioClass(item));
                el.appendChild(item);
                parent.appendChild(el);
            }
        });
    }

    const preventResponsive = el => {
        const defaultFn = el => {
            if (el.classList.contains('wp-has-aspect-ratio') ||
                findParentsByClassName(el, 'wp-has-aspect-ratio') ||
                el.classList.contains('not-responsive') ||
                findParentsByClassName(el, 'not-responsive') ||
                el.classList.contains('wp-video-shortcode')
            ) {
                return true;
            }

            return false;
        };

        if (options.preventResponsiveFn === 'function') {
            return options.preventResponsiveFn(el, defaultFn);
        }

        return defaultFn(el);
    };


    function getAspectRatioClass(item) {
        let suffix = '16x9';
        let width, height;
        switch (item.tagName.toLowerCase()) {
            case 'iframe':
            case 'video':
                width = item.getAttribute('width') || item.offsetWidth;
                height = item.getAttribute('height') || item.offsetHeight;
                suffix = getAspectRatioSuffix(width, height);
                break;
            // case 'embed':
            // case 'object':
            default:
                break;
        }
        return options.baseClass + '-' + suffix;
    }


    function getAspectRatioSuffix(width, height) {
        if (!width || !height) {
            return ratioMap[0].suffix;
        }

        return findAspectRatio(width / height, options.ratioMap);
    }

    function findAspectRatio(num, map) {
        let curr = map[0];
        let diff = Math.abs(num - curr.ratio);
        for (let i = 0; i < map.length; i++) {
            let newDiff = Math.abs(num - map[i].ratio);
            if (newDiff < diff) {
                diff = newDiff;
                curr = map[i];
            }
        }
        return curr.suffix;
    }
}
