#
# Table structure for table 'tx_t3elasticsearch_server'
#
CREATE TABLE tx_t3elasticsearch_server (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted smallint unsigned DEFAULT '0' NOT NULL,
    hidden smallint unsigned DEFAULT '0' NOT NULL,

    identifier varchar(255) DEFAULT '' NOT NULL,
    host varchar(255) DEFAULT '' NOT NULL,
    port int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_t3elasticsearch_index'
#
CREATE TABLE tx_t3elasticsearch_index (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,

    tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    crdate int(11) unsigned DEFAULT '0' NOT NULL,
    cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
    deleted smallint unsigned DEFAULT '0' NOT NULL,
    hidden smallint unsigned DEFAULT '0' NOT NULL,

    identifier varchar(255) DEFAULT '' NOT NULL,
    configuration text,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE pages (
    tx_t3elasticsearch_last_indexed int(11) DEFAULT '0' NOT NULL,
);