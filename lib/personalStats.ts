import type { DailyLog } from '@/types';
import { toLocalDateString } from './scoring';

export interface PersonalStats {
  daysSinceStart: number;
  longestStreak: number;
  highestScore: number;
  urgesResistedTotal: number;
  tasksCompletedTotal: number;
  cleanDays: number;
  daysWithAnyLog: number;
  reflectionMinutes: number;
  reflectionCount: number;
  bestWeekday: { weekday: number; avgScore: number } | null;
}

export function getPersonalStats(
  dailyLogs: Record<string, DailyLog>,
  startDate: string,
): PersonalStats {
  const logs = Object.values(dailyLogs);
  const todayKey = toLocalDateString(new Date());
  const finishedLogs = logs.filter((l) => l.date <= todayKey);

  // Days since start (always ≥1 on day 1).
  const start = new Date(startDate + 'T00:00:00');
  const now = new Date();
  const daysSinceStart = Math.max(
    1,
    Math.floor((now.getTime() - start.getTime()) / 86400000) + 1,
  );

  // Longest streak of consecutive days with ≥1 completed task. Look at every
  // run, not just the current one — the user has *ever* hit this.
  const sortedDates = finishedLogs
    .filter((l) => l.completedTasks.length > 0)
    .map((l) => l.date)
    .sort();
  let longestStreak = 0;
  let run = 0;
  let prev: string | null = null;
  for (const d of sortedDates) {
    if (prev) {
      const a = new Date(prev + 'T00:00:00').getTime();
      const b = new Date(d + 'T00:00:00').getTime();
      const diff = Math.round((b - a) / 86400000);
      if (diff === 1) {
        run++;
      } else if (diff === 0) {
        // duplicate date — skip
      } else {
        if (run > longestStreak) longestStreak = run;
        run = 1;
      }
    } else {
      run = 1;
    }
    prev = d;
  }
  if (run > longestStreak) longestStreak = run;

  const highestScore = finishedLogs.reduce(
    (m, l) => Math.max(m, l.dopamineScore),
    0,
  );

  const urgesResistedTotal = finishedLogs.reduce(
    (sum, l) => sum + l.urges.filter((u) => u.completedIntervention).length,
    0,
  );

  const tasksCompletedTotal = finishedLogs.reduce(
    (sum, l) => sum + l.completedTasks.length,
    0,
  );

  // Clean days = no bad habits logged AND at least some activity (any
  // completed task OR a check-in). Empty days don't count as clean.
  const cleanDays = finishedLogs.filter(
    (l) =>
      (l.badHabits?.length ?? 0) === 0 &&
      (l.completedTasks.length > 0 || !!l.checkIn),
  ).length;

  const daysWithAnyLog = finishedLogs.filter(
    (l) =>
      l.completedTasks.length > 0 ||
      !!l.checkIn ||
      (l.badHabits?.length ?? 0) > 0,
  ).length;

  let reflectionMs = 0;
  let reflectionCount = 0;
  for (const l of finishedLogs) {
    if (l.checkIn?.voiceNote && l.checkIn.voiceNoteDurationMs) {
      reflectionMs += l.checkIn.voiceNoteDurationMs;
      reflectionCount++;
    }
  }
  const reflectionMinutes = Math.round(reflectionMs / 60000);

  // Best weekday by average score, requires ≥2 datapoints per weekday to count.
  const weekdayBuckets: Array<{ sum: number; n: number }> = Array.from(
    { length: 7 },
    () => ({ sum: 0, n: 0 }),
  );
  for (const l of finishedLogs) {
    if (l.dopamineScore <= 0) continue;
    const dow = new Date(l.date + 'T12:00:00').getDay();
    weekdayBuckets[dow].sum += l.dopamineScore;
    weekdayBuckets[dow].n++;
  }
  let bestWeekday: PersonalStats['bestWeekday'] = null;
  let bestAvg = 0;
  for (let i = 0; i < 7; i++) {
    const b = weekdayBuckets[i];
    if (b.n < 2) continue;
    const avg = b.sum / b.n;
    if (avg > bestAvg) {
      bestAvg = avg;
      bestWeekday = { weekday: i, avgScore: Math.round(avg) };
    }
  }

  return {
    daysSinceStart,
    longestStreak,
    highestScore,
    urgesResistedTotal,
    tasksCompletedTotal,
    cleanDays,
    daysWithAnyLog,
    reflectionMinutes,
    reflectionCount,
    bestWeekday,
  };
}
