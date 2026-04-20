<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/lib/DataRepository.php';

$config = require __DIR__ . '/config/app.php';
$mockData = require __DIR__ . '/data/mock_data.php';

$supabaseClient = new SupabaseClient(
  (string) ($config['supabase']['url'] ?? ''),
  (string) ($config['supabase']['anon_key'] ?? ''),
  (string) ($config['supabase']['schema'] ?? 'public')
);

$repository = new DataRepository($config, $mockData, $supabaseClient);

$consumoDiario = $repository->getConsumoDiario();
$eventos = $repository->getEventos();
$qualidadeAr = $repository->getQualidadeAr();

$mediaConsumo = count($consumoDiario) > 0
  ? array_sum(array_column($consumoDiario, 'kwh')) / count($consumoDiario)
  : 0;

$maxConsumo = count($consumoDiario) > 0
  ? max(array_column($consumoDiario, 'kwh'))
  : 1;

$fonteDados = strtoupper($repository->getProviderName());
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InfoLora - Painel Escolar</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="page-shell">
    <header class="site-header">
      <div class="school-brand">
        <img src="assets/logos/agrupamento-pombal.svg" alt="Agrupamento de Escolas de Pombal">
      </div>
      <h1 class="site-title">InfoLora</h1>
    </header>

    <section class="logo-ribbon" aria-label="Logos institucionais">
      <img src="assets/logos/republica-portuguesa.svg" alt="Republica Portuguesa">
      <img src="assets/logos/eqavet.svg" alt="Selo EQAVET">
      <img src="assets/logos/republica-portuguesa.svg" alt="Republica Portuguesa">
      <img src="assets/logos/eqavet.svg" alt="Selo EQAVET">
    </section>

    <main class="main-grid">
      <section class="info-panel" aria-labelledby="info-title">
        <h2 id="info-title" class="panel-title">Titulo da Informacao</h2>

        <div class="info-content">
          <article class="visual-card">
            <h2>Consumo medio diario da escola</h2>

            <div class="kpi-strip">
              <p>Media semanal</p>
              <p class="kpi-value"><?php echo number_format($mediaConsumo, 1, ",", "."); ?> kWh</p>
              <p>Pico da semana: <?php echo $maxConsumo; ?> kWh</p>

              <div class="mini-chart" aria-label="Grafico de barras do consumo diario">
                <?php foreach ($consumoDiario as $item):
                    $altura = ($item["kwh"] / $maxConsumo) * 100;
                ?>
                  <div class="bar" data-value="<?php echo round($altura); ?>">
                    <span><?php echo htmlspecialchars($item["dia"]); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <section class="calendar" aria-label="Calendario de eventos">
              <h3>Calendario de eventos</h3>
              <ul>
                <?php foreach ($eventos as $evento): ?>
                  <li><?php echo htmlspecialchars($evento); ?></li>
                <?php endforeach; ?>
              </ul>
            </section>
          </article>

          <div>
            <ul class="info-list">
              <li>Consumo medio diario atualizado por dia</li>
              <li>Calendario com eventos da escola</li>
              <li>Qualidade do ar das salas 317 ate 321</li>
              <li>Estado atual da meteorologia em Pombal</li>
              <li>Fonte de dados ativa: <?php echo htmlspecialchars($fonteDados); ?></li>
            </ul>

            <section class="air-quality" aria-label="Qualidade do ar por sala">
              <h3>Qualidade do ar das salas 317 a 321</h3>
              <ul>
                <?php foreach ($qualidadeAr as $item): ?>
                  <li data-room-co2="<?php echo $item["co2"]; ?>">
                    Sala <?php echo $item["sala"]; ?>: CO2 <?php echo $item["co2"]; ?> ppm | PM2.5 <?php echo $item["pm25"]; ?> ug/m3
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          </div>
        </div>
      </section>

      <aside class="meteo-panel" aria-labelledby="meteo-title">
        <h2 id="meteo-title">Meteorologia - Pombal</h2>

        <div class="meteo-box">
          <div class="weather-now">
            <p id="weather-temp" class="weather-temp">--</p>
            <p id="weather-desc" class="weather-desc">A carregar...</p>
          </div>

          <p id="weather-humidity" class="weather-meta">Humidade: --</p>
          <p id="weather-wind" class="weather-meta">Vento: --</p>
          <span id="air-status" class="status-pill status-good">Qualidade do ar: --</span>
        </div>

        <p id="weather-updated" class="updated-at">Atualizado: --</p>
      </aside>
    </main>

    <footer class="footer-ribbon" aria-label="Logos de financiamento e parceiros">
      <img src="assets/logos/pessoas-2030.svg" alt="Pessoas 2030">
      <img src="assets/logos/construcao-futuro.svg" alt="A construcao do futuro">
      <img src="assets/logos/pessoas-2030.svg" alt="Pessoas 2030">
      <img src="assets/logos/construcao-futuro.svg" alt="A construcao do futuro">
    </footer>
  </div>

  <script src="script.js" defer></script>
</body>
</html>
