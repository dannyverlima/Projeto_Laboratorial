# Projeto_Laboratorial

Painel escolar em PHP, HTML, CSS e JS com duas fontes de dados:

- `mock`: dados locais simulados para desenvolvimento rapido.
- `supabase`: dados reais via API REST do Supabase.

## Estrutura

- `index.php`: interface principal.
- `bootstrap.php`: carregamento de variaveis de ambiente (`.env`).
- `config/app.php`: configuracao da fonte de dados e tabelas.
- `lib/SupabaseClient.php`: cliente HTTP para PostgREST do Supabase.
- `lib/DataRepository.php`: camada de repositorio com fallback para mock.
- `data/mock_data.php`: base de dados imaginaria local.
- `supabase/schema.sql`: schema SQL para criar tabelas no Supabase.
- `supabase/seed.sql`: dados iniciais para povoamento.

## Como executar localmente

1. Inicie servidor PHP na raiz do projeto:
	- `php -S localhost:8080`
2. Abra no navegador:
	- `http://localhost:8080`

## Como ligar ao Supabase

1. Crie o projeto no Supabase.
2. No SQL Editor, execute os arquivos:
	- `supabase/schema.sql`
	- `supabase/seed.sql`
3. Crie um arquivo `.env` na raiz, com base no `.env.example`.
4. Ajuste os valores:
	- `DATA_PROVIDER=supabase`
	- `SUPABASE_URL=https://SEU-PROJETO.supabase.co`
	- `SUPABASE_ANON_KEY=...`
5. Recarregue a pagina. O painel mostra no bloco de informacoes se a fonte ativa e `MOCK` ou `SUPABASE`.

## Observacao

Se o Supabase ficar indisponivel ou mal configurado, o sistema faz fallback automatico para dados mock para o painel continuar operacional.