\c provisioning

CREATE SEQUENCE subscriber_id_seq;

GRANT ALL ON subscriber_id_seq TO provisioning;
GRANT SELECT ON subscriber_id_seq TO provisioningro;

CREATE TABLE subscriber (
	id int PRIMARY KEY NOT NULL DEFAULT nextval('subscriber_id_seq'),
	username varchar(64) NOT NULL DEFAULT '',
	password varchar(25) NOT NULL DEFAULT '',
	domain varchar(64) NOT NULL DEFAULT '',
	screenname varchar(64) NOT NULL DEFAULT '',
	rpid varchar(64) NOT NULL DEFAULT '',
	email_address varchar(64) NOT NULL DEFAULT '',
	vmail varchar(64) NOT NULL DEFAULT '',
	shared boolean DEFAULT true,
	enabled boolean DEFAULT true
);

GRANT ALL ON subscriber TO provisioning;
GRANT SELECT ON subscriber TO provisioningro;

CREATE TABLE device (
	id char(12) PRIMARY KEY,
	devicetype_id integer,
	domain_id integer,
	description varchar(256) NOT NULL DEFAULT ''
);

GRANT ALL ON device TO provisioning;
GRANT SELECT ON device TO provisioningro;

CREATE SEQUENCE devicetype_id_seq;

GRANT ALL ON devicetype_id_seq TO provisioning;
GRANT SELECT ON devicetype_id_seq TO provisioningro;

CREATE TABLE devicetype (
	id int PRIMARY KEY NOT NULL DEFAULT nextval('devicetype_id_seq'),
	make varchar(25) NOT NULL DEFAULT '',
	model varchar(25) NOT NULL DEFAULT '',
	template varchar(255) NOT NULL DEFAULT '',
	description varchar(256) NOT NULL DEFAULT ''
);

GRANT ALL ON devicetype TO provisioning;
GRANT SELECT ON devicetype TO provisioningro;

CREATE SEQUENCE domain_id_seq;

GRANT ALL ON domain_id_seq TO provisioning;
GRANT SELECT ON domain_id_seq TO provisioningro;

CREATE TABLE domain (
	id int PRIMARY KEY NOT NULL DEFAULT nextval('domain_id_seq'),
	domain varchar(64) NOT NULL DEFAULT '',
	reg1 varchar(64) NOT NULL DEFAULT '',
	reg1port smallint DEFAULT 5060,
	reg2 varchar(64) NOT NULL DEFAULT '',
	reg2port smallint DEFAULT 5060,
	proxy1 varchar(64) NOT NULL DEFAULT '',
	proxy1port smallint DEFAULT 5060,
	proxy2 varchar(64) NOT NULL DEFAULT '',
	proxy2port smallint DEFAULT 5060,
	outproxy varchar(64) NOT NULL DEFAULT '',
	outproxyport smallint DEFAULT 5060,
	mohurl varchar(64) NOT NULL DEFAULT '',
	time varchar(65) NOT NULL DEFAULT '',
	regtimeout int DEFAULT 600,
	dialplan varchar(256) NOT NULL DEFAULT '',
	description varchar(256) NOT NULL DEFAULT ''
);

GRANT ALL ON domain TO provisioning;
GRANT SELECT ON domain TO provisioningro;

CREATE SEQUENCE line_id_seq;

GRANT ALL ON line_id_seq TO provisioning;
GRANT SELECT ON line_id_seq TO provisioningro;

CREATE TABLE line (
	id int PRIMARY KEY NOT NULL DEFAULT nextval('line_id_seq'),
	device_id char(12),
	subscriber_id integer,
	linenum smallint DEFAULT 1
);

GRANT ALL ON line TO provisioning;
GRANT SELECT ON line TO provisioningro;

CREATE TABLE codec (
	id varchar(64) PRIMARY KEY NOT NULL,
	parameters varchar(256) DEFAULT '',
	description varchar(256) NOT NULL DEFAULT ''
);

GRANT ALL ON codec TO provisioning;
GRANT SELECT ON codec TO provisioningro;

CREATE SEQUENCE codecgroup_id_seq;

GRANT ALL ON codecgroup_id_seq TO provisioning;
GRANT SELECT ON codecgroup_id_seq TO provisioningro;

CREATE TABLE codecgroup (
	id int PRIMARY KEY NOT NULL DEFAULT nextval('codecgroup_id_seq'),
	domain_id int,
	codec_id varchar(64),
	priority smallint
);

GRANT ALL ON codecgroup TO provisioning;
GRANT SELECT ON codecgroup TO provisioningro;
