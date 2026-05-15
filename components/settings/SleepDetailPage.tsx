'use client';

import { useAppStore } from '@/store/useAppStore';
import { getCheckInAverage, toLocalDateString } from '@/lib/scoring';
import { useT } from '@/hooks/useT';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip, CartesianGrid } from 'recharts';

function getSleepLabel(avg: number, t: ReturnType<typeof useT>): string {
  if (avg < 1.5) return t.sleepLabelTerrible;
  if (avg < 2.5) return t.sleepLabelPoor;
  if (avg < 3.5) return t.sleepLabelOK;
  if (avg < 4.5) return t.sleepLabelGood;
  return t.sleepLabelGreat;
}

function getSleepColor(avg: number): string {
  if (avg < 1.5) return '#D97070';
  if (avg < 2.5) return '#E4A85A';
  if (avg < 3.5) return '#C9955A';
  if (avg < 4.5) return '#5B8A5E';
  return '#3D6640';
}

function buildChartData(dailyLogs: Record<string, { checkIn?: { sleep: number } }>) {
  const out: { date: string; label: string; sleep: number | null }[] = [];
  const today = new Date();
  for (let i = 13; i >= 0; i--) {
    const d = new Date(today);
    d.setDate(today.getDate() - i);
    const key = toLocalDateString(d);
    const checkIn = dailyLogs[key]?.checkIn;
    out.push({
      date: key,
      label: `${d.getDate()}/${d.getMonth() + 1}`,
      sleep: checkIn ? checkIn.sleep : null,
    });
  }
  return out;
}

export default function SleepDetailPage({ onClose }: { onClose: () => void }) {
  const dailyLogs = useAppStore((s) => s.dailyLogs);
  const t = useT();

  const avg = getCheckInAverage(dailyLogs, 7, 'sleep');
  const chartData = buildChartData(dailyLogs);
  const hasData = chartData.some((d) => d.sleep !== null);
  const color = avg !== null ? getSleepColor(avg) : '#A8A29E';
  const label = avg !== null ? getSleepLabel(avg, t) : t.sleepDetailNoData;

  // SVG ring math
  const r = 52;
  const circ = 2 * Math.PI * r;
  const progress = avg !== null ? avg / 5 : 0;
  const dashOffset = circ * (1 - progress);

  const tips = [
    { title: t.sleepTip1Title, body: t.sleepTip1Body },
    { title: t.sleepTip2Title, body: t.sleepTip2Body },
    { title: t.sleepTip3Title, body: t.sleepTip3Body },
    { title: t.sleepTip4Title, body: t.sleepTip4Body },
    { title: t.sleepTip5Title, body: t.sleepTip5Body },
    { title: t.sleepTip6Title, body: t.sleepTip6Body },
    { title: t.sleepTip7Title, body: t.sleepTip7Body },
    { title: t.sleepTip8Title, body: t.sleepTip8Body },
    { title: t.sleepTip9Title, body: t.sleepTip9Body },
    { title: t.sleepTip10Title, body: t.sleepTip10Body },
  ];

  return (
    <div
      className="fixed inset-0 z-50 bg-[#F5F0EB] overflow-y-auto"
      style={{ animation: 'fade-in 0.2s ease-out' }}
    >
      <div className="max-w-sm mx-auto px-4 pt-safe-top pb-12">
        {/* Header with back */}
        <div className="flex items-center justify-between mb-6 -mx-1">
          <button
            onClick={onClose}
            className="w-10 h-10 flex items-center justify-center rounded-full active:bg-stone-200 transition-colors"
            aria-label={t.ariaBack}
          >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M15 18l-6-6 6-6" stroke="#44403c" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </button>
          <h1 className="text-stone-800 font-bold text-lg">{t.sleepDetailTitle}</h1>
          <div className="w-10" />
        </div>

        {/* Hero card */}
        <div className="bg-white rounded-3xl p-6 mb-4 shadow-sm flex flex-col items-center">
          <div className="relative">
            <svg width="140" height="140" viewBox="0 0 140 140">
              <circle cx="70" cy="70" r={r} fill="none" stroke="#f5f5f4" strokeWidth="10" />
              {avg !== null && (
                <circle
                  cx="70" cy="70" r={r}
                  fill="none"
                  stroke={color}
                  strokeWidth="10"
                  strokeLinecap="round"
                  strokeDasharray={circ}
                  strokeDashoffset={dashOffset}
                  transform="rotate(-90 70 70)"
                  style={{ transition: 'stroke-dashoffset 0.6s ease' }}
                />
              )}
              <text x="70" y="68" textAnchor="middle" fontSize="32" fontWeight="700" fill="#292524">
                {avg !== null ? avg.toFixed(1) : '—'}
              </text>
              <text x="70" y="90" textAnchor="middle" fontSize="11" fill="#a8a29e">
                / 5
              </text>
            </svg>
          </div>
          <p className="mt-3 font-bold text-base" style={{ color }}>{label}</p>
          {avg !== null && (
            <p className="text-stone-400 text-xs mt-1">
              {t.sleepDetailAvgLabel} · {t.profileSinceCheckIns.toLowerCase()}
            </p>
          )}
        </div>

        {/* Chart */}
        <div className="bg-white rounded-3xl p-5 mb-4 shadow-sm">
          <p className="text-stone-400 text-xs uppercase tracking-widest font-semibold mb-3">
            {t.sleepDetailChartLabel}
          </p>
          {hasData ? (
            <div className="h-40 -mx-2">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData} margin={{ top: 5, right: 10, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="sleepFill" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor={color} stopOpacity={0.4} />
                      <stop offset="100%" stopColor={color} stopOpacity={0.05} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid stroke="#f5f5f4" vertical={false} />
                  <XAxis
                    dataKey="label"
                    tick={{ fill: '#a8a29e', fontSize: 9 }}
                    interval={2}
                    axisLine={false}
                    tickLine={false}
                  />
                  <YAxis
                    domain={[0, 5]}
                    ticks={[1, 3, 5]}
                    tick={{ fill: '#a8a29e', fontSize: 10 }}
                    axisLine={false}
                    tickLine={false}
                  />
                  <Tooltip
                    contentStyle={{ borderRadius: 12, border: 'none', boxShadow: '0 4px 12px rgba(0,0,0,0.08)', fontSize: 12 }}
                    labelStyle={{ color: '#78716c' }}
                  />
                  <Area
                    type="monotone"
                    dataKey="sleep"
                    stroke={color}
                    strokeWidth={2.5}
                    fill="url(#sleepFill)"
                    connectNulls
                    dot={{ r: 3, fill: color }}
                    activeDot={{ r: 5 }}
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          ) : (
            <p className="text-stone-400 text-sm py-8 text-center">{t.sleepDetailNoData}</p>
          )}
        </div>

        {/* Tips */}
        <div className="mt-6 mb-3">
          <h2 className="text-stone-800 font-bold text-base">{t.sleepDetailTipsTitle}</h2>
          <p className="text-stone-400 text-xs mt-0.5">{t.sleepDetailTipsSubtitle}</p>
        </div>
        <div className="space-y-2.5">
          {tips.map((tip, i) => (
            <div key={i} className="bg-white rounded-2xl p-4 shadow-sm">
              <p className="text-stone-800 font-semibold text-sm mb-1">{tip.title}</p>
              <p className="text-stone-500 text-xs leading-relaxed">{tip.body}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
