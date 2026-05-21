import {EscPressStack} from "./utils";

export default function initLightbox() {

    const options = {
        ...{
            selectors: ['.entry-content a:has(img)', 'a:has(img.lightbox-open)', '.lightbox-open a:has(img)']
        },
        ...(window.wpsc_lightbox_options || {})
    };

    const escPress = new EscPressStack();

    window.wpsc_show_lightbox = showLightbox;

    // delegate click handler to the body for ability to catch dynamically added elements
    document.addEventListener('click', ev => {
        const target = ev.target;

        // check is target or parent is <a>
        let link;
        if (target.tagName.toLowerCase() === 'a') {
            link = target;
        } else if (target.parentNode && target.parentNode.tagName.toLowerCase() === 'a') {
            link = target.parentNode;
        }

        if (!link) {
            return;
        }

        if (!link.getAttribute('href').match(/(\.jpg|\.gif|\.jpeg|\.png|\.webp|\.svg)$/i)) {
            return;
        }

        if (!link.matches(options.selectors.join(', '))) {
            return;
        }

        ev.preventDefault();
        showLightbox(link);
    });

    function showLightbox(link) {
        const imgSrc = link.getAttribute('href');

        const image = createImage(imgSrc);
        const container = createContainer();
        const imageContainer = container.querySelector('.wpsc-lightbox-image');
        if (imageContainer) {
            imageContainer.appendChild(image);
        }

        setTimeout(() => {
            container.classList.add('wpsc-lightbox--open');
        }, 10);

        document.body.appendChild(container);
        document.body.classList.add('wpsc-lightbox-scroll');

        const closeOnEsc = escPress.pushAndGetName(() => {
            container.remove();
        });

        container.querySelectorAll('.wpsc-lightbox-body, .wpsc-lightbox-close').forEach(el => {
            el.addEventListener('click', ev => {
                container.classList.remove('wpsc-lightbox--open');
                document.body.classList.remove('wpsc-lightbox-scroll');
                setTimeout(() => {
                    container.remove();
                }, 400);
                escPress.off(closeOnEsc);
            });
        });
    }


    function createContainer() {
        const el = document.createElement('div');
        el.classList.add('wpsc-lightbox-container');
        el.setAttribute('role', 'dialog');
        el.setAttribute('tabindex', '-1');
        el.innerHTML = '<div class="wpsc-lightbox-background"></div>' +
            '<div class="wpsc-lightbox-inner">' +
            '<div class="wpsc-lightbox-close">' +
            '<svg width="17" height="17" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M-1-1h19v19h-19z"/><path fill="#fff" d="M8.485 7.071l-7.071-7.071-1.414 1.414 7.071 7.071-7.071 7.071 1.414 1.414 7.071-7.071 7.071 7.071 1.414-1.414-7.071-7.071 7.071-7.071-1.414-1.414-7.071 7.071z"/></svg>' +
            '</div>' +
            '<div class="wpsc-lightbox-body"><div class="wpsc-lightbox-image"></div></div>' +
            '<div class="wpsc-lightbox-caption"></div>' +
            '</div><!-- ./wpsc-lightbox-inner -->';

        return el;
    }

    function createImage(src) {
        const img = document.createElement('img');
        img.setAttribute('src', src);
        return img;
    }
}
