// Global audio player used across pages.
// Creates a singleton audio element `#globalBgMusic` and keeps its playback position
// synchronized to localStorage so music continues across full page navigations.
(function () {
    try {
        const selected = localStorage.getItem('selectedMusic') || null;
        const playing = localStorage.getItem('musicPlaying') === 'true';
        const savedTime = parseFloat(localStorage.getItem('musicCurrentTime')) || 0;

        function createPlayer(file) {
            const audio = document.createElement('audio');
            audio.id = 'globalBgMusic';
            audio.loop = true;
            audio.volume = 0.45;
            if (file) audio.src = '../../../sounds/' + file;
            audio.currentTime = savedTime;
            document.body.appendChild(audio);

            // persist playback position
            setInterval(() => {
                try {
                    if (!audio.paused) {
                        localStorage.setItem('musicCurrentTime', audio.currentTime);
                    }
                } catch (e) { /* ignore localStorage errors */ }
            }, 1000);

            return audio;
        }

        // Ensure singleton
        let player = document.getElementById('globalBgMusic');
        if (!player) {
            if (selected || playing) {
                player = createPlayer(selected);
            }
        } else {
            // update src & time if needed
            if (selected && player.src.indexOf(selected) === -1) player.src = '../../../sounds/' + selected;
            player.currentTime = savedTime;
        }

        if (player && playing) {
            player.play().catch(() => {
                // autoplay may require user interaction
                // we'll still keep the player available for user-triggered play
                console.log('Autoplay blocked — user interaction required to resume audio.');
            });
        }

        // Expose helper so other scripts can use the same audio element
        window.getGlobalAudio = function () {
            return document.getElementById('globalBgMusic');
        };
    
        // Open or reuse a persistent audio host window to avoid playback interruption during navigation.
        // Stores a reference on `window._audioHostWindow` and exposes helpers to control it.
        window.openAudioHost = function ({file, time = 0, play = false} = {}) {
            try {
                const hostUrl = new URL('../Shared/audio_host.php', location.href).toString();
                // Use a named window so repeated calls reuse it
                const w = window.open(hostUrl, 'debugmyday-audio', 'width=340,height=80');
                if (!w) return null;

                // Post a message after a short delay to allow the host to initialize
                const msg = { type: 'control', file: file || localStorage.getItem('selectedMusic') || null, time: time || parseFloat(localStorage.getItem('musicCurrentTime')) || 0, play: !!play };
                setTimeout(() => {
                    try { w.postMessage(msg, '*'); } catch (e) { }
                }, 300);
                // Save reference so other pages/scripts can message the host
                try { window._audioHostWindow = w; } catch (e) {}
                return w;
            } catch (err) {
                console.error('openAudioHost error', err);
                return null;
            }
        };

        // Send a control message to the host if available
        window.sendToAudioHost = function (msg) {
            try {
                if (window._audioHostWindow && !window._audioHostWindow.closed) {
                    window._audioHostWindow.postMessage(msg, '*');
                    return true;
                }
                // try to locate the named window
                const maybe = window.open('', 'debugmyday-audio');
                if (maybe && !maybe.closed) {
                    window._audioHostWindow = maybe;
                    maybe.postMessage(msg, '*');
                    return true;
                }
            } catch (e) { }
            return false;
        };

        // Pause global audio (host or in-page)
        window.pauseGlobalAudio = function () {
            try {
                // prefer host
                if (window.sendToAudioHost && window.sendToAudioHost({ type: 'control', play: false })) return;
            } catch (e) {}
            try {
                const p = window.getGlobalAudio && window.getGlobalAudio();
                if (p) p.pause();
            } catch (e) {}
        };

        // Play global audio (host or in-page). file/time optional
        window.playGlobalAudio = function (file, time) {
            try {
                if (window.sendToAudioHost && window.sendToAudioHost({ type: 'control', file: file || localStorage.getItem('selectedMusic') || null, time: typeof time === 'number' ? time : parseFloat(localStorage.getItem('musicCurrentTime')) || 0, play: true })) return;
            } catch (e) {}
            try {
                const p = window.getGlobalAudio && window.getGlobalAudio();
                if (p) {
                    if (file) p.src = '../../../sounds/' + file;
                    try { p.currentTime = time || parseFloat(localStorage.getItem('musicCurrentTime')) || 0; } catch (e) {}
                    p.play().catch(()=>{});
                }
            } catch (e) {}
        };
    } catch (err) {
        console.error('audioPlayer init error', err);
    }
})();

// If there is an expected playing state but no host/player available,
// show a small control so the user can open the persistent host (user gesture required).
(function(){
    try {
        const shouldBePlaying = localStorage.getItem('musicPlaying') === 'true';
        const hasPlayer = !!document.getElementById('globalBgMusic');

        if (shouldBePlaying && !hasPlayer) {
            // create floating button
            const btn = document.createElement('button');
            btn.id = 'openAudioHostBtn';
            btn.innerText = 'Audio';
            btn.title = 'Open audio host to continue playback';
            Object.assign(btn.style, {
                position: 'fixed',
                right: '16px',
                bottom: '16px',
                zIndex: 99999,
                background: '#8b32ff',
                color: 'white',
                border: 'none',
                padding: '10px 14px',
                borderRadius: '10px',
                cursor: 'pointer',
                boxShadow: '0 6px 18px rgba(0,0,0,0.12)'
            });

            btn.addEventListener('click', function () {
                if (window.openAudioHost) {
                    window.openAudioHost({ file: localStorage.getItem('selectedMusic') || null, time: parseFloat(localStorage.getItem('musicCurrentTime')) || 0, play: true });
                } else {
                    // fallback: try opening directly
                    window.open('../Shared/audio_host.php', 'debugmyday-audio', 'width=340,height=80');
                }
                // remove button after opening
                setTimeout(()=>{ try{ btn.remove(); }catch(e){} }, 800);
            });

            document.addEventListener('DOMContentLoaded', ()=>{
                document.body.appendChild(btn);
            });
        }
    } catch (e) { /* ignore */ }
})();
