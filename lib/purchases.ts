import { Purchases, LOG_LEVEL } from '@revenuecat/purchases-capacitor';
import type { CustomerInfo, PurchasesPackage } from '@revenuecat/purchases-capacitor';

const API_KEY = process.env.NEXT_PUBLIC_REVENUECAT_API_KEY ?? '';

// Entitlement ID zoals geconfigureerd in RevenueCat dashboard
export const PREMIUM_ENTITLEMENT_ID = 'Brain Dopamine reset Pro';

let initialized = false;

/**
 * Initialiseer RevenueCat. Aanroepen zodra de app start (eenmalig).
 * Doet niets buiten een echte iOS Capacitor omgeving.
 * @param onPremiumChange - callback die aangeroepen wordt bij statuswijziging
 */
export async function initializePurchases(
  onPremiumChange?: (isPremium: boolean) => void
): Promise<void> {
  if (initialized) return;
  if (typeof window === 'undefined') return; // SSR guard

  const cap = (window as unknown as { Capacitor?: { isNativePlatform?: () => boolean } }).Capacitor;
  if (!cap?.isNativePlatform?.()) return; // Alleen op device/simulator uitvoeren

  try {
    await Purchases.setLogLevel({ level: LOG_LEVEL.ERROR });
    await Purchases.configure({ apiKey: API_KEY });
    initialized = true;

    if (onPremiumChange) {
      // Sync premium status wanneer een aankoop wordt afgerond (bijv. na App Store sheet)
      await Purchases.addCustomerInfoUpdateListener((customerInfo: CustomerInfo) => {
        onPremiumChange(isPremiumActive(customerInfo));
      });

      // Sync premium status wanneer de app vanuit de achtergrond terugkomt
      document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'visible') {
          const premium = await checkPremiumStatus();
          onPremiumChange(premium);
        }
      });
    }
  } catch (e) {
    console.error('[Purchases] configure error:', e);
  }
}

/**
 * Haal de huidige premium-status op uit RevenueCat.
 * Returns true als de 'premium' entitlement actief is.
 */
export async function checkPremiumStatus(): Promise<boolean> {
  if (!initialized) return false;
  try {
    const { customerInfo } = await Purchases.getCustomerInfo();
    return isPremiumActive(customerInfo);
  } catch (e) {
    console.error('[Purchases] getCustomerInfo error:', e);
    return false;
  }
}

/**
 * Haal alle beschikbare premium pakketten op (weekly + monthly).
 */
export async function getPremiumPackages(): Promise<PurchasesPackage[]> {
  if (!initialized) return [];
  try {
    const { current } = await Purchases.getOfferings();
    if (!current) return [];
    return current.availablePackages;
  } catch (e) {
    console.error('[Purchases] getOfferings error:', e);
    return [];
  }
}

/**
 * Haal het standaard premium pakket op (monthly, dan eerste beschikbare).
 * Returns null als er geen aanbod beschikbaar is.
 */
export async function getPremiumPackage(): Promise<PurchasesPackage | null> {
  if (!initialized) return null;
  try {
    const { current } = await Purchases.getOfferings();
    if (!current) return null;
    return current.monthly ?? current.availablePackages[0] ?? null;
  } catch (e) {
    console.error('[Purchases] getOfferings error:', e);
    return null;
  }
}

/**
 * Koop het premium pakket. Gooit een error als de aankoop mislukt.
 * Returns true bij succes, false als de gebruiker annuleert.
 */
export async function purchasePremiumPackage(specificPackage?: PurchasesPackage): Promise<boolean> {
  if (!initialized) throw new Error('RevenueCat not initialized');

  const pkg = specificPackage ?? await getPremiumPackage();
  if (!pkg) throw new Error('Geen premium pakket gevonden. Controleer RevenueCat dashboard.');

  try {
    const result = await Purchases.purchasePackage({ aPackage: pkg });
    return isPremiumActive(result.customerInfo);
  } catch (e: unknown) {
    // userCancelled = geen error tonen
    if (isUserCancelledError(e)) return false;
    throw e;
  }
}

/**
 * Herstel eerdere aankopen (vereist door Apple App Store review).
 * Returns true als premium hersteld is.
 */
export async function restorePurchases(): Promise<boolean> {
  if (!initialized) throw new Error('RevenueCat not initialized');
  try {
    const { customerInfo } = await Purchases.restorePurchases();
    return isPremiumActive(customerInfo);
  } catch (e) {
    console.error('[Purchases] restorePurchases error:', e);
    throw e;
  }
}

// --- helpers ---

function isPremiumActive(customerInfo: CustomerInfo): boolean {
  return !!customerInfo.entitlements.active[PREMIUM_ENTITLEMENT_ID];
}

function isUserCancelledError(e: unknown): boolean {
  if (typeof e === 'object' && e !== null) {
    const err = e as Record<string, unknown>;
    // RevenueCat userCancelled vlag
    if (err['userCancelled'] === true) return true;
    // Sommige versies gooien een string error
    if (typeof err['message'] === 'string' && err['message'].includes('cancelled')) return true;
  }
  return false;
}
