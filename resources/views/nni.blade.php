<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recherche NNI — Mon Projet</title>
    <link href="{{ asset('vendor/css2.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap.min.css') }}" rel="stylesheet" crossorigin="anonymous">
        <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
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
     <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
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
                            <div class="mt-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-4 text-center">
                                        <div class="small-muted">Cumul mois courant</div>
                                        <div id="statsMonthTotal" class="h5 mb-0">0</div>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="small-muted">Cumul semaine</div>
                                        <div id="statsWeekTotal" class="h5 mb-0">0</div>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="small-muted">Aujourd'hui</div>
                                        <div id="statsTodayCount" class="h5 mb-0">0</div>
                                    </div>
                                </div>

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
                                    <h5 class="mb-0">Répartition par mois</h5>
                                    <div class="small-muted">Derniers mois</div>
                                </div>
                                <canvas id="last4MonthsChart" height="120"></canvas>
                                <div class="row g-2 mt-3">
                                    <div class="col-12 col-md-4">
                                        <div class="small-muted">Cumulé par jour (mois en cours)</div>
                                        <canvas id="dailyCumulativeChart" height="120"></canvas>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="small-muted">Cumulé par semaine (mois en cours)</div>
                                        <canvas id="weeklyCumulativeChart" height="120"></canvas>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="small-muted">Cumulé par mois (année en cours)</div>
                                        <canvas id="monthlyCumulativeChart" height="120"></canvas>
                                    </div>
                                </div>
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
        let last4MonthsChart = null;
        let dailyCumulativeChart = null;
        let weeklyCumulativeChart = null;
        let monthlyCumulativeChart = null;

        function initCharts(){
            // Places doughnut
            const placesCtx = document.getElementById('placesChart').getContext('2d');
            placesChart = new Chart(placesCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{ data: [], backgroundColor: ['#7c3aed','#60a5fa','#34d399','#f97316','#f43f5e'] }]
                },
                options: {
                    plugins: { legend: { labels: { color: '#fff' } } }
                }
            });

            // Last N months: bar for monthly counts + line for cumulative
            const last4Ctx = document.getElementById('last4MonthsChart').getContext('2d');
            last4MonthsChart = new Chart(last4Ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'Par mois', type: 'bar', data: [], backgroundColor: '#60a5fa' },
                        { label: 'Cumulé', type: 'line', data: [], borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.08)', tension: 0.25, fill: false }
                    ]
                },
                options: {
                    scales: { x: { ticks: { color: '#cbd5e1' } }, y: { ticks: { color: '#cbd5e1' } } },
                    plugins: { legend: { labels: { color: '#fff' } } }
                }
            });

            // Daily cumulative
            const dailyCtx = document.getElementById('dailyCumulativeChart').getContext('2d');
            dailyCumulativeChart = new Chart(dailyCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Cumulé (jours)', data: [], borderColor: '#34d399', backgroundColor: 'rgba(52,211,153,0.06)', tension: 0.25 }] },
                options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#cbd5e1' } }, y: { ticks: { color: '#cbd5e1' } } } }
            });

            // Weekly cumulative
            const weeklyCtx = document.getElementById('weeklyCumulativeChart').getContext('2d');
            weeklyCumulativeChart = new Chart(weeklyCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Cumulé (semaines)', data: [], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.06)', tension: 0.25 }] },
                options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#cbd5e1' } }, y: { ticks: { color: '#cbd5e1' } } } }
            });

            // Monthly cumulative
            const monthlyCtx = document.getElementById('monthlyCumulativeChart').getContext('2d');
            monthlyCumulativeChart = new Chart(monthlyCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Cumulé (mois)', data: [], borderColor: '#f43f5e', backgroundColor: 'rgba(244,63,94,0.06)', tension: 0.25 }] },
                options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#cbd5e1' } }, y: { ticks: { color: '#cbd5e1' } } } }
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

        async function fetchCharts(){
            try{
                const res = await fetch('/nni/charts-data', { headers: { 'Accept': 'application/json' } });
                if(!res.ok) return null;
                return await res.json();
            }catch(e){ console.error(e); return null; }
        }

        function updateStatsUI(stats){
            if(!stats) return;
                const statsTotalEl = document.getElementById('statsTotal');
                const statsUniqueEl = document.getElementById('statsUnique');
                if (statsTotalEl) statsTotalEl.textContent = stats.total_lookups ?? 0;
                if (statsUniqueEl) statsUniqueEl.textContent = stats.unique_nnis ?? 0;

                // places chart
                const places = stats.top_places || {};
                const placeLabels = Object.keys(places);
                const placeValues = Object.values(places);
                if (placesChart) {
                    placesChart.data.labels = placeLabels;
                    placesChart.data.datasets[0].data = placeValues;
                    placesChart.update();
                }

                // years chart (guarded)
                const years = stats.by_year || {};
                const yearLabels = Object.keys(years);
                const yearValues = Object.values(years);
                if (yearsChart && yearsChart.data && yearsChart.data.datasets && yearsChart.data.datasets[0]) {
                    yearsChart.data.labels = yearLabels;
                    yearsChart.data.datasets[0].data = yearValues;
                    yearsChart.update();
                }
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

            // fetch and render charts data
            const charts = await fetchCharts();
            if (charts && charts.last4Months) {
                last4MonthsChart.data.labels = charts.last4Months.labels || [];
                last4MonthsChart.data.datasets[0].data = charts.last4Months.counts || [];
                last4MonthsChart.data.datasets[1].data = charts.last4Months.cumulative || [];
                last4MonthsChart.update();
            }
            if (charts && charts.current) {
                const d = charts.current.daily || {labels:[], cumulative:[]};
                dailyCumulativeChart.data.labels = d.labels; dailyCumulativeChart.data.datasets[0].data = d.cumulative; dailyCumulativeChart.update();

                const w = charts.current.weekly || {labels:[], cumulative:[]};
                weeklyCumulativeChart.data.labels = w.labels; weeklyCumulativeChart.data.datasets[0].data = w.cumulative; weeklyCumulativeChart.update();

                const m = charts.current.monthly || {labels:[], cumulative:[]};
                monthlyCumulativeChart.data.labels = m.labels; monthlyCumulativeChart.data.datasets[0].data = m.cumulative; monthlyCumulativeChart.update();
            }

            if (charts && charts.summary) {
                document.getElementById('statsMonthTotal').textContent = charts.summary.month_total ?? 0;
                document.getElementById('statsWeekTotal').textContent = charts.summary.week_total ?? 0;
                document.getElementById('statsTodayCount').textContent = charts.summary.today_total ?? 0;
                // Also use the donut chart to show these three summary numbers
                try {
                    const donutLabels = ['Mois', 'Semaine', "Aujourd'hui"];
                    const donutData = [charts.summary.month_total || 0, charts.summary.week_total || 0, charts.summary.today_total || 0];
                    placesChart.data.labels = donutLabels;
                    placesChart.data.datasets[0].data = donutData;
                    placesChart.update();
                } catch (e) { console.error('Failed updating donut with summary', e); }
            }

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

    <!-- Debug overlay tools temporarily disabled to avoid JS parse errors.
         To re-enable, restore the debug button, modal and associated scripts.
     -->
    </body>
    </html>
