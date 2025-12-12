<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Connexion</title>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
	<style>
		body{font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial; min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0f172a 0%,#07102a 100%);padding:24px}
		.auth-card{max-width:920px;width:100%;border-radius:14px;overflow:hidden;box-shadow:0 10px 40px rgba(2,6,23,0.6)}
		.left{background:linear-gradient(180deg,#7c3aed 0%,#4f46e5 100%);color:#fff;padding:40px;display:flex;flex-direction:column;justify-content:center}
		.right{background:#fff;padding:36px}
		.brand{font-weight:700;letter-spacing:0.5px}
		.form-control:focus{box-shadow:none;border-color:#7c3aed}
		.small-muted{color:#6b7280}
		.logo-circle{width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:22px}
		@media(max-width:768px){.left{padding:24px;text-align:center}.right{padding:24px}}
	</style>
</head>
<body>
	<div class="container">
		<div class="auth-card mx-auto d-flex shadow-sm">
			<div class="left col-12 col-md-5">
				<div class="d-flex align-items-start gap-3 mb-3">
					<div class="logo-circle">NN</div>
					<div>
						<div class="brand h4 mb-0">Mon Projet</div>
						<div class="small-muted">Portail sécurisé</div>
					</div>
				</div>
				<div class="mt-3">
					<h3 class="mb-2">Bienvenue</h3>
					<p class="small-muted">Entrez vos identifiants pour accéder à l'espace sécurisé. Design simple et responsive avec Bootstrap.</p>
					<ul class="mt-3 small-muted">
						<li>Accès rapide</li>
						<li>Sécurisé</li>
						<li>Responsive</li>
					</ul>
				</div>
			</div>
			<div class="right col-12 col-md-7">
				<h5 class="mb-3">Se connecter</h5>
				<form method="POST" action="{{ url('/login') }}">
					@csrf
					<div class="mb-3">
						<label for="email" class="form-label">Adresse e-mail</label>
						<input id="email" type="email" name="email" class="form-control form-control-lg" placeholder="votre@email.com" required autofocus>
					</div>

					<div class="mb-3">
						<label for="password" class="form-label">Mot de passe</label>
						<input id="password" type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
					</div>

					<div class="d-flex justify-content-between align-items-center mb-3">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="remember" id="remember">
							<label class="form-check-label small" for="remember">Se souvenir de moi</label>
						</div>
						<div><a href="#" class="small">Mot de passe oublié ?</a></div>
					</div>

					<div class="d-grid mb-2">
						<button type="submit" class="btn btn-primary btn-lg">Se connecter</button>
					</div>

					<div class="text-center small-muted">ou se connecter avec</div>
					<div class="d-flex gap-2 justify-content-center mt-3">
						<button type="button" class="btn btn-outline-secondary btn-sm">Google</button>
						<button type="button" class="btn btn-outline-secondary btn-sm">Facebook</button>
					</div>

					<div class="text-center mt-4 small-muted">Pas de compte ? <a href="#">S'inscrire</a></div>
				</form>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
