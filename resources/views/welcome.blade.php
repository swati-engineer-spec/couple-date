<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>A little date proposal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --pink: #df4f91; --ink: #4c2b39; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: var(--ink); font-family: 'DM Sans', sans-serif; background: #f4dfe4; }
        .scene { min-height: 100vh; display: grid; place-items: center; padding: 34px 18px; overflow: hidden; position: relative; }
        .scene::before, .scene::after { content: ''; position: absolute; width: 260px; height: 260px; border-radius: 50%; background: rgba(255,255,255,.25); }
        .scene::before { top: -110px; left: -80px; } .scene::after { right: -110px; bottom: -80px; }
        .app-shell { position: relative; z-index: 1; width: min(100%, 438px); }
        .brand { text-align: center; color: #a64d6f; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; margin: 0 0 14px; }
        .recipient-name { color: #c2437d; font-size: 14px; font-weight: 700; letter-spacing: .05em; margin: 0 auto 10px; }
        .card { display: none; background: rgba(255,253,249,.94); border: 1px solid rgba(255,255,255,.75); border-radius: 24px; padding: clamp(26px, 7vw, 44px) clamp(21px, 7vw, 48px); box-shadow: 0 24px 70px rgba(129,55,83,.18); text-align: center; animation: rise .55s ease both; }
        .card.active { display: block; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .avatar { width: 76px; height: 76px; display: grid; place-items: center; margin: 0 auto 18px; border-radius: 24px; background: #f1edf0; font-size: 42px; }
        .photo-frame { width: 128px; height: 128px; padding: 6px; position: relative; margin: 0 auto 18px; border-radius: 50%; background: linear-gradient(145deg, #fff, #f3b5cc); box-shadow: 0 12px 24px rgba(173, 62, 112, .18), 0 0 0 8px rgba(255,255,255,.55); }
        .photo-frame::before, .photo-frame::after { content: '♥'; position: absolute; color: #df4f91; font-size: 18px; }
        .photo-frame::before { top: -8px; right: 4px; transform: rotate(15deg); } .photo-frame::after { bottom: 4px; left: -8px; color: #e9a2bf; transform: rotate(-18deg); }
        .photo-frame img { width: 100%; height: 100%; display: block; border-radius: 50%; object-fit: cover; object-position: center; }
        h1, h2 { font-family: 'Playfair Display', serif; margin: 0; color: #502c3a; line-height: 1.1; }
        h1 { font-size: clamp(30px, 8vw, 42px); } h2 { font-size: clamp(27px, 7vw, 36px); }
        .subtitle { color: #9e6b7d; font-size: 14px; line-height: 1.7; margin: 16px auto 28px; max-width: 280px; }
        .flower { color: #e987ad; font-size: 20px; vertical-align: 2px; }
        .heart-note { color: #d95391; font-size: 17px; letter-spacing: .3em; margin: 0 auto 18px; }
        .button-row { display: flex; gap: 12px; justify-content: center; align-items: center; position: relative; min-height: 48px; }
        .button-row.no-is-escaping { min-height: 112px; }
        .no-button.evading { position: absolute; z-index: 2; transition: left .16s ease, top .16s ease; }
        .no-button { user-select: none; }
        button { border: 0; cursor: pointer; font: inherit; transition: transform .2s, box-shadow .2s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(143,22,77,.14); }
        .primary, .secondary { border-radius: 999px; padding: 14px 27px; font-size: 13px; font-weight: 700; }
        .primary { color: white; background: linear-gradient(110deg, #df4f91, #c83f81); }
        .secondary { color: #9a4f91; background: #e9c7ef; }
        .question { text-align: left; color: #754858; font-weight: 700; font-size: 12px; display: block; margin: 22px 0 8px; }
        input, select { width: 100%; border: 1px solid #eadde0; border-radius: 10px; color: #6f4b58; background: #fff; font: inherit; padding: 13px 14px; outline: none; }
        input:focus, select:focus { border-color: #df4f91; box-shadow: 0 0 0 3px rgba(223,79,145,.12); }
        .full { width: 100%; margin-top: 24px; }
        .vibe-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 24px; }
        .vibe { padding: 14px 7px 12px; color: #8b6471; border: 1px solid transparent; border-radius: 14px; background: #fffafc; font-size: 12px; }
        .vibe span { display: block; font-size: 26px; margin-bottom: 7px; }
        .vibe.selected { border-color: #d790ad; background: #fff3f7; box-shadow: 0 5px 14px rgba(143,22,77,.1); }
        .vibe-action:disabled { cursor: not-allowed; opacity: .45; transform: none; box-shadow: none; }
        .whatsapp-button { background: #25d366; }
        .note { color: #aa7589; font-size: 12px; line-height: 1.6; margin: 18px auto 0; }
        .price { display: flex; align-items: center; justify-content: space-between; text-align: left; margin-top: 24px; padding: 17px 18px; border: 1px solid #f0dce5; border-radius: 15px; background: #fff; }
        .price small { display: block; color: #aa7589; font-size: 11px; margin-bottom: 5px; } .price strong { color: #761a43; font-size: 22px; }
        .success-mark { width: 70px; height: 70px; display: grid; place-items: center; margin: 0 auto 20px; border-radius: 50%; color: white; background: #d95391; font-size: 32px; }
        .footer { margin: 16px 0 0; color: #bd8199; text-align: center; font-size: 11px; }
    </style>
</head>
<body>
<main class="scene">
    <div class="app-shell">
        <p class="brand">a tiny invitation, just for you</p>
        <section class="card active" data-step="invite">
            <div class="photo-frame"><img src="{{ asset('images/abhishek.jpeg') }}" alt="Abhishek Pratap Singh"></div>
            <p class="recipient-name">Abhishek Pratap Singh</p>
            <p class="heart-note">♥ ♥ ♥</p>
            <h1><span class="flower">✿</span> Will you go on<br>a date with me? <span class="flower">✿</span></h1>
            <p class="subtitle">Abhishek, you make ordinary moments feel special. I would love to make one more beautiful memory with you.</p>
            <div class="button-row" id="invite-actions"><button class="primary" data-next="schedule">yes, let's go ♥</button><button class="secondary" id="no-button">no ✿</button></div>
        </section>
        <section class="card" data-step="schedule">
            <h2>So... when are you free?</h2>
            <label class="question" for="date">Pick a day 📅</label>
            <input id="date" type="date">
            <label class="question" for="time">What time? ⏱</label>
            <select id="time"><option value="">Select a time...</option><option>12:00 PM</option><option>12:30 PM</option><option>1:00 PM</option><option>1:30 PM</option><option>2:00 PM</option><option>3:00 PM</option><option>4:00 PM</option><option>4:30 PM</option><option>5:00 PM</option></select>
            <button class="primary full" id="schedule-button">set the date! ♥</button>
            <p class="note" id="schedule-note"></p>
        </section>
        <section class="card" data-step="vibe">
            <h2>What are we feeling? 🍴✨</h2>
            <p class="subtitle">pick your vibe</p>
            <div class="vibe-grid" role="group" aria-label="Choose your food">
                <button class="vibe" data-food="Pizza"><span>🍕</span>Pizza</button>
                <button class="vibe" data-food="Sushi"><span>🍣</span>Sushi</button>
                <button class="vibe" data-food="Burgers"><span>🍔</span>Burgers</button>
                <button class="vibe" data-food="Pasta"><span>🍝</span>Pasta</button>
                <button class="vibe" data-food="Tacos"><span>🌮</span>Tacos</button>
                <button class="vibe" data-food="Ramen"><span>🍜</span>Ramen</button>
            </div>
            <button class="primary full vibe-action" id="vibe-button" disabled>this one! ♥</button>
            <p class="note" id="vibe-note">Choose something delicious first.</p>
        </section>
        <section class="card" data-step="ready">
            <div class="avatar">🚗</div>
            <h2>glad you didn't say no.</h2>
            <p class="subtitle">Be ready by <strong id="chosen-time">6 PM</strong>, I'm coming to get you for <strong id="chosen-food">dinner</strong>.</p>
            <p class="note">P.S. normal people text. I made a website during lunch for you. no big deal.<br>♡ ♡ ♡ ♡ ♡</p>
            <button class="primary full whatsapp-button" id="whatsapp-button">send confirmation on WhatsApp ↗</button>
            <p class="note" id="whatsapp-note">Your date details will be sent securely after confirmation.</p>
        </section>
        <section class="card" data-step="success">
            <div class="success-mark">✓</div><h2>it's a date!</h2>
            <p class="subtitle">Your plans are officially locked in. See you soon, cutie.</p>
            <button class="secondary" onclick="location.reload()">start over</button>
        </section>
        <p class="footer">made with a little bit of courage ✿</p>
    </div>
</main>
<script>
    const cards = document.querySelectorAll('.card');
    const goTo = (step) => cards.forEach(card => card.classList.toggle('active', card.dataset.step === step));
    document.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', () => goTo(button.dataset.next)));
    document.querySelector('#date').min = new Date().toISOString().split('T')[0];
    let chosenDate = '';
    let chosenDateValue = '';
    let chosenTime = '';
    document.querySelector('#schedule-button').addEventListener('click', () => {
        const date = document.querySelector('#date').value;
        const time = document.querySelector('#time').value;
        const note = document.querySelector('#schedule-note');
        if (!date || !time) { note.textContent = 'Pick both a day and a time, then I will handle the rest. ♡'; return; }
        chosenDate = new Date(`${date}T12:00:00`).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' });
        chosenDateValue = date;
        chosenTime = time;
        document.querySelector('#chosen-time').textContent = `${time} on ${chosenDate}`;
        goTo('vibe');
    });
    const noButton = document.querySelector('#no-button');
    const inviteActions = document.querySelector('#invite-actions');
    let escapeCount = 0;
    const escapeNoButton = (event) => {
        if (event.pointerType && event.pointerType !== 'mouse') return;
        inviteActions.classList.add('no-is-escaping');
        noButton.classList.add('evading');
        escapeCount += 1;
        noButton.style.left = escapeCount % 2 === 1 ? '2%' : '68%';
        noButton.style.top = escapeCount % 2 === 1 ? '58px' : '8px';
    };
    noButton.addEventListener('pointerenter', escapeNoButton);
    inviteActions.addEventListener('pointermove', (event) => {
        const bounds = noButton.getBoundingClientRect();
        const distance = Math.hypot(event.clientX - (bounds.left + bounds.width / 2), event.clientY - (bounds.top + bounds.height / 2));
        if (distance < 110) escapeNoButton(event);
    });
    noButton.addEventListener('click', (event) => { event.preventDefault(); escapeNoButton(event); });
    noButton.addEventListener('keydown', (event) => { event.preventDefault(); escapeNoButton(event); });
    let chosenFood = '';
    document.querySelectorAll('.vibe').forEach(button => button.addEventListener('click', () => {
        chosenFood = button.dataset.food;
        document.querySelectorAll('.vibe').forEach(option => option.classList.remove('selected'));
        button.classList.add('selected');
        document.querySelector('#vibe-button').disabled = false;
        document.querySelector('#vibe-note').textContent = `${chosenFood} it is. Excellent choice. ♡`;
    }));
    document.querySelector('#vibe-button').addEventListener('click', () => {
        document.querySelector('#chosen-food').textContent = chosenFood;
        goTo('ready');
    });
    document.querySelector('#whatsapp-button').addEventListener('click', async () => {
        const note = document.querySelector('#whatsapp-note');
        const button = document.querySelector('#whatsapp-button');
        button.disabled = true;
        button.textContent = 'saving your date...';
        try {
            const response = await fetch('{{ route('date-confirmations.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ date: chosenDateValue, time: chosenTime, option: chosenFood }),
            });
            const result = await response.json();
            if (!response.ok && response.status !== 202) throw new Error(result.message || 'The confirmation could not be saved.');
            note.textContent = result.message;
            goTo('success');
        } catch (error) {
            note.textContent = error.message;
            button.disabled = false;
            button.textContent = 'send confirmation on WhatsApp ↗';
        }
    });
</script>
</body>
</html>
