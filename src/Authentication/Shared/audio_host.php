<?php
// Minimal host page that holds an audio element in its own window.
// It listens for postMessage control commands from the main app.
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>DebugMyDay Audio Host</title>
  <style>body{margin:0;background:#f6f2fc;font-family:sans-serif} .info{padding:8px;font-size:13px;color:#333}</style>
</head>
<body>
  <div class="info">DebugMyDay audio</div>
  <audio id="hostAudio" loop></audio>
  <script>
    (function(){
      const audio = document.getElementById('hostAudio');
      // restore last known selection
      const sel = localStorage.getItem('selectedMusic');
      const play = localStorage.getItem('musicPlaying') === 'true';
      const t = parseFloat(localStorage.getItem('musicCurrentTime')) || 0;
      if (sel) audio.src = '../../../sounds/' + sel;
      try { audio.currentTime = t; } catch(e){}
      audio.volume = 0.45;

      // persist currentTime periodically
      setInterval(()=>{
        try{ if (!audio.paused) localStorage.setItem('musicCurrentTime', audio.currentTime); }catch(e){}
      }, 800);

      if (play) {
        audio.play().catch(()=>{});
      }

      window.addEventListener('message', (ev)=>{
        try{
          const d = ev.data || {};
          if (d && d.type === 'control') {
            if (d.file) audio.src = '../../../sounds/' + d.file;
            if (typeof d.time === 'number') {
              try { audio.currentTime = d.time; } catch(e){}
            }
            if (d.play) {
              audio.play().catch(()=>{});
              localStorage.setItem('musicPlaying','true');
            } else {
              audio.pause();
              localStorage.setItem('musicPlaying','false');
            }
            // persist chosen file
            if (d.file) localStorage.setItem('selectedMusic', d.file);
          }
        }catch(e){ }
      }, false);

      // notify opener that we're ready
      try{ if (window.opener) window.opener.postMessage({type:'host-ready'}, '*'); }catch(e){}
    })();
  </script>
</body>
</html>
