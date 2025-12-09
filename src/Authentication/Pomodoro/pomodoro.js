// Only run this JS if Pomodoro elements exist
const display = document.getElementById("timeDisplay");
if (!display) {
    // Clear any leftover Pomodoro state from other pages
    localStorage.removeItem("endTime");
    localStorage.removeItem("paused");
    localStorage.removeItem("remaining");
    localStorage.removeItem("musicPlaying");
    localStorage.removeItem("musicCurrentTime");
} else {

    let timeLeft = 25 * 60;
    let timer = null;
    let running = false;
    let currentMode = "work";
    let musicPlaying = false;

    const sessionLabel = document.getElementById("sessionLabel");
    const alarm = document.getElementById("alarmSound");
    const musicSelect = document.getElementById("bgMusicSelect");
    let bgPlayer = null;

    // Initialize Pomodoro only if on this page
    function initState() {
        const sel = localStorage.getItem('selectedMusic');
        if (sel) musicSelect.value = sel;

        if (localStorage.getItem("endTime")) {
            let savedEnd = parseInt(localStorage.getItem("endTime"), 10);
            let now = Date.now();
            if (savedEnd > now) {
                running = true;
                timeLeft = Math.floor((savedEnd - now) / 1000);

                if (localStorage.getItem("musicPlaying") === "true") {
                    musicPlaying = true;
                    applySelectedMusic();
                    bgPlayer.play().catch(() => { });
                }

                resumeTimer(savedEnd);
            }
            updateDisplay();
        }
    }

    function updateDisplay() {
        let m = Math.floor(timeLeft / 60);
        let s = timeLeft % 60;
        display.innerText = `${m}:${s.toString().padStart(2, "0")}`;
    }

    function applySelectedMusic() {
        const file = musicSelect.value;
        localStorage.setItem('selectedMusic', file);

        if (!bgPlayer) {
            bgPlayer = new Audio('../../../sounds/' + file);
            bgPlayer.loop = true;
        } else if (bgPlayer.src.indexOf(file) === -1) {
            bgPlayer.src = '../../../sounds/' + file;
        }

        const t = parseFloat(localStorage.getItem('musicCurrentTime')) || 0;
        try { bgPlayer.currentTime = t; } catch { }
    }

    function setMode(mode, mins) {
        currentMode = mode;
        timeLeft = mins * 60;

        clearInterval(timer);
        running = false;
        localStorage.removeItem("endTime");

        sessionLabel.textContent =
            mode === "work" ? "Focus Time" :
                mode === "short" ? "Short Break" :
                    "Long Break";

        document.querySelectorAll(".mode-btn").forEach(btn => btn.classList.remove("active"));
        event.target.classList.add("active");

        if (bgPlayer) {
            bgPlayer.pause();
            bgPlayer.currentTime = 0;
            localStorage.setItem('musicCurrentTime', 0);
        }

        updateDisplay();
        updateStartButton();
    }

    function updateStartButton() {
        const btn = document.getElementById('startBtn');
        btn.innerHTML = running
            ? '<i class="bi bi-pause-fill"></i> Pause'
            : '<i class="bi bi-play-fill"></i> Start';
    }

    document.getElementById("startBtn").onclick = () => {
        if (running) {
            running = false;
            clearInterval(timer);
            localStorage.setItem('paused', 'true');
            localStorage.setItem('remaining', timeLeft);
            musicPlaying = false;
            localStorage.setItem('musicPlaying', 'false');
            localStorage.removeItem('endTime');
            if (bgPlayer) bgPlayer.pause();
        } else {
            running = true;
            localStorage.removeItem('paused');
            localStorage.removeItem('remaining');

            const endTime = Date.now() + timeLeft * 1000;
            localStorage.setItem('endTime', endTime);

            musicPlaying = true;
            localStorage.setItem('musicPlaying', 'true');
            applySelectedMusic();

            if (bgPlayer) {
                const pos = parseFloat(localStorage.getItem('musicCurrentTime')) || 0;
                bgPlayer.currentTime = pos;
                bgPlayer.play().catch(() => { });
            }

            resumeTimer(endTime);
        }
        updateStartButton();
    };

    musicSelect.addEventListener('change', function () {
        localStorage.setItem('selectedMusic', musicSelect.value);
        applySelectedMusic();
        if (musicPlaying && bgPlayer) bgPlayer.play();
    });

    document.getElementById("resetBtn").onclick = () => {
        running = false;
        musicPlaying = false;
        clearInterval(timer);
        localStorage.removeItem("endTime");
        localStorage.removeItem("musicPlaying");
        localStorage.removeItem('paused');
        localStorage.removeItem('remaining');
        localStorage.removeItem('musicCurrentTime');

        if (bgPlayer) {
            bgPlayer.pause();
            bgPlayer.currentTime = 0;
        }

        timeLeft = currentMode === "work" ? 25 * 60 :
            currentMode === "short" ? 5 * 60 : 15 * 60;

        updateDisplay();
        updateStartButton();
    };

    function resumeTimer(endTime) {
        clearInterval(timer);
        timer = setInterval(() => {
            let now = Date.now();
            timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));
            updateDisplay();

            if (timeLeft <= 0) {
                clearInterval(timer);
                running = false;
                localStorage.removeItem("endTime");

                if (bgPlayer) {
                    bgPlayer.pause();
                    bgPlayer.currentTime = 0;
                }

                if (alarm) alarm.play(); // Alarm only on Pomodoro page
                updateStartButton();
            }
        }, 250);
    }

    setInterval(() => {
        if (bgPlayer && !bgPlayer.paused) {
            localStorage.setItem('musicCurrentTime', bgPlayer.currentTime);
        }
    }, 1000);

    // Clear Pomodoro state on leaving page
    window.addEventListener('beforeunload', () => {
        if (!document.getElementById("timeDisplay")) return;
        if (bgPlayer) bgPlayer.pause();
        localStorage.removeItem("endTime");
        localStorage.removeItem("paused");
        localStorage.removeItem("remaining");
        localStorage.removeItem("musicPlaying");
        localStorage.removeItem("musicCurrentTime");
    });

    initState();
    updateStartButton();
}
