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
$consumoSemanaPassada = $repository->getConsumoSemanaPassada();
$eventos = $repository->getEventos();
$qualidadeAr = $repository->getQualidadeAr();
$ementaAlmocos = $repository->getEmentaAlmocos();
$fonteDados = strtoupper($repository->getProviderName());

if (count($consumoSemanaPassada) !== count($consumoDiario) || count($consumoSemanaPassada) === 0) {
  $consumoSemanaPassada = array_map(
    static fn (array $item): array => [
      'dia' => (string) ($item['dia'] ?? ''),
      'kwh' => (int) round(((int) ($item['kwh'] ?? 0)) * 1.08),
    ],
    $consumoDiario
  );
}

$mediaConsumo = count($consumoDiario) > 0
  ? array_sum(array_column($consumoDiario, 'kwh')) / count($consumoDiario)
  : 0;

$mediaSemanaPassada = count($consumoSemanaPassada) > 0
  ? array_sum(array_column($consumoSemanaPassada, 'kwh')) / count($consumoSemanaPassada)
  : 0;

$maxConsumo = count($consumoDiario) > 0
  ? max(array_column($consumoDiario, 'kwh'))
  : 1;

$minConsumo = count($consumoDiario) > 0
  ? min(array_column($consumoDiario, 'kwh'))
  : 0;

$variacaoConsumo = $mediaSemanaPassada > 0
  ? (($mediaConsumo - $mediaSemanaPassada) / $mediaSemanaPassada) * 100
  : 0;

$textoComparativo = 'O consumo desta semana manteve-se estavel em comparacao com a semana anterior.';
if ($mediaSemanaPassada > 0) {
  if (abs($variacaoConsumo) < 1.5) {
    $textoComparativo = 'O consumo desta semana manteve-se estavel em comparacao com a semana anterior.';
  } elseif ($variacaoConsumo < 0) {
    $textoComparativo = sprintf(
      'O consumo reduziu %.1f%% em relacao a semana anterior.',
      abs($variacaoConsumo)
    );
  } else {
    $textoComparativo = sprintf(
      'O consumo aumentou %.1f%% em relacao a semana anterior.',
      abs($variacaoConsumo)
    );
  }
}

$eventosOrganizados = array_map(
  static function (string $evento): array {
    if (preg_match('/^(\d{2}\/\d{2})\s*-\s*(.+)$/', $evento, $matches) === 1) {
      return [
        'data' => $matches[1],
        'descricao' => $matches[2],
      ];
    }

    return [
      'data' => '--/--',
      'descricao' => $evento,
    ];
  },
  array_slice($eventos, 0, 5)
);

$salasFixas = [317, 318, 319, 320, 321];
$qualidadeLookup = [];
foreach ($qualidadeAr as $item) {
  $qualidadeLookup[(int) ($item['sala'] ?? 0)] = [
    'co2' => (int) ($item['co2'] ?? 0),
    'pm25' => (int) ($item['pm25'] ?? 0),
  ];
}

$qualidadePorSala = [];
foreach ($salasFixas as $sala) {
  $co2 = (int) ($qualidadeLookup[$sala]['co2'] ?? 900);
  $pm25 = (int) ($qualidadeLookup[$sala]['pm25'] ?? 10);

  if ($co2 <= 900) {
    $estado = 'Boa';
    $estadoClass = 'status-good';
  } elseif ($co2 <= 1000) {
    $estado = 'Moderada';
    $estadoClass = 'status-moderate';
  } else {
    $estado = 'Ma';
    $estadoClass = 'status-bad';
  }

  $qualidadePorSala[] = [
    'sala' => $sala,
    'co2' => $co2,
    'pm25' => $pm25,
    'estado' => $estado,
    'estado_class' => $estadoClass,
  ];
}

$ementaSemanal = count($ementaAlmocos) > 0
  ? array_slice($ementaAlmocos, 0, 5)
  : [
      ['dia' => 'Segunda', 'prato' => 'Sem dados de ementa', 'vegetariano' => 'N/D', 'sopa' => 'N/D', 'sobremesa' => 'N/D'],
    ];

$topLogos = [
  ['src' => 'assets/logos/republica-portuguesa.svg', 'alt' => 'Republica Portuguesa'],
  ['src' => 'assets/logos/eqavet.svg', 'alt' => 'Selo EQAVET'],
  ['src' => 'assets/logos/republica-portuguesa.svg', 'alt' => 'Republica Portuguesa'],
  ['src' => 'assets/logos/eqavet.svg', 'alt' => 'Selo EQAVET'],
];

$footerLogos = [
  ['src' => 'assets/logos/pessoas-2030.svg', 'alt' => 'Pessoas 2030'],
  ['src' => 'assets/logos/construcao-futuro.svg', 'alt' => 'A construcao do futuro'],
  ['src' => 'assets/logos/pessoas-2030.svg', 'alt' => 'Pessoas 2030'],
  ['src' => 'assets/logos/construcao-futuro.svg', 'alt' => 'A construcao do futuro'],
];
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
      <div class="logo-track">
        <?php foreach ($topLogos as $logo): ?>
          <img src="<?php echo htmlspecialchars($logo['src']); ?>" alt="<?php echo htmlspecialchars($logo['alt']); ?>">
        <?php endforeach; ?>
        <?php foreach ($topLogos as $logo): ?>
          <img src="<?php echo htmlspecialchars($logo['src']); ?>" alt="<?php echo htmlspecialchars($logo['alt']); ?>">
        <?php endforeach; ?>
      </div>
    </section>

    <main class="main-grid">
      <section class="info-panel" aria-label="Area principal de informacoes">
        <h2 class="panel-title">Titulo da Informacao</h2>

        <div class="info-rotator" data-rotator aria-live="polite">
          <div class="rotator-stage">
            <article class="info-slide is-active" data-slide aria-hidden="false">
              <h3 class="slide-title">Consumo diario</h3>

              <div class="consumo-kpis">
                <article class="kpi-card">
                  <p class="kpi-label">Media semanal</p>
                  <p class="kpi-value"><?php echo number_format($mediaConsumo, 1, ',', '.'); ?> kWh</p>
                </article>
                <article class="kpi-card">
                  <p class="kpi-label">Pico semanal</p>
                  <p class="kpi-value"><?php echo (int) $maxConsumo; ?> kWh</p>
                </article>
                <article class="kpi-card">
                  <p class="kpi-label">Minimo semanal</p>
                  <p class="kpi-value"><?php echo (int) $minConsumo; ?> kWh</p>
                </article>
              </div>

              <div class="mini-chart" aria-label="Grafico de consumo diario">
                <?php foreach ($consumoDiario as $item):
                    $altura = $maxConsumo > 0 ? ((int) ($item['kwh'] ?? 0) / $maxConsumo) * 100 : 0;
                ?>
                  <div class="consumo-bar" data-value="<?php echo (int) round($altura); ?>">
                    <span class="bar-kwh"><?php echo (int) ($item['kwh'] ?? 0); ?></span>
                    <span class="bar-day"><?php echo htmlspecialchars((string) ($item['dia'] ?? '')); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>

              <p class="comparison-text"><?php echo htmlspecialchars($textoComparativo); ?></p>
            </article>

            <article class="info-slide" data-slide aria-hidden="true">
              <h3 class="slide-title">Calendario de eventos</h3>
              <ul class="events-list">
                <?php foreach ($eventosOrganizados as $evento): ?>
                  <li class="event-item">
                    <span class="event-date"><?php echo htmlspecialchars((string) $evento['data']); ?></span>
                    <span class="event-desc"><?php echo htmlspecialchars((string) $evento['descricao']); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </article>

            <article class="info-slide" data-slide aria-hidden="true">
              <h3 class="slide-title">Qualidade do ar</h3>
              <div class="air-grid">
                <?php foreach ($qualidadePorSala as $sala): ?>
                  <article class="air-card <?php echo htmlspecialchars((string) $sala['estado_class']); ?>" data-room-co2="<?php echo (int) $sala['co2']; ?>">
                    <p class="air-room">Sala <?php echo (int) $sala['sala']; ?></p>
                    <p class="air-state"><?php echo htmlspecialchars((string) $sala['estado']); ?></p>
                    <p class="air-values">CO2 <?php echo (int) $sala['co2']; ?> ppm | PM2.5 <?php echo (int) $sala['pm25']; ?> ug/m3</p>
                  </article>
                <?php endforeach; ?>
              </div>
            </article>

            <article class="info-slide" data-slide aria-hidden="true">
              <h3 class="slide-title">Ementa dos almocos</h3>
              <div class="menu-grid">
                <?php foreach ($ementaSemanal as $menu): ?>
                  <article class="menu-card">
                    <p class="menu-day"><?php echo htmlspecialchars((string) ($menu['dia'] ?? '')); ?></p>
                    <p class="menu-main"><?php echo htmlspecialchars((string) ($menu['prato'] ?? 'N/D')); ?></p>
                    <p class="menu-extra">Sopa: <?php echo htmlspecialchars((string) ($menu['sopa'] ?? 'N/D')); ?></p>
                    <p class="menu-extra">Vegetariano: <?php echo htmlspecialchars((string) ($menu['vegetariano'] ?? 'N/D')); ?></p>
                    <p class="menu-extra">Sobremesa: <?php echo htmlspecialchars((string) ($menu['sobremesa'] ?? 'N/D')); ?></p>
                  </article>
                <?php endforeach; ?>
              </div>
            </article>
          </div>

          <div class="slide-indicators" aria-label="Indicadores de conteudo">
            <button class="indicator is-active" type="button" data-slide-to="0" aria-label="Consumo diario" aria-current="true"></button>
            <button class="indicator" type="button" data-slide-to="1" aria-label="Calendario de eventos" aria-current="false"></button>
            <button class="indicator" type="button" data-slide-to="2" aria-label="Qualidade do ar" aria-current="false"></button>
            <button class="indicator" type="button" data-slide-to="3" aria-label="Ementa dos almocos" aria-current="false"></button>
          </div>
        </div>
      </section>

      <aside class="meteo-panel" aria-label="Meteorologia">
        <h2 class="meteo-title">Meteorologia - Pombal</h2>

        <div class="meteo-box">
          <p id="weather-temp" class="weather-temp">-- C</p>
          <p id="weather-desc" class="weather-desc">A carregar dados meteorologicos...</p>

          <div class="weather-meta">
            <p id="weather-humidity">Humidade: --%</p>
            <p id="weather-wind">Vento: -- km/h</p>
            <p id="weather-updated">Atualizado: --</p>
          </div>

          <span id="air-status" class="air-pill">Qualidade do ar: --</span>
        </div>

        <p class="source-tag">Fonte de dados ativa: <?php echo htmlspecialchars($fonteDados); ?></p>
      </aside>
    </main>

    <footer class="footer-ribbon" aria-label="Logos de financiamento e parceiros">
      <div class="logo-track footer-track">
        <?php foreach ($footerLogos as $logo): ?>
          <img src="<?php echo htmlspecialchars($logo['src']); ?>" alt="<?php echo htmlspecialchars($logo['alt']); ?>">
        <?php endforeach; ?>
        <?php foreach ($footerLogos as $logo): ?>
          <img src="<?php echo htmlspecialchars($logo['src']); ?>" alt="<?php echo htmlspecialchars($logo['alt']); ?>">
        <?php endforeach; ?>
      </div>
    </footer>
  </div>

  <script src="script.js" defer></script>
</body>
</html>
