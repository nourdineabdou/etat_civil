<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recherche NNI — Mon Projet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
     <style>
        :root{--glass: rgba(255,255,255,0.06); --accent:#7c3aed}
        body{font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial;min-height:100vh;background:linear-gradient(135deg,#07102a 0%,#07263a 60%,#0b1630 100%);color:#e6eef8;padding:32px}
        .panel{max-width:1100px;margin:0 auto}
        .glass-card{background:linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));border-radius:16px;padding:22px;box-shadow:0 12px 40px rgba(2,6,23,0.6);border:1px solid rgba(255,255,255,0.04)}
        h1,h2,h3{color:#fff}
        .muted{color:#a8b3c7}
        .photo-box{width:220px;height:300px;border-radius:12px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center}
        img.photo{width:100%;height:100%;object-fit:cover}
        .chip{background:rgba(255,255,255,0.06);padding:6px 10px;border-radius:999px;color:#fff;border:1px solid rgba(255,255,255,0.03);font-weight:600}
        .search-input .form-control{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);color:#fff}
        .search-input .form-control:focus{box-shadow:none;border-color:var(--accent);}
        .btn-accent{background:linear-gradient(90deg,var(--accent),#4f46e5);border:0;color:#fff}
        .stat-card{background:linear-gradient(90deg,rgba(124,58,237,0.08),rgba(79,70,229,0.06));border-radius:10px;padding:12px;color:#fff}
        .small-muted{color:#c5d2e6}
        @media(max-width:900px){.photo-box{width:160px;height:220px}}
    </style>
    <style>
        /* Safety: hide accidental full-screen overlays/backdrops that may block the UI */
        .modal-backdrop, .overlay, .backdrop, .bs-backdrop, .app-overlay { display: none !important; }
        [data-overlay], [data-backdrop] { display: none !important; }
        /* Remove any element with inline full-screen fixed style (best-effort) */
        [style*="position:fixed"][style*="top:0"][style*="left:0"][style*="width:100%"] { display: none !important; }
        /* Ensure main panel is on top */
        .panel { position: relative; z-index: 2147483647; }
    </style>
     <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body>

      <main class="panel">
        <div class="glass-card">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="chip"><i class="bi bi-person-badge-fill"></i></div>
                            <div>
                                <h2 class="mb-0">Recherche NNI</h2>
                                <div class="small-muted">Entrez un NNI pour afficher la fiche et les statistiques.</div>
                            </div>
                        </div>
                        <div class="ms-2">
                            <button id="logoutBtn" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Déconnexion</button>
                        </div>
                    </div>

                    <div class="mt-4 search-input">
                        <div class="input-group mb-2">
                            <input id="nniInput" type="text" class="form-control form-control-lg" placeholder="Ex: 12345678" aria-label="NNI">
                            <button id="searchBtn" class="btn btn-accent btn-lg"> <i class="bi bi-search me-2"></i>Rechercher</button>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="chip">Mock Data</span>
                            <span class="chip">Sécurisé</span>
                        </div>
                        <div class="mt-3 small-muted">Les résultats affichés sont des données d'exemple. Intégration réelle disponible sur demande.</div>
                    </div>

                    <div class="mt-4">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small-muted">Total recherches</div>
                                    <div id="statsTotal" class="h4 mb-0">0</div>
                                </div>
                                <div>
                                    <div class="small-muted">NNI uniques</div>
                                    <div id="statsUnique" class="h4 mb-0">0</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <canvas id="placesChart" height="130"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="photo-box shadow-sm">
                                    <img id="photoImg" class="photo" src="" alt="Photo" />
                                </div>
                                <div class="flex-grow-1">
                                    <h3 id="fullName">Nom Complet</h3>
                                    <div class="small-muted">NNI: <span id="nniValue">—</span></div>
                                    <div class="row g-2 mt-3">
                                        <div class="col-6">
                                            <div class="small-muted">Nom (FR)</div>
                                            <div id="nom_fr" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small-muted">Nom (AR)</div>
                                            <div id="nom_ar" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="small-muted">Nom de famille (FR)</div>
                                            <div id="nom_famille_fr" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="small-muted">Nom de famille (AR)</div>
                                            <div id="nom_famille_ar" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="small-muted">Prénom (FR)</div>
                                            <div id="prenom_fr" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="small-muted">Prénom (AR)</div>
                                            <div id="prenom_ar" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="small-muted">Date de naissance</div>
                                            <div id="date_naissance" class="fw-semibold">—</div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="small-muted">Lieu de naissance (FR)</div>
                                            <div id="lieu_naissance_fr" class="fw-semibold">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="glass-card p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Répartition par année</h5>
                                    <div class="small-muted">Dernières années</div>
                                </div>
                                <canvas id="yearsChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
     <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const nniInput = document.getElementById('nniInput');
        const searchBtn = document.getElementById('searchBtn');

        const photoImg = document.getElementById('photoImg');
        const nniValue = document.getElementById('nniValue');
        const fullName = document.getElementById('fullName');

        const fields = ['nom_fr','nom_ar','nom_famille_fr','nom_famille_ar','prenom_fr','prenom_ar','date_naissance','lieu_naissance_fr'];

        let placesChart = null;
        let yearsChart = null;

        function initCharts(){
            const placesCtx = document.getElementById('placesChart').getContext('2d');
            placesChart = new Chart(placesCtx, {
                type: 'doughnut',
                data: { labels: [], datasets: [{ data: [], backgroundColor: ['#7c3aed','#60a5fa','#34d399','#f97316','#f43f5e'] }] },
                options: { plugins:{legend:{labels:{color:'#fff'}}} }
            });

            const yearsCtx = document.getElementById('yearsChart').getContext('2d');
            yearsChart = new Chart(yearsCtx, {
                type: 'bar',
                data: { labels: [], datasets: [{ label: 'Par année', data: [], backgroundColor: '#7c3aed' }] },
                options: { scales:{x:{ticks:{color:'#cbd5e1'}},y:{ticks:{color:'#cbd5e1'}}}, plugins:{legend:{display:false}} }
            });
        }

        async function fetchStats(){
            try{
                const res = await fetch('/nni/stats', { headers: { 'Accept': 'application/json' } });
                if(!res.ok) return null;
                const payload = await res.json();
                return payload.stats ?? null;
            }catch(e){ console.error(e); return null; }
        }

        function updateStatsUI(stats){
            if(!stats) return;
            document.getElementById('statsTotal').textContent = stats.total_lookups ?? 0;
            document.getElementById('statsUnique').textContent = stats.unique_nnis ?? 0;

            // places chart
            const places = stats.top_places || {};
            const placeLabels = Object.keys(places);
            const placeValues = Object.values(places);
            placesChart.data.labels = placeLabels;
            placesChart.data.datasets[0].data = placeValues;
            placesChart.update();

            // years chart
            const years = stats.by_year || {};
            const yearLabels = Object.keys(years);
            const yearValues = Object.values(years);
            yearsChart.data.labels = yearLabels;
            yearsChart.data.datasets[0].data = yearValues;
            yearsChart.update();
        }

        function showResult(data){
            photoImg.src = data.photo || '';
            nniValue.textContent = data.nni || '—';
            fullName.textContent = [data.prenom_fr, data.nom_famille_fr].filter(Boolean).join(' ') || '—';
            fields.forEach(f => { const el = document.getElementById(f); if(el) el.textContent = data[f] || '—'; });
        }

        searchBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const nni = nniInput.value.trim();
            if(!nni) return;
            searchBtn.disabled = true; searchBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin me-2"></i>Recherche...';
            try{
                const res = await fetch('/nni/lookup', { method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json' }, body: JSON.stringify({ nni }) });
                if(!res.ok) throw new Error('Erreur serveur');
                const payload = await res.json();
                showResult(payload);
                if(payload.stats) updateStatsUI(payload.stats);
            }catch(err){ console.error(err); alert('Erreur lors de la recherche'); }
            finally{ searchBtn.disabled = false; searchBtn.innerHTML = '<i class="bi bi-search me-2"></i>Rechercher'; }
        });

        // init
        document.addEventListener('DOMContentLoaded', async () => {
            initCharts();
            const stats = await fetchStats();
            updateStatsUI(stats);

            // attach logout handler
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async () => {
                    try{
                        await fetch('/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
                    }catch(e){ /* ignore */ }
                    // redirect to login page
                    window.location.href = '/login';
                });
            }

            // Remove any accidental overlay/backdrop elements that may hide the UI
            try{
                document.querySelectorAll('.modal-backdrop, .overlay, .backdrop, .bs-backdrop, .app-overlay, [data-overlay], [data-backdrop]').forEach(el => el.remove());
                // also remove obvious fixed full-screen elements
                document.querySelectorAll('*').forEach(el => {
                    try{
                        const s = getComputedStyle(el);
                        if (s.position === 'fixed' && s.top === '0px' && s.left === '0px' && (s.width === '100%' || s.width === '100vw')) {
                            el.remove();
                        }
                    }catch(e){}
                });
            }catch(e){/* ignore cleanup errors */}
        });
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!-- Debug helper: floating button to list and remove overlays -->
<style>
    #debugOverlayBtn{position:fixed;right:18px;bottom:18px;z-index:2147483647;background:#111827;color:#fff;border:0;border-radius:10px;padding:10px 12px;box-shadow:0 6px 20px rgba(2,6,23,0.6);cursor:pointer}
    #debugOverlayModal{position:fixed;right:18px;bottom:70px;z-index:2147483647;min-width:320px;max-width:420px;background:#fff;color:#000;border-radius:10px;padding:12px;box-shadow:0 12px 40px rgba(2,6,23,0.4);display:none}
    #debugOverlayModal h6{margin:0 0 6px 0}
    #debugOverlayList{max-height:300px;overflow:auto;margin:6px 0;padding:0;list-style:none}
    #debugOverlayList li{padding:6px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;gap:8px}
    #debugOverlayList button{flex:0 0 auto}
</style>
<button id="debugOverlayBtn">Debug overlays</button>
<div id="debugOverlayModal" aria-hidden="true">
    <h6>Overlays détectés</h6>
    <div class="small-muted">Éléments fixes/full-screen / z-index élevés</div>
    <ul id="debugOverlayList"></ul>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">
        <button id="debugRefreshBtn" class="btn btn-sm btn-secondary">Rafraîchir</button>
        <button id="debugCloseBtn" class="btn btn-sm btn-outline-secondary">Fermer</button>
    </div>
</div>
<script>
    function findOverlayCandidates(){
        const candidates = [];
        document.querySelectorAll('*').forEach(el => {
            try{
                const s = getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                const big = (rect.width >= window.innerWidth - 2 && rect.height >= window.innerHeight - 2);
                const fixedFull = s.position === 'fixed' && (s.top === '0px' || Math.abs(rect.top) < 2) && (s.left === '0px' || Math.abs(rect.left) < 2) && big;
                const highZ = Number(s.zIndex) && Number(s.zIndex) > 1000;
                const semiOpaque = (s.backgroundColor && (s.backgroundColor.startsWith('rgba') || s.opacity < 0.95));
                if (fixedFull || highZ || semiOpaque) {
                    candidates.push({el, rect, z: s.zIndex || 'auto', pos: s.position, bg: s.backgroundColor, opacity: s.opacity});
                }
            }catch(e){}
        });
        return candidates;
    }

    function selectorFor(el){
        if(el.id) return `#${el.id}`;
        if(el.className && typeof el.className === 'string') return `${el.tagName.toLowerCase()}.${el.className.trim().split(/\s+/).join('.')}`;
        return el.tagName.toLowerCase();
    }

    function renderOverlayList(){
        const list = document.getElementById('debugOverlayList');
        list.innerHTML = '';
        const items = findOverlayCandidates();
        if(items.length===0){ list.innerHTML = '<li>Aucun overlay détecté</li>'; return; }
        items.forEach((it, idx) => {
            const li = document.createElement('li');
            const info = document.createElement('div');
            info.innerHTML = `<strong>${selectorFor(it.el)}</strong><div class="small">z-index: ${it.z} pos:${it.pos} bg:${it.bg} opacity:${it.opacity}</div>`;
            const btns = document.createElement('div');
            const remove = document.createElement('button'); remove.className='btn btn-sm btn-danger'; remove.textContent='Supprimer';
            remove.addEventListener('click', ()=>{ try{ it.el.remove(); renderOverlayList(); }catch(e){ alert('Impossible de supprimer'); } });
            const highlight = document.createElement('button'); highlight.className='btn btn-sm btn-outline-secondary ms-2'; highlight.textContent='Surbrillance';
            highlight.addEventListener('click', ()=>{ it.el.style.outline='4px solid red'; it.el.scrollIntoView({behavior:'smooth',block:'center'}); });
            btns.appendChild(remove); btns.appendChild(highlight);
            li.appendChild(info); li.appendChild(btns); list.appendChild(li);
        });
    }

    document.getElementById('debugOverlayBtn').addEventListener('click', ()=>{
        const modal = document.getElementById('debugOverlayModal');
        modal.style.display = modal.style.display==='block' ? 'none' : 'block';
        if(modal.style.display==='block') renderOverlayList();
    });
    document.getElementById('debugRefreshBtn').addEventListener('click', renderOverlayList);
    document.getElementById('debugCloseBtn').addEventListener('click', ()=>{ document.getElementById('debugOverlayModal').style.display='none'; });

    // Keyboard shortcuts: Ctrl+Alt+D -> toggle debug panel, Ctrl+Alt+R -> remove first detected overlay
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.altKey && e.code === 'KeyD') {
            e.preventDefault();
            const btn = document.getElementById('debugOverlayBtn');
            if (btn) btn.click();
        }
        if (e.ctrlKey && e.altKey && e.code === 'KeyR') {
            e.preventDefault();
            // remove first candidate
            const items = findOverlayCandidates();
            if (items.length > 0) {
                try{ items[0].el.remove(); alert('Premier overlay supprimé'); }catch(e){ alert('Impossible de supprimer'); }
            } else {
                alert('Aucun overlay détecté');
            }
        }
    });
</script>
</body>
</html>
</body>
</html>
