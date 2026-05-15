import { UserProfile, DailyLog, BadHabit } from '@/types';
import { TASKS } from './tasks';

/**
 * Calculate the initial dopamine score based on user profile.
 * High screen time / bad habits = lower starting score.
 */
export function getInitialScore(profile: UserProfile): number {
  let score = 60; // neutral baseline

  // Penalise for high screen time
  score -= Math.min(profile.screenTimeHours * 3, 20);

  // Penalise for poor sleep
  score -= (5 - profile.sleepQuality) * 3;

  // Penalise for low energy
  score -= (5 - profile.energyLevel) * 2;

  // Penalise for active habits
  const habitPenalties: Record<keyof UserProfile['habits'], number> = {
    socialMedia: 5,
    caffeine: 3,
    junkFood: 3,
    alcohol: 4,
    porn: 5,
    gaming: 3,
  };
  for (const [key, penalty] of Object.entries(habitPenalties)) {
    if (profile.habits[key as keyof UserProfile['habits']]) score -= penalty;
  }

  return Math.max(10, Math.min(score, 70));
}

export type BreakdownItem = {
  key: string;
  labelKey: string;
  labelParams?: Record<string, string | number>;
  value: number;
};

export type ScoreBreakdown = {
  baseline: {
    start: number;
    items: BreakdownItem[];
    rawTotal: number;
    clampedTotal: number;
  };
  today: {
    items: BreakdownItem[];
    debtItem: BreakdownItem | null;
    positiveTotal: number;
    positiveClamped: number;
    final: number;
  };
  tip: { messageKey: string; params?: Record<string, string | number> } | null;
};

const HABIT_LABEL_KEYS: Record<keyof UserProfile['habits'], string> = {
  socialMedia: 'scoreInfoHabitSocial',
  caffeine: 'scoreInfoHabitCaffeine',
  junkFood: 'scoreInfoHabitJunkFood',
  alcohol: 'scoreInfoHabitAlcohol',
  porn: 'scoreInfoHabitPorn',
  gaming: 'scoreInfoHabitGaming',
};

const HABIT_PENALTIES: Record<keyof UserProfile['habits'], number> = {
  socialMedia: 5,
  caffeine: 3,
  junkFood: 3,
  alcohol: 4,
  porn: 5,
  gaming: 3,
};

export function getScoreBreakdown(
  profile: UserProfile,
  ctx: {
    completedTaskIds?: string[];
    checkInAvg?: number | null;
    streak?: number;
    steps?: number;
    stepGoal?: number;
    debtPoints?: number;
    challengesToday?: number;
    urgesResisted?: number;
  } = {},
): ScoreBreakdown {
  const baselineItems: BreakdownItem[] = [];

  const screenPenalty = Math.min(profile.screenTimeHours * 3, 20);
  if (screenPenalty > 0) {
    baselineItems.push({
      key: 'screenTime',
      labelKey: 'scoreInfoScreenTime',
      labelParams: { hours: profile.screenTimeHours },
      value: -screenPenalty,
    });
  }

  const sleepPenalty = (5 - profile.sleepQuality) * 3;
  if (sleepPenalty > 0) {
    baselineItems.push({
      key: 'sleep',
      labelKey: 'scoreInfoSleep',
      labelParams: { quality: profile.sleepQuality },
      value: -sleepPenalty,
    });
  }

  const energyPenalty = (5 - profile.energyLevel) * 2;
  if (energyPenalty > 0) {
    baselineItems.push({
      key: 'energy',
      labelKey: 'scoreInfoEnergy',
      labelParams: { level: profile.energyLevel },
      value: -energyPenalty,
    });
  }

  for (const key of Object.keys(HABIT_PENALTIES) as Array<keyof UserProfile['habits']>) {
    if (profile.habits[key]) {
      baselineItems.push({
        key: `habit_${key}`,
        labelKey: HABIT_LABEL_KEYS[key],
        value: -HABIT_PENALTIES[key],
      });
    }
  }

  const rawTotal = 60 + baselineItems.reduce((s, i) => s + i.value, 0);
  const clampedTotal = Math.max(10, Math.min(rawTotal, 70));

  const completedTaskIds = ctx.completedTaskIds ?? [];
  let rawPoints = 0;
  for (const id of completedTaskIds) {
    const task = TASKS.find((t) => t.id === id);
    if (task) rawPoints += task.points;
  }
  let taskBonus = Math.min(30, rawPoints);
  if (rawPoints > 30) taskBonus += Math.min(20, (rawPoints - 30) * 0.5);

  const checkInBonus = ctx.checkInAvg != null ? (ctx.checkInAvg - 3) * 5 : 0;
  const streakBonus = ctx.streak ? Math.min(10, ctx.streak) : 0;
  const stepsBonus =
    ctx.steps && ctx.stepGoal && ctx.stepGoal > 0
      ? Math.min(10, (ctx.steps / ctx.stepGoal) * 10)
      : 0;
  const challengeBonus = ctx.challengesToday ? Math.min(15, ctx.challengesToday * 5) : 0;
  const urgeBonus = ctx.urgesResisted ? Math.min(15, ctx.urgesResisted * 3) : 0;
  const debtPenalty = ctx.debtPoints ?? 0;

  const todayItems: BreakdownItem[] = [];
  if (taskBonus !== 0) {
    todayItems.push({
      key: 'tasks',
      labelKey: 'scoreInfoTodayTasks',
      labelParams: { n: completedTaskIds.length },
      value: taskBonus,
    });
  }
  if (checkInBonus !== 0) {
    todayItems.push({
      key: 'checkIn',
      labelKey: 'scoreInfoTodayCheckIn',
      value: checkInBonus,
    });
  }
  if (streakBonus !== 0) {
    todayItems.push({
      key: 'streak',
      labelKey: 'scoreInfoTodayStreak',
      labelParams: { n: ctx.streak ?? 0 },
      value: streakBonus,
    });
  }
  if (stepsBonus !== 0) {
    todayItems.push({
      key: 'steps',
      labelKey: 'scoreInfoTodaySteps',
      labelParams: { steps: ctx.steps ?? 0, goal: ctx.stepGoal ?? 0 },
      value: stepsBonus,
    });
  }
  if (challengeBonus !== 0) {
    todayItems.push({
      key: 'challenges',
      labelKey: 'scoreInfoTodayChallenges',
      labelParams: { n: ctx.challengesToday ?? 0 },
      value: challengeBonus,
    });
  }
  if (urgeBonus !== 0) {
    todayItems.push({
      key: 'urges',
      labelKey: 'scoreInfoTodayUrges',
      labelParams: { n: ctx.urgesResisted ?? 0 },
      value: urgeBonus,
    });
  }

  const debtItem: BreakdownItem | null =
    debtPenalty > 0
      ? { key: 'debt', labelKey: 'scoreInfoTodayDebt', value: -debtPenalty }
      : null;

  const positiveTotal =
    clampedTotal + taskBonus + checkInBonus + streakBonus + stepsBonus + challengeBonus + urgeBonus;
  const positiveClamped = Math.max(0, Math.min(positiveTotal, 100));
  const final = Math.max(0, positiveClamped - debtPenalty);

  let tip: ScoreBreakdown['tip'] = null;
  const heaviestBaseline = baselineItems
    .slice()
    .sort((a, b) => a.value - b.value)[0];
  if (heaviestBaseline && heaviestBaseline.value <= -5) {
    if (heaviestBaseline.key === 'screenTime') {
      tip = {
        messageKey: 'scoreInfoTipScreenTime',
        params: { points: 3 },
      };
    } else if (heaviestBaseline.key === 'sleep') {
      tip = { messageKey: 'scoreInfoTipSleep', params: { points: 3 } };
    } else if (heaviestBaseline.key.startsWith('habit_')) {
      tip = {
        messageKey: 'scoreInfoTipHabit',
        params: { points: Math.abs(heaviestBaseline.value) },
      };
    }
  } else if (clampedTotal >= 60) {
    tip = { messageKey: 'scoreInfoTipHealthy' };
  }

  return {
    baseline: {
      start: 60,
      items: baselineItems,
      rawTotal,
      clampedTotal,
    },
    today: {
      items: todayItems,
      debtItem,
      positiveTotal,
      positiveClamped,
      final,
    },
    tip,
  };
}

/**
 * Calculate a day's dopamine score by combining all signals.
 *
 * Ingredients (matches the in-app explanation modal):
 *   - Task bonus: up to 50 pt with diminishing returns
 *   - Check-in influence: ±10 pt around a 3.0 midpoint (1=-10, 3=0, 5=+10)
 *   - Streak bonus: +1 pt per consecutive day, max +10
 *   - Steps bonus: proportional to step goal, max +10 at goal
 *   - Challenge bonus: +5 pt per challenge today, max +15
 *   - Urge-resistance bonus: +3 pt per urge met with intervention, max +15
 *   - Debt penalty: subtract bad-habit debt points (always visible)
 *
 * The positive ingredients are clamped to 100 BEFORE debt subtracts, so debt
 * always produces a visible drop instead of disappearing into headroom above
 * the cap.
 *
 * All optional inputs default to neutral (0), so legacy callers that only
 * pass tasks + baseScore keep the same task-only behavior.
 */
export function calculateDayScore(
  completedTaskIds: string[],
  baseScore: number,
  ctx: {
    checkInAvg?: number | null;
    streak?: number;
    steps?: number;
    stepGoal?: number;
    debtPoints?: number;
    challengesToday?: number;
    urgesResisted?: number;
  } = {},
): number {
  // Task bonus with diminishing returns (max 50 pt)
  let rawPoints = 0;
  for (const id of completedTaskIds) {
    const task = TASKS.find((t) => t.id === id);
    if (task) rawPoints += task.points;
  }
  let taskBonus = Math.min(30, rawPoints);
  if (rawPoints > 30) {
    taskBonus += Math.min(20, (rawPoints - 30) * 0.5);
  }

  // Check-in influence (±10 pt around 3.0 midpoint)
  const checkInBonus = ctx.checkInAvg != null ? (ctx.checkInAvg - 3) * 5 : 0;

  // Streak bonus (+1/day, max +10)
  const streakBonus = ctx.streak ? Math.min(10, ctx.streak) : 0;

  // Steps bonus (proportional to goal, max +10)
  const stepsBonus =
    ctx.steps && ctx.stepGoal && ctx.stepGoal > 0
      ? Math.min(10, (ctx.steps / ctx.stepGoal) * 10)
      : 0;

  // Challenge bonus (+5 pt per challenge completed today, max +15)
  const challengeBonus = ctx.challengesToday
    ? Math.min(15, ctx.challengesToday * 5)
    : 0;

  // Urge-resistance bonus (+3 pt per urge met with the intervention, max +15)
  const urgeBonus = ctx.urgesResisted
    ? Math.min(15, ctx.urgesResisted * 3)
    : 0;

  // Debt penalty
  const debtPenalty = ctx.debtPoints ?? 0;

  // Clamp the positive side to 100 BEFORE debt, so debt always shows up.
  const positiveTotal =
    baseScore + taskBonus + checkInBonus + streakBonus + stepsBonus + challengeBonus + urgeBonus;
  const positiveClamped = Math.max(0, Math.min(positiveTotal, 100));
  return Math.max(0, positiveClamped - debtPenalty);
}

/**
 * Calculate current streak from daily logs.
 *
 * A "streak day" has at least one task completed. TODAY is never counted —
 * the streak shows what you've already built up; tomorrow it grows by 1 if
 * today had at least one task. This avoids mid-day flicker from un-toggling
 * a task and keeps the streak monotonic during the day.
 */
export function calculateStreak(
  dailyLogs: Record<string, DailyLog>
): number {
  let streak = 0;
  // Start from yesterday — today is "in progress" and not counted yet.
  let d = getPreviousDay(getTodayString());

  for (let i = 0; i < 365; i++) {
    const log = dailyLogs[d];
    if (log && log.completedTasks.length > 0) {
      streak++;
      d = getPreviousDay(d);
    } else {
      break;
    }
  }
  return streak;
}

/**
 * Display cap for debt — beyond this users get "{N}+" because debt has already
 * crushed the score to 0 and further additions have no further visible effect.
 */
export const DEBT_DISPLAY_CAP = 50;

/**
 * Format a debt value for display. Anything ≥ DEBT_DISPLAY_CAP becomes
 * "{cap}+" so the number stops chasing the user.
 */
export function formatDebtDisplay(debt: number): string {
  if (debt >= DEBT_DISPLAY_CAP) return `${DEBT_DISPLAY_CAP}+`;
  return String(Math.round(debt));
}

/**
 * Consistent today-score read with a personalized fallback. When the daily
 * log isn't yet hydrated (first render, first day) we fall back to the
 * user's baseline rather than 0 or an arbitrary 40 — keeps Today-tab UI
 * (greeting, bg color, BrainTodayCard) from flashing mismatched states.
 */
export function getTodayScore(
  dailyLogs: Record<string, DailyLog>,
  profile: UserProfile | null,
): number {
  const today = getTodayString();
  const log = dailyLogs[today];
  if (log && typeof log.dopamineScore === 'number') return log.dopamineScore;
  return profile ? getInitialScore(profile) : 40;
}

/**
 * Get last N days' scores for charting.
 */
export function getScoreHistory(
  dailyLogs: Record<string, DailyLog>,
  days = 7,
  labels?: { today: string; yesterday: string; locale?: string }
): { date: string; label: string; score: number }[] {
  const result = [];
  for (let i = days - 1; i >= 0; i--) {
    const d = getDateString(i);
    const log = dailyLogs[d];
    result.push({
      date: d,
      label: getDayLabel(d, i, labels),
      score: log?.dopamineScore ?? 0,
    });
  }
  return result;
}

export function toLocalDateString(d: Date): string {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${dd}`;
}

export function getTodayString(): string {
  return toLocalDateString(new Date());
}

/**
 * ISO-style week key — the Monday of the week, formatted as YYYY-MM-DD.
 * Weeks run Monday → Sunday. Resets fire when this key changes.
 */
export function getWeekKey(d: Date = new Date()): string {
  const dayOfWeek = (d.getDay() + 6) % 7; // 0 = Monday
  const monday = new Date(d.getFullYear(), d.getMonth(), d.getDate() - dayOfWeek);
  return toLocalDateString(monday);
}

function getDateString(daysAgo: number): string {
  const d = new Date();
  d.setDate(d.getDate() - daysAgo);
  return toLocalDateString(d);
}

function getPreviousDay(dateStr: string): string {
  const d = new Date(dateStr + 'T12:00:00');
  d.setDate(d.getDate() - 1);
  return toLocalDateString(d);
}

function getDayLabel(
  dateStr: string,
  daysAgo: number,
  labels?: { today: string; yesterday: string; locale?: string }
): string {
  if (daysAgo === 0) return labels?.today ?? 'Today';
  if (daysAgo === 1) return labels?.yesterday ?? 'Yesterday';
  const d = new Date(dateStr + 'T12:00:00');
  return d.toLocaleDateString(labels?.locale ?? 'en', { weekday: 'short' });
}

export function getPlanDay(startDate: string): number {
  const start = new Date(startDate + 'T12:00:00');
  const today = new Date();
  const diff = Math.floor(
    (today.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)
  );
  return Math.max(1, diff + 1);
}

export function getScoreLabel(score: number): string {
  if (score < 20) return 'Depleted';
  if (score < 35) return 'Low';
  if (score < 50) return 'Recovering';
  if (score < 65) return 'Balanced';
  if (score < 80) return 'Energized';
  return 'Optimal';
}

export const BAD_HABIT_DEBT: Record<BadHabit['type'], number> = {
  scrolling: 10,
  porn: 15,
  junk_food: 8,
  sugar: 5,
  gaming: 12,
  alcohol: 10,
  caffeine: 4,
  other: 6,
};

export function calculateDailyDebt(badHabits: BadHabit[] = []): number {
  return badHabits.reduce((sum, h) => sum + h.debtPoints, 0);
}

export type DebtLabelKey = 'debtClean' | 'debtLow' | 'debtBuilding' | 'debtHigh' | 'debtCritical';

export function getDebtLabel(debt: number): DebtLabelKey {
  if (debt === 0) return 'debtClean';
  if (debt < 15) return 'debtLow';
  if (debt < 30) return 'debtBuilding';
  if (debt < 50) return 'debtHigh';
  return 'debtCritical';
}

export function getDebtColor(debt: number): string {
  if (debt === 0) return '#5B8A5E';
  if (debt < 15) return '#8DAF8F';
  if (debt < 30) return '#E4A85A';
  if (debt < 50) return '#D97070';
  return '#B94040';
}

export function getScoreColor(score: number): string {
  if (score < 35) return '#D97070';
  if (score < 50) return '#E4A85A';
  if (score < 65) return '#C9955A';
  if (score < 80) return '#5B8A5E';
  return '#3D6640';
}

/**
 * Average a daily check-in field over the last N days.
 * Returns null when there are no check-ins in that window.
 */
export function getCheckInAverage(
  dailyLogs: Record<string, DailyLog>,
  days: number,
  field: 'sleep' | 'energy' | 'mood',
): number | null {
  const today = new Date(getTodayString());
  let sum = 0;
  let count = 0;
  for (let i = 0; i < days; i++) {
    const d = new Date(today);
    d.setDate(today.getDate() - i);
    const key = toLocalDateString(d);
    const checkIn = dailyLogs[key]?.checkIn;
    if (checkIn) {
      sum += checkIn[field];
      count++;
    }
  }
  return count > 0 ? sum / count : null;
}
