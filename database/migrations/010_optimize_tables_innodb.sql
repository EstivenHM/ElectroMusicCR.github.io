-- =====================================================================
-- ElectroMusicCR - Script de optimización de rendimiento MariaDB/MySQL
-- Aplica ENGINE=InnoDB y verifica índices para evitar 502 por bloqueos de tabla
-- Seguro para ejecutar en phpMyAdmin en hosting compartido (InfinityFree)
-- =====================================================================

-- 1. Asegurar motor transaccional InnoDB en todas las tablas
-- (InnoDB usa bloqueos a nivel de fila; MyISAM bloquea toda la tabla en escrituras causando 502)
ALTER TABLE `events` ENGINE=InnoDB;
ALTER TABLE `news` ENGINE=InnoDB;
ALTER TABLE `trivia` ENGINE=InnoDB;
ALTER TABLE `trivia_questions` ENGINE=InnoDB;
ALTER TABLE `trivia_options` ENGINE=InnoDB;
ALTER TABLE `trivia_players` ENGINE=InnoDB;
ALTER TABLE `trivia_submissions` ENGINE=InnoDB;
ALTER TABLE `trivia_settings` ENGINE=InnoDB;
ALTER TABLE `users` ENGINE=InnoDB;

-- 2. Asegurar optimización de espacio y estadísticas de los índices
OPTIMIZE TABLE `events`, `news`, `trivia`, `trivia_questions`, `trivia_options`, `trivia_players`, `trivia_submissions`;
