'use client';

import { useState, useEffect } from 'react';
import { useAppStore } from '@/store/useAppStore';
import { useT } from '@/hooks/useT';
import type { PurchasesPackage } from '@revenuecat/purchases-capacitor';
import { PACKAGE_TYPE } from '@revenuecat/purchases-typescript-internal-esm';
import LegalModal from '@/components/legal/LegalModal';
import BottomSheet from '@/components/ui/BottomSheet';

interface PremiumModalProps {
  onClose: () => void;
}

type PurchaseState = 'idle' | 'loading' | 'error';
type LegalOpen = null | 'privacy' | 'terms';

export default function PremiumModal({ onClose }: PremiumModalProps) {
  const t = useT();
  const purchasePremium = useAppStore((s) => s.purchasePremium);
  const getPremiumPackages = useAppStore((s) => s.getPremiumPackages);
  const [state, setState] = useState<PurchaseState>('idle');
  const [errorMsg, setErrorMsg] = useState('');
  const [packages, setPackages] = useState<PurchasesPackage[]>([]);
  const [selectedPkg, setSelectedPkg] = useState<PurchasesPackage | null>(null);
  const [legalOpen, setLegalOpen] = useState<LegalOpen>(null);

  const FEATURES = [
    { emoji: '💸', text: t.premiumFeature1 },
    { emoji: '🧠', text: t.premiumFeature2 },
    { emoji: '⚡', text: t.premiumFeature3 },
    { emoji: '📊', text: t.premiumFeature4 },
    { emoji: '🎨', text: t.premiumFeature5 },
  ];

  useEffect(() => {
    const order = [PACKAGE_TYPE.WEEKLY, PACKAGE_TYPE.MONTHLY, PACKAGE_TYPE.ANNUAL, PACKAGE_TYPE.LIFETIME];
    const applyPackages = (pkgs: PurchasesPackage[]) => {
      const sorted = [...pkgs].sort((a, b) => {
        const ai = order.indexOf(a.packageType as PACKAGE_TYPE);
        const bi = order.indexOf(b.packageType as PACKAGE_TYPE);
        return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
      });
      setPackages(sorted);
      // Default selectie: Annual (beste deal pushen), anders Monthly, anders eerste
      const annual = sorted.find((p) => p.packageType === PACKAGE_TYPE.ANNUAL);
      const monthly = sorted.find((p) => p.packageType === PACKAGE_TYPE.MONTHLY);
      setSelectedPkg(annual ?? monthly ?? sorted[0] ?? null);
    };

    // Dev mock voor screenshots — alleen actief als window.__MOCK_PAYWALL_PACKAGES__ is gezet
    if (typeof window !== 'undefined') {
      const mock = (window as unknown as Record<string, unknown>)['__MOCK_PAYWALL_PACKAGES__'];
      if (Array.isArray(mock) && mock.length > 0) {
        applyPackages(mock as PurchasesPackage[]);
        return;
      }
    }

    getPremiumPackages().then(applyPackages);
  }, [getPremiumPackages]);

  const handleUnlock = async () => {
    setState('loading');
    setErrorMsg('');
    const result = await purchasePremium(selectedPkg ?? undefined);
    if (result === 'success') {
      onClose();
    } else if (result === 'cancelled') {
      setState('idle');
    } else {
      setState('error');
      setErrorMsg(t.purchaseError);
    }
  };

  const getSavingsVsMonthly = (pkg: PurchasesPackage): number | null => {
    if ((pkg.packageType as PACKAGE_TYPE) !== PACKAGE_TYPE.ANNUAL) return null;
    const monthly = packages.find((p) => p.packageType === PACKAGE_TYPE.MONTHLY);
    if (!monthly) return null;
    const annualEquivalent = monthly.product.price * 12;
    if (annualEquivalent <= 0) return null;
    const savings = 1 - pkg.product.price / annualEquivalent;
    if (savings <= 0.05) return null;
    return Math.round(savings * 100);
  };

  const formatPackageLabel = (pkg: PurchasesPackage) => {
    const type = pkg.packageType as PACKAGE_TYPE;
    if (type === PACKAGE_TYPE.WEEKLY) return t.pkgWeekly;
    if (type === PACKAGE_TYPE.MONTHLY) return t.pkgMonthly;
    if (type === PACKAGE_TYPE.ANNUAL) return t.pkgAnnual;
    if (type === PACKAGE_TYPE.LIFETIME) return t.pkgLifetime;
    return pkg.product.title;
  };

  // Bulletproof subscription title derived from packageType only — never from
  // product.title (which can be misconfigured in App Store Connect and caused
  // Apple's "weekly shown as yearly" rejection). Uses English labels for App
  // Store review clarity.
  const getSubscriptionTitle = (pkg: PurchasesPackage): string => {
    const type = pkg.packageType as PACKAGE_TYPE;
    if (type === PACKAGE_TYPE.WEEKLY) return 'Dopamine Reset Pro — Weekly';
    if (type === PACKAGE_TYPE.MONTHLY) return 'Dopamine Reset Pro — Monthly';
    if (type === PACKAGE_TYPE.ANNUAL) return 'Dopamine Reset Pro — Yearly';
    if (type === PACKAGE_TYPE.LIFETIME) return 'Dopamine Reset Pro — Lifetime';
    return 'Dopamine Reset Pro';
  };

  const getSubscriptionLength = (pkg: PurchasesPackage): string => {
    const type = pkg.packageType as PACKAGE_TYPE;
    if (type === PACKAGE_TYPE.WEEKLY) return t.paywallLengthWeekly;
    if (type === PACKAGE_TYPE.MONTHLY) return t.paywallLengthMonthly;
    if (type === PACKAGE_TYPE.ANNUAL) return t.paywallLengthAnnual;
    if (type === PACKAGE_TYPE.LIFETIME) return t.paywallLengthLifetime;
    return '—';
  };

  const getPerPeriod = (pkg: PurchasesPackage): string => {
    const type = pkg.packageType as PACKAGE_TYPE;
    if (type === PACKAGE_TYPE.WEEKLY) return t.paywallPerWeekly;
    if (type === PACKAGE_TYPE.MONTHLY) return t.paywallPerMonthly;
    if (type === PACKAGE_TYPE.ANNUAL) return t.paywallPerAnnual;
    if (type === PACKAGE_TYPE.LIFETIME) return t.paywallPerLifetime;
    return '';
  };

  const getPrice = (pkg: PurchasesPackage) =>
    pkg.product.priceString ?? '—';

  return (
    <>
    <BottomSheet onClose={onClose} disabled={state === 'loading'} paddingBottom="pb-10">
      <div className="text-center mb-6">
          <p className="text-xs font-semibold uppercase tracking-widest text-[#5B8A5E] mb-1">
            {t.premiumLabel}
          </p>
          <h2 className="text-2xl font-bold text-stone-800">{t.premiumTitle}</h2>
          <p className="text-stone-400 text-sm mt-2 whitespace-pre-line">{t.premiumSub}</p>
        </div>

        <div className="space-y-3 mb-6">
          {FEATURES.map((f) => (
            <div key={f.text} className="flex items-start gap-3">
              <span className="text-xl w-7 flex-shrink-0">{f.emoji}</span>
              <p className="text-stone-700 text-sm leading-snug">{f.text}</p>
            </div>
          ))}
        </div>

        {packages.length > 1 && (
          <div className="flex gap-2 mb-4 pt-2">
            {packages.map((pkg) => {
              const isSelected = selectedPkg?.packageType === pkg.packageType as PACKAGE_TYPE;
              const savings = getSavingsVsMonthly(pkg);
              return (
                <button
                  key={pkg.packageType}
                  onClick={() => setSelectedPkg(pkg)}
                  className="relative flex-1 py-3 rounded-2xl border-2 text-center transition-all"
                  style={{
                    borderColor: isSelected ? '#5B8A5E' : '#e7e5e4',
                    background: isSelected ? '#f0f7f0' : 'white',
                  }}
                >
                  {savings !== null && (
                    <span
                      className="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full text-[9px] font-bold text-white whitespace-nowrap shadow-sm"
                      style={{ background: '#5B8A5E' }}
                    >
                      {t.premiumSave.replace('{n}', String(savings))}
                    </span>
                  )}
                  <p className="text-xs text-stone-500">{formatPackageLabel(pkg)}</p>
                  <p className="font-bold text-stone-800 text-sm">{getPrice(pkg)}</p>
                </button>
              );
            })}
          </div>
        )}

        {/* Subscription details — required by Guideline 3.1.2(c).
            Title + length are computed from packageType (never from
            product.title, which can be misconfigured in App Store Connect). */}
        {selectedPkg && (
          <div className="bg-stone-50 rounded-2xl px-4 py-3 mb-4 text-xs text-stone-500 space-y-1">
            <div className="flex justify-between gap-3">
              <span>{t.paywallSubscription}</span>
              <span className="font-semibold text-stone-700 text-right">
                {getSubscriptionTitle(selectedPkg)}
              </span>
            </div>
            <div className="flex justify-between gap-3">
              <span>{t.paywallLength}</span>
              <span className="font-semibold text-stone-700 text-right">
                {getSubscriptionLength(selectedPkg)}
              </span>
            </div>
            <div className="flex justify-between gap-3">
              <span>{t.paywallPrice}</span>
              <span className="font-semibold text-stone-700 text-right">
                {getPrice(selectedPkg)} {getPerPeriod(selectedPkg)}
              </span>
            </div>
          </div>
        )}

        {errorMsg ? (
          <p className="text-red-500 text-xs text-center mb-3">{errorMsg}</p>
        ) : null}

        <button
          className="w-full py-4 rounded-2xl font-bold text-white text-base flex items-center justify-center gap-2 disabled:opacity-60"
          style={{ background: 'linear-gradient(135deg, #5B8A5E, #3D6640)' }}
          onClick={handleUnlock}
          disabled={state === 'loading'}
        >
          {state === 'loading' ? (
            <>
              <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
              {t.purchaseLoading}
            </>
          ) : selectedPkg ? (
            `${t.premiumBtn} — ${getPrice(selectedPkg)}`
          ) : (
            t.premiumBtn
          )}
        </button>

        <p className="text-stone-400 text-xs text-center mt-3">{t.premiumBilling}</p>

        {/* Required by Guideline 3.1.2(c) — Privacy Policy + Terms links.
            Opens inline LegalModal so content is always available (no external
            URL dependency, no network required). */}
        <div className="flex items-center justify-center gap-4 mt-3 pt-3 border-t border-stone-100">
          <button
            onClick={() => setLegalOpen('privacy')}
            className="text-xs text-[#5B8A5E] font-medium underline"
          >
            {t.privacyPolicy ?? 'Privacy Policy'}
          </button>
          <span className="text-stone-300 text-xs">·</span>
          <button
            onClick={() => setLegalOpen('terms')}
            className="text-xs text-[#5B8A5E] font-medium underline"
          >
            {t.termsOfUse ?? 'Terms of Use (EULA)'}
          </button>
        </div>
      </BottomSheet>

      {legalOpen && (
        <LegalModal kind={legalOpen} onClose={() => setLegalOpen(null)} />
      )}
    </>
  );
}
