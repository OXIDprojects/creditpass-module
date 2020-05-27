# Uncomment this line if you execute SQL manually for MySQL 5
# SET @@session.sql_mode = '';

--
-- Tabellenstruktur für Tabelle `oecreditpasscache`
--

DROP TABLE IF EXISTS `oecreditpasscache`;
CREATE TABLE `oecreditpasscache` (
  `ID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL default '',
  `USERID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL default '',
  `ASSESSMENTRESULT` blob NOT NULL,
  `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Timestamp',
  `USERIDENT` varchar(32) NOT NULL default '',
  `PAYMENTID` varchar(32) NOT NULL default '',
  `PAYMENTDATA` varchar(32) NOT NULL default '',
  `ANSWERCODE` varchar(2) NOT NULL default '' COMMENT 'Answer code',
  PRIMARY KEY  (`ID`),
  KEY `USERID` (`USERID`),
  KEY `TIMESTAMP` (`TIMESTAMP`),
  KEY `BONICHECKMODE` (`BONICHECKMODE`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

