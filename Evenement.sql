UPDATE sortie as s SET s.etat_id = 3 WHERE s.date_limite_inscription <= NOW();
UPDATE sortie as s SET s.etat_id = 4 WHERE s.date_heure_debut <= NOW();
UPDATE sortie as s SET s.etat_id = 5 WHERE DATE_ADD(s.date_heure_debut, INTERVAL s.duree MINUTE)  <= NOW();
UPDATE sortie as s SET s.etat_id = 7 WHERE DATE_ADD(DATE_ADD(s.date_heure_debut, INTERVAL s.duree MINUTE), INTERVAL '31' DAY) <= NOW();