<?php

declare(strict_types=1);

require_once __DIR__ . '/SupabaseClient.php';

final class DataRepository
{
    private array $config;
    private array $mockData;
    private ?SupabaseClient $supabaseClient;

    public function __construct(array $config, array $mockData, ?SupabaseClient $supabaseClient = null)
    {
        $this->config = $config;
        $this->mockData = $mockData;
        $this->supabaseClient = $supabaseClient;
    }

    public function getProviderName(): string
    {
        return $this->isSupabaseReady() ? 'supabase' : 'mock';
    }

    /**
     * @return array<int, array{dia: string, kwh: int}>
     */
    public function getConsumoDiario(): array
    {
        if ($this->isSupabaseReady()) {
            try {
                $rows = $this->supabaseClient->select(
                    $this->config['supabase']['tables']['consumo_diario'],
                    'dia_sigla,kwh,ordem',
                    ['order' => 'ordem.asc']
                );

                return array_map(
                    static fn (array $row): array => [
                        'dia' => (string) ($row['dia_sigla'] ?? ''),
                        'kwh' => (int) ($row['kwh'] ?? 0),
                    ],
                    $rows
                );
            } catch (Throwable $exception) {
                // Fallback para mock em caso de indisponibilidade externa.
            }
        }

        return $this->mockData['consumo_diario'];
    }

    /**
     * @return array<int, string>
     */
    public function getEventos(): array
    {
        if ($this->isSupabaseReady()) {
            try {
                $rows = $this->supabaseClient->select(
                    $this->config['supabase']['tables']['eventos_escola'],
                    'data_evento,descricao',
                    ['order' => 'data_evento.asc']
                );

                return array_map(
                    static function (array $row): string {
                        $data = (string) ($row['data_evento'] ?? '');
                        $descricao = (string) ($row['descricao'] ?? '');

                        if ($data === '') {
                            return $descricao;
                        }

                        $safeDate = date_create($data);
                        $labelDate = $safeDate instanceof DateTimeInterface ? $safeDate->format('d/m') : $data;

                        return $labelDate . ' - ' . $descricao;
                    },
                    $rows
                );
            } catch (Throwable $exception) {
                // Fallback para mock em caso de indisponibilidade externa.
            }
        }

        return $this->mockData['eventos'];
    }

    /**
     * @return array<int, array{sala: int, co2: int, pm25: int}>
     */
    public function getQualidadeAr(): array
    {
        if ($this->isSupabaseReady()) {
            try {
                $rows = $this->supabaseClient->select(
                    $this->config['supabase']['tables']['qualidade_ar'],
                    'sala,co2,pm25',
                    ['order' => 'sala.asc']
                );

                return array_map(
                    static fn (array $row): array => [
                        'sala' => (int) ($row['sala'] ?? 0),
                        'co2' => (int) ($row['co2'] ?? 0),
                        'pm25' => (int) ($row['pm25'] ?? 0),
                    ],
                    $rows
                );
            } catch (Throwable $exception) {
                // Fallback para mock em caso de indisponibilidade externa.
            }
        }

        return $this->mockData['qualidade_ar'];
    }

    private function isSupabaseReady(): bool
    {
        return $this->config['data_provider'] === 'supabase'
            && $this->supabaseClient instanceof SupabaseClient
            && $this->supabaseClient->isConfigured();
    }
}
