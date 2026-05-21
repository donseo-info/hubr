export function findParentsByClassName(el, className) {
    let p = el;
    while (true) {
        p = p.parentNode;
        if (p === document) {
            return null;
        }
        if (p.classList.contains(className)) {
            return p;
        }
    }
}

export function findParents(el, matchFn) {
    if (typeof matchFn !== 'function') {
        return null;
    }

    let p = el;
    while (true) {
        p = p.parentNode;
        if (p === document) {
            return null;
        }
        if (matchFn(el, p)) {
            return p;
        }
    }
}

export class EscPressStack {

    /**
     * Constructor
     */
    constructor() {
        this._stack = [];
        document.addEventListener('keyup', evt => {
            evt = evt || window.event;
            let isEscape = false;
            if ("key" in evt) {
                isEscape = (evt.key === "Escape" || evt.key === "Esc");
            } else {
                isEscape = (evt.keyCode === 27);
            }

            if (isEscape) {
                const item = this._stack.pop();
                if (item && item.callback && typeof item.callback === 'function') {
                    item.callback();
                }
            }
        });
    }

    /**
     *
     * @param {string} name
     * @param {function} callback
     * @return {EscPressStack}
     */
    push(name, callback) {
        this.off(name);
        this._stack.push({name, callback});
        return this;
    }

    /**
     *
     * @param {function} callback
     * @return {string}
     */
    pushAndGetName(callback) {
        const name = (Math.random() + 1).toString(36).substring(7) + new Date().valueOf();
        this.push(name, callback);
        return name;
    }

    /**
     *
     * @param {string} name
     * @return {EscPressStack}
     */
    off(name) {
        this._stack = this._stack.filter(item => {
            return item.name !== name;
        });
        return this;
    }
}
