import { useAppStore } from '@/store/useAppStore';
import { getTranslations, isRTL } from '@/lib/i18n';
import type { Translations, Locale } from '@/lib/i18n';

export function useT(): Translations {
  const language = useAppStore(s => s.language) as Locale;
  return getTranslations(language || 'en');
}

export function useLocale(): { locale: Locale; rtl: boolean } {
  const language = useAppStore(s => s.language) as Locale;
  const locale = language || 'en';
  return { locale, rtl: isRTL(locale) };
}
