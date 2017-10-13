--
-- Add a table of DeviceType Field values to allow
--

CREATE TABLE fac_DeviceTypes (
	TypeID INT(11) NOT NULL AUTO_INCREMENT,
	Type varchar(23) NOT NULL,
	PRIMARY KEY(TypeID)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

INSERT INTO fac_DeviceTypes (Type) VALUES

  ('Server'),
  ('Appliance'),
  ('Storage Array'),
  ('Chassis'),
  ('Patch Panel'),
  ('Physical Infrastructure'),
  ('Sensor'),
  ('Load Balancer'),
  ('Switch'),
  ('Serial Switch'),
  ('LAN Switch'),
  ('FEX Switch'),
  ('SAN Switch'),
  ('Cluster Switch'),
  ('Fabric Switch'),
  ('Tape Library'),
  ('Data Base Engine'),
  ('Crypto Farm'),
  ('Crypto Gateway'),
  ('Router'),
  ('Firewall'),
  ('Optimization Manager'),
  ('SAN'),
  ('Workstation'),
  ('Server HMC'),
  ('IPS'),
  ('JBOD'),
  ('UCS'),
  ('Gateway');
  
--
-- Bump up the database version (uncomment below once released)
--

UPDATE fac_Config set Value="4.5-m1" WHERE Parameter="Version";
