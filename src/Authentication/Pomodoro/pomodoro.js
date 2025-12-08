let timeLeft = 25 * 60;
let timer = null;
let running = false;
let currentMode = "work";
let musicPlaying = false;

const display = document.getElementById("timeDisplay");
const sessionLabel = document.getElementById("sessionLabel");
const bgMusicLocal = document.getElementById("bgMusic"); // fallback local audio element
const alarm = document.getElementById("alarmSound");
const musicSelect = document.getElementById("bgMusicSelect");

function getBg() {
    return (window.getGlobalAudio && window.getGlobalAudio()) || bgMusicLocal;
}

// Restore saved state: endTime, paused, remaining, selected music, and playback
function initState() {
    // restore selected music dropdown
    const sel = localStorage.getItem('selectedMusic');
    if (sel) musicSelect.value = sel;

    // If an endTime exists and is in the future, resume timer
    if (localStorage.getItem("endTime")) {
        let savedEnd = parseInt(localStorage.getItem("endTime"), 10);
        let now = Date.now();
        if (savedEnd > now) {
            running = true;
            timeLeft = Math.floor((savedEnd - now) / 1000);

            // Restore music playback if it was playing
            if (localStorage.getItem("musicPlaying") === "true") {
                musicPlaying = true;
                applySelectedMusic();
                const player = getBg();
                if (player) {
                    player.play().catch(() => console.log('Autoplay blocked'));
                }
            }

            resumeTimer(savedEnd);
        }
        updateDisplay();
        return;
    }

    // If paused state exists, restore remaining time but do not auto-start
    if (localStorage.getItem('paused') === 'true' && localStorage.getItem('remaining')) {
        timeLeft = parseInt(localStorage.getItem('remaining'), 10) || timeLeft;
        updateDisplay();
        // ensure music reflects selected but not auto-play
        applySelectedMusic();
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
    const player = getBg();
    if (player) {
        // if player is the global one created by audioPlayer.js, update src
        if (player.id === 'globalBgMusic') {
            if (!player.src || player.src.indexOf(file) === -1) player.src = '../../../sounds/' + file;
            // restore last saved time
            const t = parseFloat(localStorage.getItem('musicCurrentTime')) || 0;
            try { player.currentTime = t; } catch (e) {}
        } else {
            player.src = '../../../sounds/' + file;
        }
    }
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
    if (event && event.target) event.target.classList.add("active");

    const player = getBg();
    if (player) {
        player.pause();
        player.currentTime = 0;
        localStorage.setItem('musicCurrentTime', 0);
    }

    updateDisplay();
    updateStartButton();
}

function updateStartButton() {
    const btn = document.getElementById('startBtn');
    if (running) {
        btn.innerHTML = '<i class="bi bi-pause-fill"></i> Pause';
    } else {
        btn.innerHTML = '<i class="bi bi-play-fill"></i> Start';
    }
}

document.getElementById("startBtn").onclick = () => {
    // Toggle start/pause
    if (running) {
        // Pause
        running = false;
        clearInterval(timer);
        localStorage.setItem('paused', 'true');
        localStorage.setItem('remaining', timeLeft);
        musicPlaying = false;
        localStorage.setItem('musicPlaying', 'false');

        const player = getBg();
        if (player) player.pause();
    } else {
        // Start or resume
        running = true;
        localStorage.removeItem('paused');
        localStorage.removeItem('remaining');

        const endTime = Date.now() + timeLeft * 1000;
        localStorage.setItem('endTime', endTime);
        musicPlaying = true;
        localStorage.setItem('musicPlaying', 'true');

            applySelectedMusic();
            // Try to open persistent audio host popup. If it fails, fallback to in-page player.
            if (window.openAudioHost) {
                window.openAudioHost({ file: musicSelect.value, time: parseFloat(localStorage.getItem('musicCurrentTime')) || 0, play: true });
            } else {
                const player = getBg();
                if (player) {
                    const pos = parseFloat(localStorage.getItem('musicCurrentTime')) || 0;
                    try { player.currentTime = pos; } catch (e) {}
                    player.play().catch(() => console.log('Autoplay blocked'));
                }
            }

        resumeTimer(endTime);
    }
    updateStartButton();
};

// Save music selection change
musicSelect.addEventListener('change', function () {
    applySelectedMusic();
    // if currently playing, ensure it plays immediately
    if (musicPlaying) {
        const player = getBg();
        if (player) player.play().catch(() => {});
    }
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

    const player = getBg();
    if (player) {
        player.pause();
        try { player.currentTime = 0; } catch (e) {}
    }

    if (currentMode === "work") timeLeft = 25 * 60;
    if (currentMode === "short") timeLeft = 5 * 60;
    if (currentMode === "long") timeLeft = 15 * 60;

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

            const player = getBg();
            if (player) {
                player.pause();
                try { player.currentTime = 0; } catch (e) {}
            }

            alarm.play();
            updateStartButton();
        }
    }, 250);
}

// Periodically save music time so playback can continue across navigations
setInterval(() => {
    const player = getBg();
    try {
        if (player && !player.paused) {
            localStorage.setItem('musicCurrentTime', player.currentTime);
        }
    } catch (e) { }
}, 1000);

// Initialize
initState();
updateStartButton();

