import { DailyLog } from '@/types';

export type TrendDirection = 'up' | 'down' | 'flat';

export interface TrendStat {
  key: 'fastDopamine' | 'avoidance' | 'urgesGivenIn' | 'tasksDone';
  current: number;
  previous: number;
  delta: number;
  direction: TrendDirection;
  goodDirection: 'down' | 'up';
}

export interface Pattern {
  kind: 'sleepBadHabit' | 'energyAvoidance' | 'moodTasks' | 'bestDay' | 'interventionWin' | 'none';
  values: Record<string, string | number>;
}

export interface ActionSuggestion {
  kind: 'sleep' | 'walk' | 'morning' | 'intervention' | 'continue' | 'startTracking';
}

export interface WeeklyInsights {
  hasEnoughData: boolean;
  daysTracked: number;
  trends: TrendStat[];
  pattern: Pattern;
  action: ActionSuggestion;
}

function sumDays(logs: DailyLog[]) {
  return {
    fastDopamine: logs.reduce((s, l) => s + (l.badHabits?.length ?? 0), 0),
    avoidance: logs.filter((l) => l.completedTasks.length < 3).length,
    urgesGivenIn: logs.reduce(
      (s, l) => s + l.urges.filter((u) => !u.completedIntervention).length,
      0,
    ),
    tasksDone: logs.reduce((s, l) => s + l.completedTasks.length, 0),
  };
}

function trend(
  key: TrendStat['key'],
  current: number,
  previous: number,
  goodDirection: 'down' | 'up',
): TrendStat {
  const delta = current - previous;
  const direction: TrendDirection = delta === 0 ? 'flat' : delta > 0 ? 'up' : 'down';
  return { key, current, previous, delta, direction, goodDirection };
}

function detectPattern(logs: DailyLog[]): Pattern {
  const checkInDays = logs.filter((l) => l.checkIn);
  if (checkInDays.length < 4) {
    const bestDay = logs.reduce<DailyLog | null>(
      (best, l) =>
        !best || l.completedTasks.length > best.completedTasks.length ? l : best,
      null,
    );
    if (bestDay && bestDay.completedTasks.length >= 3) {
      return { kind: 'bestDay', values: { tasks: bestDay.completedTasks.length } };
    }
    return { kind: 'none', values: {} };
  }

  const goodSleep = checkInDays.filter((l) => (l.checkIn?.sleep ?? 0) >= 4);
  const badSleep = checkInDays.filter((l) => (l.checkIn?.sleep ?? 0) <= 2);
  if (goodSleep.length >= 2 && badSleep.length >= 2) {
    const goodAvg =
      goodSleep.reduce((s, l) => s + (l.badHabits?.length ?? 0), 0) / goodSleep.length;
    const badAvg =
      badSleep.reduce((s, l) => s + (l.badHabits?.length ?? 0), 0) / badSleep.length;
    if (badAvg > 0 && badAvg >= goodAvg * 1.5 && badAvg - goodAvg >= 0.5) {
      const factor = goodAvg > 0 ? Math.round((badAvg / goodAvg) * 10) / 10 : Math.round(badAvg * 10) / 10;
      return { kind: 'sleepBadHabit', values: { factor } };
    }
  }

  const highEnergy = checkInDays.filter((l) => (l.checkIn?.energy ?? 0) >= 4);
  const lowEnergy = checkInDays.filter((l) => (l.checkIn?.energy ?? 0) <= 2);
  if (highEnergy.length >= 2 && lowEnergy.length >= 2) {
    const lowAvoid = lowEnergy.filter((l) => l.completedTasks.length < 3).length / lowEnergy.length;
    const highAvoid =
      highEnergy.filter((l) => l.completedTasks.length < 3).length / highEnergy.length;
    if (lowAvoid > 0 && lowAvoid >= highAvoid * 2 && lowAvoid - highAvoid >= 0.25) {
      const lowPct = Math.round(lowAvoid * 100);
      return { kind: 'energyAvoidance', values: { lowPct } };
    }
  }

  const highMood = checkInDays.filter((l) => (l.checkIn?.mood ?? 0) >= 4);
  const lowMood = checkInDays.filter((l) => (l.checkIn?.mood ?? 0) <= 2);
  if (highMood.length >= 2 && lowMood.length >= 2) {
    const highAvg =
      highMood.reduce((s, l) => s + l.completedTasks.length, 0) / highMood.length;
    const lowAvg = lowMood.reduce((s, l) => s + l.completedTasks.length, 0) / lowMood.length;
    if (highAvg - lowAvg >= 1) {
      const diff = Math.round((highAvg - lowAvg) * 10) / 10;
      return { kind: 'moodTasks', values: { diff } };
    }
  }

  const totalUrges = logs.reduce((s, l) => s + l.urges.length, 0);
  const interventions = logs.reduce(
    (s, l) => s + l.urges.filter((u) => u.completedIntervention).length,
    0,
  );
  if (totalUrges >= 4 && interventions / totalUrges >= 0.7) {
    const pct = Math.round((interventions / totalUrges) * 100);
    return { kind: 'interventionWin', values: { pct } };
  }

  const bestDay = logs.reduce<DailyLog | null>(
    (best, l) =>
      !best || l.completedTasks.length > best.completedTasks.length ? l : best,
    null,
  );
  if (bestDay && bestDay.completedTasks.length >= 3) {
    return { kind: 'bestDay', values: { tasks: bestDay.completedTasks.length } };
  }

  return { kind: 'none', values: {} };
}

function suggestAction(trends: TrendStat[], pattern: Pattern): ActionSuggestion {
  if (pattern.kind === 'sleepBadHabit') return { kind: 'sleep' };
  if (pattern.kind === 'energyAvoidance') return { kind: 'morning' };
  if (pattern.kind === 'moodTasks') return { kind: 'walk' };
  if (pattern.kind === 'interventionWin') return { kind: 'continue' };

  const fast = trends.find((t) => t.key === 'fastDopamine');
  const urges = trends.find((t) => t.key === 'urgesGivenIn');
  const avoid = trends.find((t) => t.key === 'avoidance');

  if (urges && urges.current > urges.previous && urges.current >= 2) {
    return { kind: 'intervention' };
  }
  if (fast && fast.current >= 5) return { kind: 'sleep' };
  if (avoid && avoid.current >= 3) return { kind: 'morning' };
  return { kind: 'continue' };
}

export function buildWeeklyInsights(dailyLogs: Record<string, DailyLog>): WeeklyInsights {
  const all = Object.values(dailyLogs).sort((a, b) => b.date.localeCompare(a.date));
  const last7 = all.slice(0, 7);
  const prev7 = all.slice(7, 14);
  const last14 = all.slice(0, 14);

  const current = sumDays(last7);
  const previous = sumDays(prev7);

  const trends: TrendStat[] = [
    trend('fastDopamine', current.fastDopamine, previous.fastDopamine, 'down'),
    trend('avoidance', current.avoidance, previous.avoidance, 'down'),
    trend('urgesGivenIn', current.urgesGivenIn, previous.urgesGivenIn, 'down'),
    trend('tasksDone', current.tasksDone, previous.tasksDone, 'up'),
  ];

  const pattern = detectPattern(last14);
  const action =
    last7.length === 0 ? { kind: 'startTracking' as const } : suggestAction(trends, pattern);

  return {
    hasEnoughData: last7.length > 0,
    daysTracked: last7.length,
    trends,
    pattern,
    action,
  };
}
