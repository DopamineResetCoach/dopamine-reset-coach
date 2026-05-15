'use client';

import { useEffect } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { isRTL } from '@/lib/i18n';
import type { Locale } from '@/lib/i18n';

export default function DirectionProvider() {
  const language = useAppStore((s) => s.language);

  useEffect(() => {
    const locale = (language ?? 'en') as Locale;
    const dir = isRTL(locale) ? 'rtl' : 'ltr';
    document.documentElement.setAttribute('dir', dir);
    document.documentElement.setAttribute('lang', locale);
  }, [language]);

  return null;
}
