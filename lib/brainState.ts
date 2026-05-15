import { DailyLog, UserProfile, DailyCheckIn } from '@/types';
import { calculateStreak, calculateDailyDebt, getTodayString, getPlanDay, toLocalDateString } from './scoring';

export type Level = 'low' | 'medium' | 'high';

export interface BrainStateResult {
  stateKey: number;
  params: Record<string, string | number>;
  metrics: {
    focus: Level;
    impulse: Level;
    recovery: Level;
  };
}

interface Ctx {
  todayLog: DailyLog | undefined;
  yesterdayLog: DailyLog | undefined;
  recentLogs: DailyLog[];
  todayCheckIn: DailyCheckIn | undefined;
  recentCheckIns: DailyCheckIn[];
  totalCheckIns: number;
  todayScore: number;
  todayDebt: number;
  todayBadHabits: number;
  yesterdayBadHabits: number;
  todayTasks: number;
  cleanDaysInRow: number;
  scoreTrend: 'rising' | 'declining' | 'flat';
  scoreRisingDays: number;
  scoreDecliningDays: number;
  streak: number;
  planDay: number;
  todaySteps: number;
  stepGoal: number;
  hour: number;
  dayOfWeek: number;
  urgeFreeStreak: number;
  todayUrgesAllResisted: boolean;
  todayUrgesCount: number;
}

function buildCtx(
  dailyLogs: Record<string, DailyLog>,
  profile: UserProfile,
  todaySteps: number,
  stepGoal: number,
  now: Date,
): Ctx {
  const today = getTodayString();
  const sorted = Object.values(dailyLogs).sort((a, b) => b.date.localeCompare(a.date));
  const todayLog = dailyLogs[today];
  const yesterday = new Date(now);
  yesterday.setDate(yesterday.getDate() - 1);
  const yesterdayKey = toLocalDateString(yesterday);
  const yesterdayLog = dailyLogs[yesterdayKey];

  const recentLogs = sorted.slice(0, 14);
  const recentCheckIns = recentLogs.map((l) => l.checkIn).filter(Boolean) as DailyCheckIn[];
  const totalCheckIns = Object.values(dailyLogs).filter((l) => l.checkIn).length;

  const todayScore = todayLog?.dopamineScore ?? 0;
  const todayDebt = todayLog ? calculateDailyDebt(todayLog.badHabits) : 0;
  const todayBadHabits = todayLog?.badHabits?.length ?? 0;
  const yesterdayBadHabits = yesterdayLog?.badHabits?.length ?? 0;
  const todayTasks = todayLog?.completedTasks.length ?? 0;

  let cleanDaysInRow = 0;
  for (const l of sorted) {
    if ((l.badHabits?.length ?? 0) === 0) cleanDaysInRow++;
    else break;
  }

  const last5Scores = sorted.slice(0, 5).map((l) => l.dopamineScore);
  let risingDays = 0;
  for (let i = 0; i < last5Scores.length - 1; i++) {
    if (last5Scores[i] > last5Scores[i + 1] + 2) risingDays++;
    else break;
  }
  let decliningDays = 0;
  for (let i = 0; i < last5Scores.length - 1; i++) {
    if (last5Scores[i] < last5Scores[i + 1] - 2) decliningDays++;
    else break;
  }
  const scoreTrend: Ctx['scoreTrend'] =
    risingDays >= 2 ? 'rising' : decliningDays >= 2 ? 'declining' : 'flat';

  const streak = calculateStreak(dailyLogs);
  const planDay = getPlanDay(profile.startDate);

  let urgeFreeStreak = 0;
  for (const l of sorted) {
    const gaveIn = l.urges.some((u) => !u.completedIntervention);
    if (!gaveIn) urgeFreeStreak++;
    else break;
  }

  const todayUrges = todayLog?.urges ?? [];
  const todayUrgesCount = todayUrges.length;
  const todayUrgesAllResisted =
    todayUrgesCount > 0 && todayUrges.every((u) => u.completedIntervention);

  return {
    todayLog,
    yesterdayLog,
    recentLogs,
    todayCheckIn: todayLog?.checkIn,
    recentCheckIns,
    totalCheckIns,
    todayScore,
    todayDebt,
    todayBadHabits,
    yesterdayBadHabits,
    todayTasks,
    cleanDaysInRow,
    scoreTrend,
    scoreRisingDays: risingDays,
    scoreDecliningDays: decliningDays,
    streak,
    planDay,
    todaySteps,
    stepGoal,
    hour: now.getHours(),
    dayOfWeek: now.getDay(),
    urgeFreeStreak,
    todayUrgesAllResisted,
    todayUrgesCount,
  };
}

function pickState(c: Ctx): { stateKey: number; params: Record<string, string | number> } {
  if (c.planDay === 1) return { stateKey: 9, params: {} };

  if (c.todayCheckIn && c.totalCheckIns === 1) return { stateKey: 27, params: {} };

  if ([7, 14, 21, 30].includes(c.planDay)) {
    return { stateKey: 28, params: { n: c.planDay } };
  }

  if (c.todayUrgesAllResisted && c.todayUrgesCount >= 2) {
    return { stateKey: 15, params: {} };
  }

  if (c.urgeFreeStreak >= 3) return { stateKey: 18, params: {} };

  if (c.todayBadHabits >= 3) return { stateKey: 12, params: {} };

  if (c.yesterdayBadHabits >= 3 && c.todayScore < 45) {
    return { stateKey: 29, params: {} };
  }

  if (c.yesterdayBadHabits >= 2 && c.todayBadHabits === 0 && c.todayLog) {
    return { stateKey: 1, params: {} };
  }

  if (c.cleanDaysInRow >= 5) return { stateKey: 8, params: {} };

  const sleep = c.todayCheckIn?.sleep ?? 5;
  const mood = c.todayCheckIn?.mood ?? 3;
  const energy = c.todayCheckIn?.energy ?? 3;

  if (c.todayCheckIn && sleep <= 2 && c.todayBadHabits >= 1 && c.hour >= 18) {
    return { stateKey: 5, params: {} };
  }

  if (c.todayCheckIn && sleep <= 2) return { stateKey: 6, params: {} };

  const lastThreeEnergies = c.recentCheckIns.slice(0, 3).map((ci) => ci.energy);
  if (lastThreeEnergies.length === 3 && lastThreeEnergies.every((e) => e <= 2)) {
    return { stateKey: 22, params: {} };
  }

  if (c.todayCheckIn && mood <= 2 && energy <= 2) return { stateKey: 14, params: {} };

  if (c.todayCheckIn && mood <= 2 && c.todayTasks >= 3) {
    return { stateKey: 26, params: {} };
  }

  if (c.todayCheckIn && mood >= 4 && sleep >= 4) return { stateKey: 13, params: {} };

  if (c.scoreTrend === 'rising' && c.scoreRisingDays >= 2) {
    return { stateKey: 3, params: {} };
  }

  if (c.scoreTrend === 'declining' && c.scoreDecliningDays >= 2) {
    return { stateKey: 21, params: {} };
  }

  if (c.todayDebt > 5) return { stateKey: 7, params: {} };

  if (c.todayScore >= 65 && sleep >= 4 && c.todayCheckIn) {
    return { stateKey: 4, params: {} };
  }

  if (c.todayTasks >= 4 && c.todayBadHabits === 0) return { stateKey: 23, params: {} };

  if (c.hour >= 14 && c.hour < 17 && c.todayScore >= 60 && c.todayBadHabits === 0) {
    return { stateKey: 30, params: {} };
  }

  if (c.stepGoal > 0 && c.todaySteps >= c.stepGoal) return { stateKey: 16, params: {} };

  if (c.planDay > 3 && c.todaySteps < 2000 && c.todaySteps > 0) {
    return { stateKey: 17, params: {} };
  }

  if (c.dayOfWeek === 5 && c.hour >= 17 && c.todayScore >= 50) {
    return { stateKey: 20, params: {} };
  }

  if (c.dayOfWeek === 1 && c.yesterdayBadHabits + (c.recentLogs[1]?.badHabits?.length ?? 0) >= 2) {
    return { stateKey: 19, params: {} };
  }

  if (c.streak >= 5) return { stateKey: 2, params: { n: c.planDay } };

  if (c.planDay >= 2 && c.planDay <= 4) return { stateKey: 10, params: {} };

  if (c.planDay >= 8 && c.planDay <= 14) return { stateKey: 11, params: {} };

  if (c.todayScore >= 40 && c.todayScore <= 60) return { stateKey: 24, params: {} };

  return { stateKey: 25, params: {} };
}

function computeFocus(c: Ctx): Level {
  const sleep = c.todayCheckIn?.sleep;
  if (sleep !== undefined) {
    if (sleep >= 4 && c.todayScore >= 60) return 'high';
    if (sleep <= 2 || c.todayScore <= 35) return 'low';
  } else {
    if (c.todayScore >= 65) return 'high';
    if (c.todayScore <= 35) return 'low';
  }
  return 'medium';
}

function computeImpulse(c: Ctx): Level {
  const sleep = c.todayCheckIn?.sleep;
  let level: Level = 'medium';
  if ((sleep !== undefined && sleep <= 2) || c.todayBadHabits >= 2) level = 'high';
  else if (c.todayScore >= 65 && c.todayBadHabits === 0) level = 'low';

  const isWeekendEvening =
    (c.dayOfWeek === 5 || c.dayOfWeek === 6) && c.hour >= 18;
  if (isWeekendEvening && level !== 'high') {
    level = level === 'low' ? 'medium' : 'high';
  }
  return level;
}

function computeRecovery(c: Ctx): Level {
  const mood = c.todayCheckIn?.mood ?? 3;
  if (c.streak >= 5 || (c.scoreTrend === 'rising' && c.scoreRisingDays >= 2)) {
    return 'high';
  }
  if (c.scoreTrend === 'declining' && c.scoreDecliningDays >= 2 && mood <= 2) {
    return 'low';
  }
  return 'medium';
}

export function computeBrainState(
  dailyLogs: Record<string, DailyLog>,
  profile: UserProfile,
  todaySteps: number,
  stepGoal: number,
  now: Date = new Date(),
): BrainStateResult {
  const ctx = buildCtx(dailyLogs, profile, todaySteps, stepGoal, now);
  const { stateKey, params } = pickState(ctx);
  return {
    stateKey,
    params,
    metrics: {
      focus: computeFocus(ctx),
      impulse: computeImpulse(ctx),
      recovery: computeRecovery(ctx),
    },
  };
}
