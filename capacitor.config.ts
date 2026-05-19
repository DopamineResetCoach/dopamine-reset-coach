import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.dopaminereset.coach',
  appName: 'Dopamine Reset Coach',
  webDir: 'out',
  ios: {
    contentInset: 'never',
  },
  server: {
    // Keeps localStorage working on iOS WKWebView (same origin)
    iosScheme: 'ionic',
  },
};

export default config;
