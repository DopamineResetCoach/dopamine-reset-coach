import { CapacitorHealthkit, OtherData, SampleNames } from '@perfood/capacitor-healthkit';
import { toLocalDateString } from './scoring';

let authorized = false;

function isCapacitor(): boolean {
  if (typeof window === 'undefined') return false;
  const cap = (window as unknown as { Capacitor?: { isNativePlatform?: () => boolean } }).Capacitor;
  return !!cap?.isNativePlatform?.();
}

export async function requestStepAuthorization(): Promise<boolean> {
  if (!isCapacitor()) return false;
  try {
    await CapacitorHealthkit.requestAuthorization({
      all: [],
      read: ['steps'],
      write: [],
    });
    authorized = true;
    return true;
  } catch (e) {
    console.error('[Steps] authorization error:', e);
    return false;
  }
}

// HealthKit returns raw samples from EVERY source (iPhone, Apple Watch, third-party
// fitness apps). Summing them double-counts. Apple's Health app deduplicates internally
// but the plugin doesn't expose that aggregated total — so we group samples per day
// per source and take the max-source-total. Approximates Apple's reconciled daily count.
function bucketAndDedupe(
  samples: ReadonlyArray<{ startDate?: string; sourceBundleId?: string; source?: string; value?: number }>,
): Record<string, number> {
  const byDayBySource: Record<string, Record<string, number>> = {};
  for (const s of samples) {
    const ts = s.startDate;
    if (!ts) continue;
    const day = toLocalDateString(new Date(ts));
    const source = s.sourceBundleId || s.source || 'unknown';
    if (!byDayBySource[day]) byDayBySource[day] = {};
    byDayBySource[day][source] = (byDayBySource[day][source] ?? 0) + (s.value ?? 0);
  }
  const out: Record<string, number> = {};
  for (const [day, sources] of Object.entries(byDayBySource)) {
    const totals = Object.values(sources);
    if (totals.length === 0) continue;
    out[day] = Math.round(Math.max(...totals));
  }
  return out;
}

export async function getStepsHistory(daysBack: number): Promise<Record<string, number>> {
  if (!isCapacitor()) return {};
  if (!authorized) {
    const ok = await requestStepAuthorization();
    if (!ok) return {};
  }
  try {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - daysBack, 0, 0, 0);

    const result = await CapacitorHealthkit.queryHKitSampleType<OtherData>({
      sampleName: SampleNames.STEP_COUNT,
      startDate: start.toISOString(),
      endDate: now.toISOString(),
      limit: 0,
    });

    return bucketAndDedupe(result.resultData);
  } catch (e) {
    console.error('[Steps] history error:', e);
    return {};
  }
}

export async function getTodaySteps(): Promise<number> {
  if (!isCapacitor()) return 0;
  if (!authorized) {
    const ok = await requestStepAuthorization();
    if (!ok) return 0;
  }
  try {
    const now = new Date();
    const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);

    const result = await CapacitorHealthkit.queryHKitSampleType<OtherData>({
      sampleName: SampleNames.STEP_COUNT,
      startDate: startOfDay.toISOString(),
      endDate: now.toISOString(),
      limit: 0,
    });

    const buckets = bucketAndDedupe(result.resultData);
    const todayKey = toLocalDateString(now);
    return buckets[todayKey] ?? 0;
  } catch (e) {
    console.error('[Steps] query error:', e);
    return 0;
  }
}

/**
 * Bereken de Stride Score op basis van stappen en goal.
 * Max score: 100 punten
 */
export function calcStrideScore(steps: number, goal: number): number {
  if (goal <= 0) return 0;
  const ratio = steps / goal;

  let score: number;
  if (ratio >= 2.0) {
    score = 100;
  } else if (ratio >= 1.2) {
    score = 75 + (ratio - 1.2) / 0.8 * 25;
  } else if (ratio >= 1.0) {
    score = 60 + (ratio - 1.0) / 0.2 * 15;
  } else if (ratio >= 0.5) {
    score = 20 + (ratio - 0.5) / 0.5 * 40;
  } else {
    score = ratio / 0.5 * 20;
  }

  return Math.round(Math.min(100, Math.max(0, score)));
}

