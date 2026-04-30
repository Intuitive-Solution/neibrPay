type GtagFn = (...args: unknown[]) => void;

export function trackGtagEvent(
  eventName: string,
  eventParams?: Record<string, string | number | boolean | undefined>
): void {
  if (!import.meta.client) return;
  const gtag = (window as Window & { gtag?: GtagFn }).gtag;
  if (typeof gtag !== 'function') return;
  gtag('event', eventName, eventParams ?? {});
}
