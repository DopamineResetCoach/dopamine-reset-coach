import { LocalNotifications } from '@capacitor/local-notifications';

const CHECK_IN_NOTIFICATION_ID = 1001;

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

export async function scheduleDailyCheckInReminder(
  time: { hour: number; minute: number },
  title: string,
  body: string,
): Promise<boolean> {
  if (!isCapacitor()) return false;
  try {
    // Cancel any existing schedule first to avoid duplicates
    await LocalNotifications.cancel({ notifications: [{ id: CHECK_IN_NOTIFICATION_ID }] });

    await LocalNotifications.schedule({
      notifications: [
        {
          id: CHECK_IN_NOTIFICATION_ID,
          title,
          body,
          schedule: {
            on: { hour: time.hour, minute: time.minute },
            allowWhileIdle: true,
          },
        },
      ],
    });
    return true;
  } catch (e) {
    console.error('[Notif] schedule error:', e);
    return false;
  }
}

export async function cancelCheckInReminder(): Promise<void> {
  if (!isCapacitor()) return;
  try {
    await LocalNotifications.cancel({ notifications: [{ id: CHECK_IN_NOTIFICATION_ID }] });
  } catch (e) {
    console.error('[Notif] cancel error:', e);
  }
}
