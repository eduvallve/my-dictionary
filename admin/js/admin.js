/**
 * Admin JS code
*/

const cfgMdAdmin = {
    selectors: {
        general: {
            generalContainer: '#md-general',
            addLanguageSelect: '.md-new-language select',
            newLanguageBtn: '.md-add-language',
            languagesTableBody: 'table.md-language__table tbody',
            item: '.md-language__item',
            code: '.md-language__code',
            initialDefault: 'input[name="defaultLanguage"][checked]',
        },
        form: {
            msg: {
                success: '.md-msg__success'
            }
        }
    },
    classes: {
        general: {
            name: 'md-language__name',
            code: 'md-language__code',
            itemDefault: 'md-language__item--default',
            remove: 'md-language__action-remove',
        },
        form: {
            hide: 'md-msg__hide',
        },
        hidden: 'hidden',
    }
}

function isRadioDefaultLanguage(target) {
    return target.hasAttribute('type') &&
        target.getAttribute('type') === 'radio' &&
        target.hasAttribute('name') &&
        target.getAttribute('name') === 'defaultLanguage';
}

/** Basic class */
class BasicMdComponent {
    constructor(el) {
        this.el = el;
    }
}

/** Class for General Admin */
class GeneralAdmin extends BasicMdComponent {
    constructor(el) {
        super (el);
        this.init();
    }

    init() {
        this.setRefs();
        this.addEventListeners();
        this.hideActiveLanguagesFromSelect();
    }

    setRefs() {
        this.newLanguageBtn = this.el.querySelector(cfgMdAdmin.selectors.general.newLanguageBtn);
        this.newLanguageSelect = this.el.querySelector(cfgMdAdmin.selectors.general.addLanguageSelect);
        this.pluginUrl = this.el.dataset.pluginUrl;
        this.languagesTableBody = this.el.querySelector(cfgMdAdmin.selectors.general.languagesTableBody);
        this.initialDefaultLanguage = this.el.querySelector(cfgMdAdmin.selectors.general.initialDefault).value;
    }

    addEventListeners() {
        this.newLanguageBtn.addEventListener('click', this.addLanguage.bind(this));
        this.el.addEventListener('click', e => {
            const target = e.target;
            if (target.classList.contains(cfgMdAdmin.classes.general.remove)) {
                this.removeLanguage(target);
            } else if ( isRadioDefaultLanguage(target) ) {
                const items = this.el.querySelectorAll(cfgMdAdmin.selectors.general.item);
                items.forEach(item => {
                    item.classList.remove(cfgMdAdmin.classes.general.itemDefault);
                });
                target.closest(cfgMdAdmin.selectors.general.item).classList.add(cfgMdAdmin.classes.general.itemDefault);
            } else if (
                target.classList.contains(cfgMdAdmin.classes.general.name) ||
                target.classList.contains(cfgMdAdmin.classes.general.code)
                ) {
                target.closest(cfgMdAdmin.selectors.general.item).querySelector('input').click();
            }
        });
    }

    addLanguage() {
        if (this.newLanguageSelect.value.trim() !== '') {
            const filename = `${this.pluginUrl}general.template.language.row.php?md_code=${this.newLanguageSelect.value}`;
            this.fileGetContents(filename);
            this.hideLanguageOption(this.newLanguageSelect.value);
            this.newLanguageSelect.selectedIndex = 0;
            this.keepDefaultLanguageSelected();
        }
    }

    removeLanguage(target) {
        target.closest(cfgMdAdmin.selectors.general.item).remove();
        this.showLanguageOption(target.dataset.code);
    }

    hideLanguageOption(language) {
        this.newLanguageSelect.querySelector(`option[title="${language}"]`).classList.add(cfgMdAdmin.classes.hidden);
    }

    showLanguageOption(language) {
        this.newLanguageSelect.querySelector(`option[title="${language}"]`).classList.remove(cfgMdAdmin.classes.hidden);
    }

    keepDefaultLanguageSelected() {
        this.el.querySelector(`${cfgMdAdmin.selectors.general.item}.${cfgMdAdmin.classes.general.itemDefault} input`).setAttribute('checked',true);
    }

    hideActiveLanguagesFromSelect() {
        this.el.querySelectorAll(cfgMdAdmin.selectors.general.code).forEach(language => {
            this.hideLanguageOption(language.innerText);
        });
    }

    fileGetContents(filename) {
        fetch(filename).then((resp) => resp.text()).then(data => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const row = doc.querySelector('tbody').innerHTML;
            this.languagesTableBody.innerHTML += row;
        });
    }
}

/** Class for Success Messages  */
class SuccessMsg extends BasicMdComponent {
    constructor(el) {
        super (el);
        this.init();
    }

    init() {
        setTimeout(() => this.addHideClass(),3000);
    }

    addHideClass() {
        this.el.classList.add(cfgMdAdmin.classes.form.hide);
        setTimeout(() => {
            this.el.remove();
        },500);
    }
}

/**
 * Initialize first load class instances
 */

window.onload = function() {
    const generalTab = document.querySelector(cfgMdAdmin.selectors.general.generalContainer);
    if (generalTab) {
        new GeneralAdmin(generalTab);
    }

    document.querySelectorAll(cfgMdAdmin.selectors.form.msg.success).forEach(message => {
        new SuccessMsg(message);
    });
}