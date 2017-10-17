--
-- Add Marking column to fac_Ports table
--
ALTER TABLE fac_Ports ADD COLUMN MarkingID VARCHAR(20) NOT NULL DEFAULT '' AFTER Notes;

--
-- Bump up the database version (uncomment below once released)
--

UPDATE fac_Config set Value="4.5.1-m2" WHERE Parameter="Version";
