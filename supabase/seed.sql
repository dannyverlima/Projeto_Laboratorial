insert into public.consumo_diario (dia_sigla, ordem, kwh)
values
  ('Seg', 1, 286),
  ('Ter', 2, 302),
  ('Qua', 3, 297),
  ('Qui', 4, 315),
  ('Sex', 5, 289)
on conflict do nothing;

insert into public.eventos_escola (data_evento, descricao)
values
  ('2026-04-22', 'Reuniao de delegados de turma'),
  ('2026-04-24', 'Mostra de projetos laboratoriais'),
  ('2026-04-29', 'Simulacro de seguranca'),
  ('2026-05-03', 'Feira da sustentabilidade'),
  ('2026-05-08', 'Sessao com encarregados de educacao')
on conflict do nothing;

insert into public.qualidade_ar (sala, co2, pm25)
values
  (317, 780, 8),
  (318, 860, 11),
  (319, 920, 13),
  (320, 810, 9),
  (321, 880, 10)
on conflict do nothing;
