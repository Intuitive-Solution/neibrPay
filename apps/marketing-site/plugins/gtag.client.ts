const GA_ID = 'G-DB646XGZ88';

function isLocalHost(hostname: string): boolean {
  return (
    hostname === 'localhost' ||
    hostname === '127.0.0.1' ||
    hostname === '::1' ||
    hostname === '[::1]'
  );
}

export default defineNuxtPlugin(() => {
  if (!import.meta.client) return;
  if (isLocalHost(window.location.hostname)) return;

  const external = document.createElement('script');
  external.async = true;
  external.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`;
  document.head.appendChild(external);

  const inline = document.createElement('script');
  inline.textContent = `window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '${GA_ID}');`;
  document.head.appendChild(inline);
});
