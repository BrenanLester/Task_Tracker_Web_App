let timeLeft = 25 * 60;
let timer = null;
let running = false;
let currentMode = "work";

const display = document.getElementById("timeDisplay");
const sessionLabel = document.getElementById("sessionLabel");
const bgMusic = document.getElementById("bgMusic");
const alarm = document.getElementById("alarmSound");

function updateDisplay() {
    let m = Math.floor(timeLeft / 60);
    let s = timeLeft % 60;
    display.innerText = `${m}:${s.toString().padStart(2, "0")}`;
}

function setMode(mode, mins) {
    currentMode = mode;
    timeLeft = mins * 60;
    clearInterval(timer);
    running = false;

    // Change label
    sessionLabel.textContent =
        mode === "work" ? "Focus Time" :
        mode === "short" ? "Short Break" :
        "Long Break";

    // Update active button styling
    document.querySelectorAll(".mode-btn").forEach(btn => btn.classList.remove("active"));
    event.target.classList.add("active");

    bgMusic.pause();
    updateDisplay();
}

document.getElementById("startBtn").onclick = () => {
    if (running) return;
    running = true;

    const endTime = Date.now() + timeLeft * 1000; // set target end time

    bgMusic.volume = 0.45;
    bgMusic.play();

    timer = setInterval(() => {
        const now = Date.now();
        timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));

        updateDisplay();

        if (timeLeft <= 0) {
            clearInterval(timer);
            running = false;

            bgMusic.pause();
            alarm.play();
        }
    }, 250); // faster check for better accuracy
};

document.getElementById("resetBtn").onclick = () => {
    running = false;
    clearInterval(timer);

    bgMusic.pause();

    if (currentMode === "work") timeLeft = 25 * 60;
    if (currentMode === "short") timeLeft = 5 * 60;
    if (currentMode === "long") timeLeft = 15 * 60;

    updateDisplay();
};
