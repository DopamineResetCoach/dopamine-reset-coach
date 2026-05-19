import type { Translations } from './i18n/types';
import { type StageId } from './stages';

/**
 * Dopamine science insights — unlockable as the user advances through stages.
 * 30 total tips, distributed across 5 stages. Each new stage unlocks a fresh
 * batch so users have new content to read as they progress.
 *
 * NOTE: lib/insights.ts is the weekly-insights module — unrelated. Keep this
 * module focused only on the science tip catalog.
 */
export const INSIGHTS_PER_STAGE: Record<StageId, number> = {
  1: 5,
  2: 5,
  3: 7,
  4: 7,
  5: 6,
};

export const TOTAL_INSIGHTS = 30;

export interface InsightMeta {
  /** 1-based index, 1..30 */
  index: number;
  unlocksAt: StageId;
  titleKey: keyof Translations;
  bodyKey: keyof Translations;
}

function buildInsights(): InsightMeta[] {
  const out: InsightMeta[] = [];
  let i = 1;
  const stageOrder: StageId[] = [1, 2, 3, 4, 5];
  for (const stage of stageOrder) {
    const count = INSIGHTS_PER_STAGE[stage];
    for (let n = 0; n < count; n++, i++) {
      out.push({
        index: i,
        unlocksAt: stage,
        titleKey: `insight${i}Title` as keyof Translations,
        bodyKey: `insight${i}Body` as keyof Translations,
      });
    }
  }
  return out;
}

export const INSIGHTS: InsightMeta[] = buildInsights();

/** Cumulative count of insights unlocked at a given stage. */
export function getUnlockedInsightCount(currentStage: StageId): number {
  let n = 0;
  for (let s = 1; s <= currentStage; s++) {
    n += INSIGHTS_PER_STAGE[s as StageId];
  }
  return n;
}

/** Insights newly unlocked by entering `stageId` (not cumulative). */
export function getInsightsUnlockedByStage(stageId: StageId): number {
  return INSIGHTS_PER_STAGE[stageId];
}

/** Group insights by the stage they unlock at, in stage order. */
export function getInsightsByStage(): Record<StageId, InsightMeta[]> {
  const grouped: Record<StageId, InsightMeta[]> = { 1: [], 2: [], 3: [], 4: [], 5: [] };
  for (const ins of INSIGHTS) grouped[ins.unlocksAt].push(ins);
  return grouped;
}

/**
 * Pick one unlocked insight to surface as a "did you know" pill on a given
 * date. Deterministic per-day so it doesn't shuffle on re-render.
 */
export function getDailyInsightIndex(
  currentStage: StageId,
  date: Date = new Date(),
): number | null {
  const unlocked = getUnlockedInsightCount(currentStage);
  if (unlocked === 0) return null;
  const seed =
    date.getFullYear() * 10000 + (date.getMonth() + 1) * 100 + date.getDate();
  return (seed % unlocked) + 1; // 1-based
}
