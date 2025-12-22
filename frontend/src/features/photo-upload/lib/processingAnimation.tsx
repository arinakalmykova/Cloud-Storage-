export function startDotsAnimation(setStatus: (s: string) => void) {
  let dots = 0;

  const intervalId = window.setInterval(() => {
    dots = (dots + 1) % 4;
    setStatus('Сжимаем в WebP' + '.'.repeat(dots));
  }, 800);

  return () => {
    clearInterval(intervalId);
  };
}
