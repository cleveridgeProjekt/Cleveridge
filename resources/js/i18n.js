import { createI18n } from 'vue-i18n'

import de from '../../locales/de.json'
import en from '../../locales/en.json'
import es  from '../../locales/es.json'
import ukr from '../../locales/ukr.json'

const normalize = (code) => {
    const c = (code || '').toLowerCase()
    if (c.startsWith('uk')) return 'ukr'
    if (c.startsWith('es')) return 'es'
    if (c.startsWith('de')) return 'de'
    return 'en'
}

const stored = localStorage.getItem('locale')
const nav = normalize(navigator.language)
const locale = stored || nav || 'de'

const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale,
    fallbackLocale: 'en',
    messages: { de, en, es, ukr },
    missing: (loc, key) => console.warn(`[i18n] Missing key "${key}" for locale "${loc}"`)
})

export default i18n
