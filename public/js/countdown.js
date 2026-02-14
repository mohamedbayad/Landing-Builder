document.addEventListener('DOMContentLoaded', function() {
    const timers = document.querySelectorAll('.countertimer');
    if (timers.length === 0) return;

    // 1. Determine Landing ID
    // Check meta tag first, then fallback to dataset on the first timer instance
    let landingId = document.querySelector('meta[name="landing-id"]')?.content;
    
    if (!landingId && timers[0].dataset.landingId) {
        landingId = timers[0].dataset.landingId;
    }

    if (!landingId) {
        console.warn('Countdown: No landing ID found.');
        return;
    }

    // 2. Fetch Countdown Data
    fetch(`/l/${landingId}/countdown`)
        .then(r => r.json())
        .then(data => {
            if (!data.enabled || data.remaining_seconds <= 0) {
                timers.forEach(t => t.style.display = 'none'); // Hide if disabled/expired
                return;
            }

            let remaining = data.remaining_seconds;

            // 3. Start Interval
            const updateTimers = () => {
                if (remaining < 0) remaining = 0;

                timers.forEach(timer => {
                    const daysEl = timer.querySelector('.days');
                    const hoursEl = timer.querySelector('.hours');
                    const minsEl = timer.querySelector('.mins');
                    const secsEl = timer.querySelector('.secs');

                    let d, h, m, s;

                    // If days element exists in this specific timer instance
                    if (daysEl) {
                        d = Math.floor(remaining / 86400); // 24*60*60
                        h = Math.floor((remaining % 86400) / 3600);
                    } else {
                        // No days element -> Hours can exceed 24
                        d = 0;
                        h = Math.floor(remaining / 3600);
                    }

                    m = Math.floor((remaining % 3600) / 60);
                    s = remaining % 60;

                    // Update DOM
                    const pad = (n) => n.toString().padStart(2, '0');

                    if (daysEl) daysEl.textContent = d; // Days can be > 99, no padding or maybe pad to 2? Standard is usually no pad for days if > 99 but pad if < 10? User example "Days + HH", usually days is just number. Let's keep it simple.
                    // Actually user didn't specify padding for days, but for hours/mins/secs usually 2 digits.
                    // Let's pad hours/mins/secs.
                    
                    if (hoursEl) hoursEl.textContent = pad(h);
                    if (minsEl) minsEl.textContent = pad(m);
                    if (secsEl) secsEl.textContent = pad(s);
                });

                if (remaining <= 0) {
                    clearInterval(interval);
                    // Optional: Add 'is-ended' class
                    timers.forEach(t => t.classList.add('is-ended'));
                }
                
                remaining--;
            };

            updateTimers(); // Initial run
            const interval = setInterval(updateTimers, 1000);
        })
        .catch(err => console.error('Countdown Error:', err));
});
