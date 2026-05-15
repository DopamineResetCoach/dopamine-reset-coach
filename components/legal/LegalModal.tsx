'use client';

type LegalKind = 'privacy' | 'terms';

interface LegalModalProps {
  kind: LegalKind;
  onClose: () => void;
}

export default function LegalModal({ kind, onClose }: LegalModalProps) {
  const isPrivacy = kind === 'privacy';

  return (
    <div
      className="fixed inset-0 z-[60] flex items-end justify-center"
      style={{ background: 'rgba(0,0,0,0.6)' }}
      onClick={onClose}
    >
      <div
        className="w-full max-w-sm bg-white rounded-t-3xl pb-6 max-h-[85vh] flex flex-col"
        style={{ animation: 'slide-up 0.25s ease-out' }}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="px-6 pt-4 pb-3 border-b border-stone-100 flex items-center justify-between flex-shrink-0">
          <h2 className="text-base font-bold text-stone-800">
            {isPrivacy ? 'Privacy Policy' : 'Terms of Use (EULA)'}
          </h2>
          <button
            onClick={onClose}
            className="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center text-stone-600"
          >
            ✕
          </button>
        </div>

        <div className="overflow-y-auto px-6 py-4 text-sm text-stone-700 leading-relaxed">
          {isPrivacy ? <PrivacyContent /> : <TermsContent />}
        </div>
      </div>
    </div>
  );
}

function PrivacyContent() {
  return (
    <div className="space-y-4">
      <p className="text-stone-400 text-xs">Last updated: April 2026</p>

      <p>
        Dopamine Reset Coach (&quot;the App&quot;) is designed with privacy as a core principle.
        This Privacy Policy explains what data the App handles.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">Data we do NOT collect</h3>
      <ul className="list-disc pl-5 space-y-1">
        <li>No account required — we do not collect your name, email, or any identity information.</li>
        <li>No analytics tracking of personal behavior.</li>
        <li>No advertising identifiers.</li>
        <li>No third-party trackers.</li>
      </ul>

      <h3 className="font-bold text-stone-800 mt-4">Data stored locally on your device</h3>
      <p>
        All your progress, preferences, habit logs, and streaks are stored locally in your
        device&apos;s storage. This data never leaves your device. If you delete the App,
        this data is removed.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">Apple Health / HealthKit</h3>
      <p>
        If you grant permission, the App reads your daily step count from Apple Health to
        calculate your Stride Score. Step data is used only within the App and is never
        transmitted to any server. We only READ steps; we never write any data to Apple Health.
        You can revoke this permission at any time in iOS Settings → Privacy & Security → Health.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">Subscriptions (RevenueCat)</h3>
      <p>
        Premium subscriptions are processed by Apple and managed through RevenueCat
        (our subscription infrastructure provider). RevenueCat receives an anonymous
        subscriber identifier to verify your subscription status. No personally
        identifying information is shared.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">Your rights</h3>
      <p>
        You can delete all your data at any time via Settings → Reset App, or by
        uninstalling the App. Since no data is stored on our servers, there is
        nothing for us to retain.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">Contact</h3>
      <p>
        Questions about this policy? Contact the developer through the App Store listing
        or the support email provided in App Store Connect.
      </p>
    </div>
  );
}

function TermsContent() {
  return (
    <div className="space-y-4">
      <p className="text-stone-400 text-xs">Last updated: April 2026</p>

      <p>
        By downloading, installing, or using Dopamine Reset Coach (&quot;the App&quot;), you
        agree to the following Terms of Use.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">1. Not medical advice</h3>
      <p>
        The App is a self-guided habit and focus tool. It is NOT medical advice,
        therapy, or a substitute for professional mental health care. If you are
        struggling with addiction, depression, or any serious health condition, please
        consult a licensed healthcare provider.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">2. Subscriptions</h3>
      <ul className="list-disc pl-5 space-y-1">
        <li>Premium is available as a weekly or monthly auto-renewing subscription.</li>
        <li>Payment is charged to your Apple ID at purchase confirmation.</li>
        <li>
          Subscriptions auto-renew unless cancelled at least 24 hours before the end
          of the current period.
        </li>
        <li>
          Manage or cancel anytime via iOS Settings → [your name] → Subscriptions.
        </li>
        <li>
          Pricing is shown in the App and on the App Store product page. Prices may
          vary by region.
        </li>
      </ul>

      <h3 className="font-bold text-stone-800 mt-4">3. Intellectual property</h3>
      <p>
        All content, design, text, and code in the App are the property of the
        developer. You may not copy, redistribute, reverse-engineer, or create
        derivative works without permission.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">4. Acceptable use</h3>
      <p>
        You agree to use the App for its intended purpose (personal habit tracking).
        You will not attempt to exploit, tamper with, or bypass payment mechanisms.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">5. Disclaimers</h3>
      <p>
        The App is provided &quot;as is&quot; without warranty. The developer is not
        liable for outcomes resulting from use of the App, including but not limited
        to loss of data, missed goals, or decisions influenced by the content.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">6. Changes</h3>
      <p>
        These Terms may be updated. Continued use of the App after updates constitutes
        acceptance of the new Terms.
      </p>

      <h3 className="font-bold text-stone-800 mt-4">7. Governing law</h3>
      <p>
        These Terms are governed by the laws of the Netherlands. Any disputes shall
        be resolved in the competent courts of the Netherlands.
      </p>
    </div>
  );
}
