-- Run ONCE after schema import. No real voter, admin, or vote data is created.
INSERT INTO elections (title,slug,registration_open,results_published)
VALUES('AMSA AIU Election 2026','amsa-aiu-2026',0,0);
SET @e=(SELECT id FROM elections WHERE slug='amsa-aiu-2026');
INSERT INTO election_rounds(election_id,round_number,round_type,status) VALUES(@e,1,'main','draft');
SET @r=(SELECT id FROM election_rounds WHERE election_id=@e AND round_number=1);
INSERT INTO positions(election_id,name,slug,display_order) VALUES
(@e,'President','president',1),
(@e,'Vice President','vice-president',2),
(@e,'Secretary','secretary',3),
(@e,'Male Treasurer','male-treasurer',4),
(@e,'Female Treasurer','female-treasurer',5);
INSERT INTO round_positions(round_id,position_id) SELECT @r,id FROM positions WHERE election_id=@e;
