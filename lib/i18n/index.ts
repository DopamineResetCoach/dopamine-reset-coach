import { en } from './en';
import { nl } from './nl';
import { de } from './de';
import { fr } from './fr';
import { es } from './es';
import { pt } from './pt';
import { it } from './it';
import { pl } from './pl';
import { tr } from './tr';
import { ja } from './ja';
import { zh } from './zh';
import { ar } from './ar';
import type { Translations } from './types';

export type Locale = 'en' | 'nl' | 'de' | 'fr' | 'es' | 'pt' | 'it' | 'pl' | 'tr' | 'ja' | 'zh' | 'ar';

export const SUPPORTED_LOCALES: { code: Locale; name: string; flag: string; rtl?: boolean }[] = [
  { code: 'en', name: 'English', flag: '🇬🇧' },
  { code: 'nl', name: 'Nederlands', flag: '🇳🇱' },
  { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'es', name: 'Español', flag: '🇪🇸' },
  { code: 'pt', name: 'Português', flag: '🇧🇷' },
  { code: 'it', name: 'Italiano', flag: '🇮🇹' },
  { code: 'pl', name: 'Polski', flag: '🇵🇱' },
  { code: 'tr', name: 'Türkçe', flag: '🇹🇷' },
  { code: 'ja', name: '日本語', flag: '🇯🇵' },
  { code: 'zh', name: '中文', flag: '🇨🇳' },
  { code: 'ar', name: 'العربية', flag: '🇸🇦', rtl: true },
];

const translations: Record<Locale, Translations> = {
  en, nl, de, fr, es, pt, it, pl, tr, ja, zh, ar,
};

export function getTranslations(locale: Locale): Translations {
  return translations[locale] ?? translations.en;
}

export function isRTL(locale: Locale): boolean {
  return SUPPORTED_LOCALES.find(l => l.code === locale)?.rtl === true;
}

export type { Translations };
