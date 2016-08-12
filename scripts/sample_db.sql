-- Version 0.0.6
\c provisioning

-- Set some samples/defaults
insert into subscriber values(
	DEFAULT,
	'1000',
	'test123',
	'test.net',
	'Test Extension 1000',
	'Extension 1000',
	'1000@test.net',
	'*771000',
	DEFAULT,
	DEFAULT
);
insert into devicetype values(DEFAULT,'Aastra','Generic','aastra/phone.cfg','Basic Aastra config');
insert into devicetype values(DEFAULT,'Cisco','Generic','cisco/phone.xml','Basic Cisco config');
insert into devicetype values(DEFAULT,'Polycom','Generic','polycom/default.cfg','Basic Polycom SoundPoint config');
insert into devicetype values(DEFAULT,'SNOM','Generic','snom/phone.xml','Basic SNOM config');
insert into device values('deadbeefcafe',1,1,'Sample device');
insert into line values(DEFAULT,'deadbeefcafe',1,1);
insert into domain values(
	DEFAULT,
	'test.net',
	'10.0.0.10',
	DEFAULT,
	DEFAULT,
	DEFAULT,
	'10.0.0.10',
	DEFAULT,
	DEFAULT,
	DEFAULT,
	DEFAULT,
	DEFAULT,
	DEFAULT,
	DEFAULT,
	600,
	DEFAULT,
	'Default network-wide settings'
);
insert into codec values('g711u','bitrate=64','g711 mu-law no compression (US and Japan)');
insert into codec values('g711a','bitrate=64','g711 A-law no compression (Europe)');
insert into codec values('g711u16khz','bitrate=64','g711 mu-law no compression @ 16000hz (US and Japan)');
insert into codec values('g711a16khz','bitrate=64','g711 A-law no compression @ 16000hz (Europe)');
insert into codec values('g722','bitrate=64','g722 wide-band codec');
insert into codec values('g7231','framesize=30 bitrate=6.3','g723.1 (licensed) Part of H.323 standard, low-bandwidth');
insert into codec values('g726','bitrate=32','ADPCM high-compression international toll-quality');
insert into codec values('g728','bitrate=16','ADPCM');
insert into codec values('g729','framesize=10 bitrate=8','g729 (licensed) narrow-band codec');
insert into codec values('gsm','framesize=20 bitrate=13','GSM');
insert into codec values('ilbc','framesize=20 bitrate=15','iLBC free narrow-band codec');
insert into codec values('speex','framesize=20 bitrate=44','Speex open-source voice codec');
insert into codecgroup values(DEFAULT,1,'g711a',1);
insert into codecgroup values(DEFAULT,1,'g711u',2);
