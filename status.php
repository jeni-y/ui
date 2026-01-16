<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Deployment Console</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(to bottom,#0d1117 0%,#161b22 100%); color:#c9d1d9; font-family: monospace;}
h1 { color:#58a6ff;}
#console {background-color:#0d1117;border-radius:.5rem;padding:1rem;height:600px;overflow-y:auto;white-space:pre-line;border:1px solid #30363d;}
.step {display:flex;align-items:center;margin-bottom:.5rem;}
.circle {width:1rem;height:1rem;border-radius:50%;display:inline-block;margin-right:.5rem;}
.pending {background:gray;}
.in-progress {background:blue;animation:pulse 1s infinite;}
.success {background:green;}
.failed {background:red;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.clouds {position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:-1;background:url('https://i.ibb.co/qMj0v3k/clouds.png') repeat-x;background-size:cover;opacity:.1;animation:cloudMove 100s linear infinite;}
@keyframes cloudMove{0%{background-position-x:0;}100%{background-position-x:1000px;}}
</style>
</head>
<body class="p-6 flex flex-col items-center">
<div class="clouds"></div>
<h1 class="text-2xl font-bold mb-2">Deployment Console</h1>
<p id="ingress" class="mb-4 text-blue-400 underline">Ingress URL: Fetching...</p>
<div id="console"></div>

<script>
const consoleEl = document.getElementById('console');
const ingressEl = document.getElementById('ingress');
const clientName = new URLSearchParams(window.location.search).get('client');

function appendLog(text,type='pending'){
  const div = document.createElement('div');
  div.className='step';
  const circle=document.createElement('span');
  circle.className='circle '+type;
  div.appendChild(circle);
  const span=document.createElement('span');
  span.textContent=text;
  div.appendChild(span);
  consoleEl.appendChild(div);
  consoleEl.scrollTop=consoleEl.scrollHeight;
}

// Connect WebSocket
const ws = new WebSocket('ws://localhost:8080?client='+encodeURIComponent(clientName));

ws.onmessage = function(e){
    const data = JSON.parse(e.data);
    consoleEl.innerHTML='';
    appendLog('Deployment Status: '+data.status, data.status);
    if(data.ingress_url){
        ingressEl.innerHTML="Ingress URL: <a href='"+data.ingress_url+"' target='_blank'>"+data.ingress_url+"</a>";
    }
};
</script>
</body>
</html>