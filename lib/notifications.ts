import { LocalNotifications } from '@capacitor/local-notifications';

// Reserve ID ranges so morning + evening schedules don't collide.
const MORNING_ID_BASE = 1001;
const EVENING_ID_BASE = 1100;
const SCHEDULE_DAYS = 14;

function isCapacitor(): boolean {
  if (typeof window === 'undefined') return false;
  const cap = (window as unknown as { Capacitor?: { isNativePlatform?: () => boolean } }).Capacitor;
  return !!cap?.isNativePlatform?.();
}

export async function requestNotificationPermission(): Promise<'granted' | 'denied' | 'unavailable'> {
  if (!isCapacitor()) return 'unavailable';
  try {
    const current = await LocalNotifications.checkPermissions();
    if (current.display === 'granted') return 'granted';
    const result = await LocalNotifications.requestPermissions();
    return result.display === 'granted' ? 'granted' : 'denied';
  } catch (e) {
    console.error('[Notif] permission error:', e);
    return 'denied';
  }
}

function buildScheduleDates(time: { hour: number; minute: number }, days: number): Date[] {
  const out: Date[] = [];
  const now = new Date();
  for (let i = 0; i < days; i++) {
    const d = new Date(now);
    d.setDate(d.getDate() + i);
    d.setHours(time.hour, time.minute, 0, 0);
    if (i === 0 && d.getTime() <= now.getTime()) continue;
    out.push(d);
  }
  return out;
}

async function cancelRange(idBase: number, count: number): Promise<void> {
  if (!isCapacitor()) return;
  try {
    await LocalNotifications.cancel({
      notifications: Array.from({ length: count }, (_, i) => ({ id: idBase + i })),
    });
  } catch (e) {
    console.error('[Notif] cancel error:', e);
  }
}

export async function scheduleDailyCheckInReminder(
  time: { hour: number; minute: number },
  title: string,
  bodyRotation: string[],
): Promise<boolean> {
  if (!isCapacitor()) return false;
  if (bodyRotation.length === 0) return false;
  try {
    await cancelRange(MORNING_ID_BASE, SCHEDULE_DAYS);
    const dates = buildScheduleDates(time, SCHEDULE_DAYS);
    await LocalNotifications.schedule({
      notifications: dates.map((at, i) => ({
        id: MORNING_ID_BASE + i,
        title,
        // Day-of-week → predictable rotation: Mon=index 0, Sun=index 6.
        body: bodyRotation[((at.getDay() + 6) % 7) % bodyRotation.length],
        schedule: { at, allowWhileIdle: true },
      })),
    });
    return true;
  } catch (e) {
    console.error('[Notif] schedule error:', e);
    return false;
  }
}

export async function cancelCheckInReminder(): Promise<void> {
  await cancelRange(MORNING_ID_BASE, SCHEDULE_DAYS);
}

export async function scheduleEveningReflection(
  time: { hour: number; minute: number },
  title: string,
  bodyRotation: string[],
): Promise<boolean> {
  if (!isCapacitor()) return false;
  if (bodyRotation.length === 0) return false;
  try {
    await cancelRange(EVENING_ID_BASE, SCHEDULE_DAYS);
    const dates = buildScheduleDates(time, SCHEDULE_DAYS);
    await LocalNotifications.schedule({
      notifications: dates.map((at, i) => ({
        id: EVENING_ID_BASE + i,
        title,
        body: bodyRotation[i % bodyRotation.length],
        schedule: { at, allowWhileIdle: true },
      })),
    });
    return true;
  } catch (e) {
    console.error('[Notif] evening schedule error:', e);
    return false;
  }
}

export async function cancelEveningReflection(): Promise<void> {
  await cancelRange(EVENING_ID_BASE, SCHEDULE_DAYS);
}
