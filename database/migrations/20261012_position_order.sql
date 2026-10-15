-- Optional metadata alignment for an existing AMSA AIU 2026 database.
-- Runtime queries also enforce this order, so applying this does not change IDs or relationships.
START TRANSACTION;

UPDATE positions p
JOIN elections e ON e.id=p.election_id
SET p.display_order=p.display_order+10
WHERE e.slug='amsa-aiu-2026';

UPDATE positions p
JOIN elections e ON e.id=p.election_id
SET p.display_order=CASE p.slug
  WHEN 'president' THEN 1
  WHEN 'vice-president' THEN 2
  WHEN 'secretary' THEN 3
  WHEN 'male-treasurer' THEN 4
  WHEN 'female-treasurer' THEN 5
  ELSE p.display_order
END
WHERE e.slug='amsa-aiu-2026';

COMMIT;
