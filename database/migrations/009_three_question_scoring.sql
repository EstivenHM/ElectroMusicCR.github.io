-- Migration: 009_three_question_scoring.sql
-- Description: Update trivia_submissions to enforce single attempt per player per trivia

ALTER TABLE `trivia_submissions`
  DROP KEY `uq_submission_player_trivia_attempt`,
  ADD UNIQUE KEY `uq_submission_player_trivia` (`player_id`, `trivia_id`);
