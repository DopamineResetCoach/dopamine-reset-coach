import type { DailyLog } from '@/types';
import { toLocalDateString } from './scoring';

export interface VoiceJournalEntry {
  date: string; // YYYY-MM-DD
  dataUri: string;
  durationMs: number;
  mood: number | null; // 1-5
  score: number;
  badHabitCount: number;
}

export function getVoiceJournalEntries(dailyLogs: Record<string, DailyLog>): VoiceJournalEntry[] {
  return Object.values(dailyLogs)
    .filter((l): l is DailyLog & { checkIn: NonNullable<DailyLog['checkIn']> } =>
      !!l.checkIn?.voiceNote && typeof l.checkIn.voiceNoteDurationMs === 'number',
    )
    .map((l) => ({
      date: l.date,
      dataUri: l.checkIn.voiceNote!,
      durationMs: l.checkIn.voiceNoteDurationMs!,
      mood: l.checkIn.mood ?? null,
      score: l.dopamineScore,
      badHabitCount: l.badHabits?.length ?? 0,
    }))
    .sort((a, b) => b.date.localeCompare(a.date));
}

export function getReflectionStreak(dailyLogs: Record<string, DailyLog>): number {
  let streak = 0;
  const now = new Date();
  for (let i = 0; i < 365; i++) {
    const d = new Date(now);
    d.setDate(d.getDate() - i);
    const log = dailyLogs[toLocalDateString(d)];
    if (log?.checkIn?.voiceNote) {
      streak++;
    } else {
      // Today doesn't break the streak (user may not have done it yet)
      if (i === 0) continue;
      break;
    }
  }
  return streak;
}

export interface WeekReflectionStats {
  count: number;
  totalSeconds: number;
}

export function getWeekReflectionStats(dailyLogs: Record<string, DailyLog>): WeekReflectionStats {
  const now = new Date();
  let count = 0;
  let totalMs = 0;
  for (let i = 0; i < 7; i++) {
    const d = new Date(now);
    d.setDate(d.getDate() - i);
    const log = dailyLogs[toLocalDateString(d)];
    if (log?.checkIn?.voiceNote && log.checkIn.voiceNoteDurationMs) {
      count++;
      totalMs += log.checkIn.voiceNoteDurationMs;
    }
  }
  return { count, totalSeconds: Math.round(totalMs / 1000) };
}

export interface ReflectionPattern {
  kind: 'higher' | 'lower' | 'flat' | 'insufficient';
  delta: number; // points difference (always positive)
  reflectionDayCount: number;
  nonReflectionDayCount: number;
}

// Compares avg score on days WITH a voice note vs days WITHOUT, looking at
// the most recent 30 days. Needs ≥3 of each to surface a pattern — otherwise
// returns 'insufficient' and the UI hides the line.
export function getReflectionPattern(dailyLogs: Record<string, DailyLog>): ReflectionPattern {
  const now = new Date();
  let refSum = 0, refN = 0, nonSum = 0, nonN = 0;
  for (let i = 0; i < 30; i++) {
    const d = new Date(now);
    d.setDate(d.getDate() - i);
    const log = dailyLogs[toLocalDateString(d)];
    if (!log) continue;
    if (log.checkIn?.voiceNote) {
      refSum += log.dopamineScore; refN++;
    } else {
      nonSum += log.dopamineScore; nonN++;
    }
  }
  if (refN < 3 || nonN < 3) {
    return { kind: 'insufficient', delta: 0, reflectionDayCount: refN, nonReflectionDayCount: nonN };
  }
  const refAvg = refSum / refN;
  const nonAvg = nonSum / nonN;
  const diff = Math.round(refAvg - nonAvg);
  if (Math.abs(diff) < 3) {
    return { kind: 'flat', delta: 0, reflectionDayCount: refN, nonReflectionDayCount: nonN };
  }
  return {
    kind: diff > 0 ? 'higher' : 'lower',
    delta: Math.abs(diff),
    reflectionDayCount: refN,
    nonReflectionDayCount: nonN,
  };
}
