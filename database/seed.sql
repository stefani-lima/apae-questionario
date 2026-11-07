-- Perguntas padrão (executado apenas quando a tabela está vazia).
-- Validar o texto com a coordenação da APAE antes de colocar em produção.

INSERT INTO perguntas (texto, tipo, obrigatoria, ativa, ordem) VALUES
  ('Como você avalia o atendimento na recepção?',                              'escala', TRUE,  TRUE, 1),
  ('A equipe foi atenciosa e respeitosa com você?',                            'escala', TRUE,  TRUE, 2),
  ('Como foi o tempo de espera para ser atendido?',                            'escala', TRUE,  TRUE, 3),
  ('Como você avalia a limpeza e o conforto do espaço?',                       'escala', TRUE,  TRUE, 4),
  ('Como você avalia os atendimentos e atividades oferecidos ao assistido?',   'escala', FALSE, TRUE, 5),
  ('De modo geral, qual é a sua satisfação com a APAE?',                       'escala', TRUE,  TRUE, 6),
  ('Quer deixar um elogio, sugestão ou crítica?',                              'texto',  FALSE, TRUE, 7);
