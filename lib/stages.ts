import type { DailyLog } from '@/types';
import { toLocalDateString } from './scoring';

export type StageId = 1 | 2 | 3 | 4 | 5;

export interface StageMeta {
  id: StageId;
  // Inclusive lower bound of qualifying weeks needed to be in this stage.
  threshold: number;
  emoji: string;
  // i18n keys — looked up via t at render time.
  nameKey: keyof StageI18n;
  descKey: keyof StageI18n;
  scienceKey: keyof StageI18n;
}

// Subset of Translations that this module references. Kept loose so the
// scoring module doesn't pull on the full Translations type.
type StageI18n = {
  stage1Name: string; stage2Name: string; stage3Name: string; stage4Name: string; stage5Name: string;
  stage1Desc: string; stage2Desc: string; stage3Desc: string; stage4Desc: string; stage5Desc: string;
  stage1Science: string; stage2Science: string; stage3Science: string; stage4Science: string; stage5Science: string;
};

// Emojis trace a healing/growth arc (heal → sprout → grow → tree → peak), not
// a moon cycle. Lunar icons were misread as "time of day" or "months"; the
// growth metaphor maps directly to brain recovery and removes the need for
// any "these are weeks, not months" disclaimer.
export const STAGES: StageMeta[] = [
  { id: 1, threshold: 0,  emoji: '🩹', nameKey: 'stage1Name', descKey: 'stage1Desc', scienceKey: 'stage1Science' },
  { id: 2, threshold: 2,  emoji: '🌱', nameKey: 'stage2Name', descKey: 'stage2Desc', scienceKey: 'stage2Science' },
  { id: 3, threshold: 4,  emoji: '🌿', nameKey: 'stage3Name', descKey: 'stage3Desc', scienceKey: 'stage3Science' },
  { id: 4, threshold: 8,  emoji: '🌳', nameKey: 'stage4Name', descKey: 'stage4Desc', scienceKey: 'stage4Science' },
  { id: 5, threshold: 12, emoji: '🏔️', nameKey: 'stage5Name', descKey: 'stage5Desc', scienceKey: 'stage5Science' },
];

/**
 * A day "qualifies" toward stage progress if the user actually engaged:
 * either a saved check-in, or at least one completed task. Just opening
 * the app is not enough — we want lived behavior, not vanity opens.
 */
export function isDayQualifying(log: DailyLog | undefined): boolean {
  if (!log) return false;
  if (log.checkIn) return true;
  if (log.completedTasks.length > 0) return true;
  return false;
}

/**
 * Get the Monday (YYYY-MM-DD) of the ISO-style week containing `date`.
 * Weeks run Monday → Sunday — same convention as scoring.getWeekKey.
 */
function getWeekMondayKey(date: Date): string {
  const d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const dayOfWeek = (d.getDay() + 6) % 7; // 0 = Monday
  d.setDate(d.getDate() - dayOfWeek);
  return toLocalDateString(d);
}

/**
 * Count "qualifying weeks" since the user's startDate. A week is qualifying
 * if it contains ≥5 qualifying days (allows 2 missed days — realistic, not
 * punishing). The CURRENT week is excluded because it's not yet finished;
 * partial weeks shouldn't determine stage transitions.
 */
export function countQualifyingWeeks(
  dailyLogs: Record<string, DailyLog>,
  startDate: string,
): number {
  const start = new Date(startDate + 'T12:00:00');
  if (Number.isNaN(start.getTime())) return 0;

  const today = new Date();
  today.setHours(12, 0, 0, 0);
  const currentWeekKey = getWeekMondayKey(today);

  // Bucket each daily log by week-Monday key, counting qualifying days.
  const perWeek: Record<string, number> = {};
  for (const [dateKey, log] of Object.entries(dailyLogs)) {
    if (!isDayQualifying(log)) continue;
    const logDate = new Date(dateKey + 'T12:00:00');
    if (Number.isNaN(logDate.getTime())) continue;
    if (logDate < start) continue;
    const wk = getWeekMondayKey(logDate);
    if (wk === currentWeekKey) continue; // exclude in-progress week
    perWeek[wk] = (perWeek[wk] ?? 0) + 1;
  }

  let count = 0;
  for (const days of Object.values(perWeek)) {
    if (days >= 5) count++;
  }
  return count;
}

export function getStageForWeeks(qualifyingWeeks: number): StageId {
  let current: StageId = 1;
  for (const s of STAGES) {
    if (qualifyingWeeks >= s.threshold) current = s.id;
  }
  return current;
}

export function getStageMeta(id: StageId): StageMeta {
  return STAGES.find((s) => s.id === id) ?? STAGES[0];
}

export interface StageProgress {
  current: StageId;
  qualifyingWeeks: number;
  /** Threshold of the current stage (weeks needed to be in it). */
  currentThreshold: number;
  /** Next stage id, or null if at max. */
  next: StageId | null;
  /** Threshold of the next stage, or null. */
  nextThreshold: number | null;
  /** Qualifying weeks still needed to reach the next stage, or 0 at max. */
  weeksToNext: number;
  /** 0..1 progress within the current stage band. */
  bandProgress: number;
}

export function getStageProgress(
  dailyLogs: Record<string, DailyLog>,
  startDate: string,
): StageProgress {
  const qualifyingWeeks = countQualifyingWeeks(dailyLogs, startDate);
  const current = getStageForWeeks(qualifyingWeeks);
  const currentMeta = getStageMeta(current);
  const nextMeta = STAGES.find((s) => s.id === current + 1) ?? null;

  if (!nextMeta) {
    return {
      current,
      qualifyingWeeks,
      currentThreshold: currentMeta.threshold,
      next: null,
      nextThreshold: null,
      weeksToNext: 0,
      bandProgress: 1,
    };
  }

  const bandSize = nextMeta.threshold - currentMeta.threshold;
  const within = qualifyingWeeks - currentMeta.threshold;
  const bandProgress = bandSize > 0 ? Math.min(1, Math.max(0, within / bandSize)) : 1;

  return {
    current,
    qualifyingWeeks,
    currentThreshold: currentMeta.threshold,
    next: nextMeta.id,
    nextThreshold: nextMeta.threshold,
    weeksToNext: Math.max(0, nextMeta.threshold - qualifyingWeeks),
    bandProgress,
  };
}

/**
 * Count qualifying days in the CURRENT (in-progress) week. Used for the
 * "days this week" indicator on StageCard so the user can see traction
 * before the week closes.
 */
export function countCurrentWeekQualifyingDays(
  dailyLogs: Record<string, DailyLog>,
): number {
  const today = new Date();
  today.setHours(12, 0, 0, 0);
  const currentWeekKey = getWeekMondayKey(today);
  let n = 0;
  for (const [dateKey, log] of Object.entries(dailyLogs)) {
    if (!isDayQualifying(log)) continue;
    const logDate = new Date(dateKey + 'T12:00:00');
    if (Number.isNaN(logDate.getTime())) continue;
    if (getWeekMondayKey(logDate) === currentWeekKey) n++;
  }
  return n;
}
