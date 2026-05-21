/**
 * @version 1.0.1
 * @param {jQuery} $
 * @param {{}} options
 */
export function initSettings($, options) {
    initTabs();
    initColorPicker($('.js-wpshop-settings-color-picker'));

    $(document).on('click', '.js-wpshop-settings-tabs li', e => {
        const $el = $(e.currentTarget);
        if ($el.hasClass('active')) {
            return;
        }
        const tabId = $el.data('tab');
        switchActiveTab($el);
        showTabContent($(tabId));
        putInStorage(options.storage_key, tabId);
    });

    $(document).on('click', '.js-wpshop-settings-welcome-close', e => {
        $(e.target).parent().fadeOut(150);
        $.ajax(ajaxurl, {data: {action: options.actions.hide_welcome}});
    });

    $(document).on('click', '.js-wpshop-settings-remove-license', e => {
        e.preventDefault();
        if (confirm($(e.target).data('message'))) {
            $.ajax(ajaxurl, {data: {action: options.actions.remove_license}}).done(response => {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.data.map(item => item.message).join('\n'));
                }
            });
        }
    });

    $(document).on('change', 'input,select,textarea', function () {
        const $save = $('.js-wpshop-settings-container-save');
        if (!$save.data('handled_sticky')) {
            handleBasedOnPosition($save.find('button'), function () {
                $save.hide().css('position', 'sticky').slideDown(200);
            }, function () {
                $save.css('position', 'sticky');
            });
            $save.css('position', 'sticky');
            $save.data('handled_sticky', 1);
        }
    });

    let colorPickerChangeTimeout;

    function initColorPicker($el) {
        $el.wpColorPicker({
            'clear': (ev) => {
                const $el = $(ev.target);
                if (!$el.data('custom_trigger_change')) {
                    $(ev.target).trigger('change');
                }
                document.dispatchEvent(new CustomEvent('wpshop_settings_trigger_update_preview', {detail: {e: ev}}));
            },
            'change': (ev, ui) => {
                clearTimeout(colorPickerChangeTimeout);
                colorPickerChangeTimeout = setTimeout(function () {
                    $(ev.target).trigger('change');
                }, 200);
                document.dispatchEvent(new CustomEvent('wpshop_settings_color_picker_change', {
                    detail: {
                        e: ev,
                        ui: ui
                    }
                }));
            }
        });
    }

    function initTabs() {
        const trySwitch = hash => {
            try {
                const $tab = $('.js-wpshop-settings-tabs li[data-tab="' + hash + '"]');
                const $tabContent = $(hash);
                if ($tab.length && $tabContent.length) {
                    switchActiveTab($tab);
                    showTabContent($tabContent);
                    return true;
                }
            } catch (err) {
                console.warn(err);
            }
            return false;
        };

        // check url hash
        if (window.location.hash.length && trySwitch(window.location.hash)) {
            return;
        }

        // check local storage
        const storedTab = getFromStorage(options.storage_key);
        if (storedTab) {
            trySwitch(storedTab);
        }
    }

    /**
     * @param {jquery} $el
     */
    function switchActiveTab($el) {
        const $tabs = $el.parent('.js-wpshop-settings-tabs');
        $tabs.find('li').removeClass('active');
        $el.addClass('active');
    }

    /**
     * @param {jquery} $tabContent
     */
    function showTabContent($tabContent) {
        $('.js-wpshop-settings-tab').hide().promise().done(function () {
            $tabContent.fadeIn(250, function () {
                document.dispatchEvent(new CustomEvent('wpshop_settings:show_tab_content', {detail: {tab: $tabContent.attr('id')}}));
            });
        });
    }

    /**
     * @param {string} name
     * @param {string} value
     */
    function putInStorage(name, value) {
        if (canUseLocalStorage()) {
            localStorage.setItem(name, value);
        }
    }

    /**
     * @param name
     * @return {string|null}
     */
    function getFromStorage(name) {
        if (canUseLocalStorage()) {
            return localStorage.getItem(name);
        }
        return null;
    }

    /**
     * @return {boolean}
     */
    function canUseLocalStorage() {
        if (typeof localStorage === 'undefined') {
            console.warn('Unable to use local storage in current browser');
            return false;
        }
        return true;
    }

    /**
     *
     * @param {jquery} $el
     * @param {function} cbFullOut callback for use if the el is full out of view port
     * @param {function} cbPartialOut callback for use if the el is partially out of view port
     */
    function handleBasedOnPosition($el, cbFullOut, cbPartialOut) {
        if (!$el.length) {
            return;
        }
        const rect = $el[0].getBoundingClientRect();

        const viewPortBottom = (window.innerHeight || document.documentElement.clientHeight);

        if (rect.top > viewPortBottom) {
            cbFullOut();
        } else if (rect.bottom > viewPortBottom) {
            cbPartialOut();
        }
    }
}
