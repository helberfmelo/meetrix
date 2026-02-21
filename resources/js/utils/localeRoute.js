const URL_TO_I18N_LOCALE = {
    pt_br: 'pt-BR',
    zh: 'zh',
    zh_cn: 'zh-CN',
    en: 'en',
    es: 'es',
    fr: 'fr',
    de: 'de',
    pt: 'pt',
    ja: 'ja',
    ko: 'ko',
    it: 'it',
    ru: 'ru',
    tr: 'tr',
    ar: 'ar',
};

const I18N_LOCALE_ALIASES = {
    'en-us': 'en',
    'en_us': 'en',
    'pt-br': 'pt-BR',
    'pt_br': 'pt-BR',
    'zh-cn': 'zh-CN',
    'zh_cn': 'zh-CN',
};

const URL_LOCALE_ALIASES = {
    'en-us': 'en',
    'en_us': 'en',
    'pt-br': 'pt_br',
    'zh-cn': 'zh_cn',
};

const I18N_TO_URL_LOCALE = Object.entries(URL_TO_I18N_LOCALE).reduce((acc, [urlLocale, i18nLocale]) => {
    acc[i18nLocale] = urlLocale;
    return acc;
}, {});

const pickFirst = (value) => (Array.isArray(value) ? value[0] : value);
const ensureLeadingSlash = (path) => (path && path.startsWith('/') ? path : `/${path || ''}`);

export const DEFAULT_I18N_LOCALE = 'en';
export const DEFAULT_URL_LOCALE = 'en';
export const SUPPORTED_URL_LOCALES = Object.keys(URL_TO_I18N_LOCALE);
export const LOCALE_ROUTE_PATTERN = [...SUPPORTED_URL_LOCALES, 'pt-br', 'zh-cn', 'en-us', 'en_us'].join('|');

const LOCALE_PREFIX_REGEX = new RegExp(`^/(${LOCALE_ROUTE_PATTERN})(?=/|$)`, 'i');

export function normalizeUrlLocale(value) {
    const rawLocale = pickFirst(value);
    if (!rawLocale) return null;

    const normalizedLocale = String(rawLocale).trim().toLowerCase();
    if (!normalizedLocale) return null;

    const aliasedLocale = URL_LOCALE_ALIASES[normalizedLocale] || normalizedLocale.replace(/-/g, '_');
    return SUPPORTED_URL_LOCALES.includes(aliasedLocale) ? aliasedLocale : null;
}

export function normalizeI18nLocale(value) {
    const rawLocale = pickFirst(value);
    if (!rawLocale) return null;

    const normalizedLocale = String(rawLocale).trim();
    if (!normalizedLocale) return null;

    if (I18N_TO_URL_LOCALE[normalizedLocale]) {
        return normalizedLocale;
    }

    const loweredLocale = normalizedLocale.toLowerCase();
    if (I18N_LOCALE_ALIASES[loweredLocale]) {
        return I18N_LOCALE_ALIASES[loweredLocale];
    }

    const hyphenLocale = loweredLocale.replace(/_/g, '-');
    if (I18N_LOCALE_ALIASES[hyphenLocale]) {
        return I18N_LOCALE_ALIASES[hyphenLocale];
    }

    const directMatch = Object.keys(I18N_TO_URL_LOCALE).find(
        (supportedLocale) => supportedLocale.toLowerCase() === hyphenLocale
    );

    return directMatch || null;
}

export function localeToUrlSegment(locale) {
    const normalizedLocale = normalizeI18nLocale(locale);
    return normalizedLocale ? I18N_TO_URL_LOCALE[normalizedLocale] : null;
}

export function urlSegmentToLocale(localeSegment) {
    const normalizedLocale = normalizeUrlLocale(localeSegment);
    return normalizedLocale ? URL_TO_I18N_LOCALE[normalizedLocale] : null;
}

export function extractLocaleSegment(pathname = '/') {
    const normalizedPathname = ensureLeadingSlash(pathname);
    const firstSegment = normalizedPathname.split('/').filter(Boolean)[0];
    return normalizeUrlLocale(firstSegment);
}

export function stripLocalePrefix(path = '/') {
    const normalizedPath = ensureLeadingSlash(path);
    const strippedPath = normalizedPath.replace(LOCALE_PREFIX_REGEX, '');
    return strippedPath || '/';
}

export function withLocalePrefix(path = '/', locale) {
    const targetLocale = localeToUrlSegment(locale) || DEFAULT_URL_LOCALE;
    const normalizedPath = ensureLeadingSlash(path);
    const pathWithoutLocale = stripLocalePrefix(normalizedPath);

    return pathWithoutLocale === '/' ? `/${targetLocale}` : `/${targetLocale}${pathWithoutLocale}`;
}

export function resolveLocalePreference({
    urlLocale,
    savedLocale,
    browserLocale,
} = {}) {
    return (
        urlSegmentToLocale(urlLocale) ||
        normalizeI18nLocale(savedLocale) ||
        normalizeI18nLocale(browserLocale) ||
        DEFAULT_I18N_LOCALE
    );
}
