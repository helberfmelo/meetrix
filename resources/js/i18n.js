import { createI18n } from 'vue-i18n';

// Import translation files
import en from './locales/en.json';
import ptBR from './locales/pt-BR.json';
import es from './locales/es.json';
import fr from './locales/fr.json';
import de from './locales/de.json';
import pt from './locales/pt.json';
import zhCN from './locales/zh-CN.json';
import zh from './locales/zh.json';
import ja from './locales/ja.json';
import ko from './locales/ko.json';
import it from './locales/it.json';
import ru from './locales/ru.json';
import tr from './locales/tr.json';
import ar from './locales/ar.json';
import { extractLocaleSegment, resolveLocalePreference } from './utils/localeRoute';

const messages = {
    'en': en,
    'en-US': en,
    'pt-BR': ptBR,
    'es': es,
    'fr': fr,
    'de': de,
    'pt': pt,
    'zh': zh,
    'zh-CN': zhCN,
    'ja': ja,
    'ko': ko,
    'it': it,
    'ru': ru,
    'tr': tr,
    'ar': ar
};

// Determine default locale
const savedLocale = localStorage.getItem('locale');
const browserLocale = navigator.language;
const urlLocale = extractLocaleSegment(window.location.pathname);
const defaultLocale = resolveLocalePreference({
    urlLocale,
    savedLocale,
    browserLocale,
});

const i18n = createI18n({
    legacy: false, // Use Composition API
    locale: defaultLocale,
    fallbackLocale: 'en',
    messages,
});

export default i18n;
