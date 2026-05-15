'use client';

import { getDailyQuote } from '@/lib/quotes';
import { useT } from '@/hooks/useT';

export default function DailyQuote() {
  const t = useT();
  const quote = getDailyQuote();
  return (
    <div className="bg-[#5B8A5E] rounded-3xl px-5 py-5 mb-4 shadow-sm">
      <p className="text-white/70 text-sm font-medium uppercase tracking-widest mb-2">
        {t.quoteLabel}
      </p>
      <p className="text-white text-base font-medium leading-relaxed mb-2">
        "{quote.text}"
      </p>
      <p className="text-white/50 text-sm">— {quote.author}</p>
    </div>
  );
}
