CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE 'notary_header' ('txid' VARCHAR PRIMARY KEY, 'chain' VARCHAR, 'block_height' INTEGER, 'block_time' INTEGER, 'block_datetime' VARCHAR, 'block_hash' VARCHAR, 'ac_ntx_blockhash' VARCHAR, 'ac_ntx_height' INTEGER, 'opret' VARCHAR, 'season' VARCHAR);
null;
CREATE TABLE 'notary_detail' ('id' INTEGER PRIMARY KEY AUTOINCREMENT, 'detail_id' INTEGER, 'name' VARCHAR, 'notary_header_txid' VARCHAR, UNIQUE ('notary_header_txid','name'));
null;
CREATE TABLE 'notary_batch_data' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'batch_name' VARCHAR, 'start_txid' VARCHAR, 'end_txid' VARCHAR, 'data_count' INTEGER, 'batch_row_count' INTEGER, 'sort_seq' INTEGER, 'status' VARCHAR, 'created_date' INTEGER, 'updated_date' INTEGER);
