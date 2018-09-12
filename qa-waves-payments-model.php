<?php

function wp_create_table_sql()
{
	return "CREATE TABLE IF NOT EXISTS ^waves_payments (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		txid varchar(44) DEFAULT NULL,
		asset_name varchar(16) DEFAULT NULL,
		amount int(11) NOT NULL DEFAULT '0',
		purpose varchar(255) DEFAULT NULL,
		created_on int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY(id),
		KEY(txid)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
		";
}

function wp_count_payments()
{
	$sql = 'SELECT COUNT(*) AS cnt FROM ^waves_payments WHERE txid IS NOT NULL';
	$result = qa_db_query_sub($sql);
	$rec = qa_db_read_one_assoc($result, true);
	
	if (is_array($rec) && isset($rec['cnt']))
		return intval($rec['cnt']);
		
	return 0;
}

function wp_create_payment($rec)
{
	$now = time();
	$sql = 'INSERT INTO ^waves_payments (asset_name, amount, purpose, created_on) VALUES ($, #, $, #)';
	qa_db_query_sub($sql, $rec['asset_name'], $rec['amount'], $rec['purpose'], $now);
	return qa_db_last_insert_id();
}

function wp_create_payment_rec($txid, $asset_name, $amount, $purpose)
{
	$now = time();
	$sql = 'INSERT INTO ^waves_payments (txid, asset_name, amount, purpose, created_on) VALUES ($, $, #, $, #)';
	qa_db_query_sub($sql, $txid, $asset_name, $amount, $purpose, $now);
	return qa_db_last_insert_id();
}

function wp_read_payment($id)
{
	$sql = 'SELECT txid, asset_name, amount, purpose, created_on FROM ^waves_payments WHERE id=#';
	$result = qa_db_query_sub($sql, $id);
	return qa_db_read_one_assoc($result, true);
}

function wp_read_payment_by_txid($txid)
{
	$sql = 'SELECT id, asset_name, amount, purpose, created_on FROM ^waves_payments WHERE txid=$';
	$result = qa_db_query_sub($sql, $txid);
	return qa_db_read_one_assoc($result, true);
}

function wp_read_payment_page($page, $length)
{
	$sql = 'SELECT id, txid, asset_name, amount, purpose, created_on FROM ^waves_payments WHERE txid IS NOT NULL ORDER BY created_on DESC LIMIT # OFFSET #';
	$result = qa_db_query_sub($sql, $length, $page * $length);
	return qa_db_read_all_assoc($result);
}

function wp_update_payment($rec)
{
	$sql = 'UPDATE ^waves_payments SET txid=$, asset_name=$, amount=#, purpose=$ WHERE id=#';
	return qa_db_query_sub($sql, $rec['txid'], $rec['asset_name'], $rec['amount'], $rec['purpose'], $rec['id']);
}

function wp_update_payment_txid($id, $txid)
{
	$sql = 'UPDATE ^waves_payments SET txid=$ WHERE id=#';
	return qa_db_query_sub($sql, $txid, $id);
}

function wp_delete_payment($id)
{
	$sql = 'DELETE FROM ^waves_payments WHERE id=#';
	return qa_db_query_sub($sql, $id);
}


