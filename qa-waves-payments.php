<?php

include_once "qa-waves-payments-model.php";

function wp_get_waves_pay_req_url($asset, $amount, $cb_url)
{
	$assetid = qa_waves_asset_id($asset);
	$addr = qa_waves_acct_addr_opt();
	$dp = qa_waves_asset_dp($asset);
	$w_amount = number_format($amount, $dp, '.', '');
	$w_url = 'https://client.wavesplatform.com/#send/' . $assetid . '?recipient=' . $addr .
		'&amount=' . $w_amount . '&referrer=' . $cb_url . '&strict';
		
	return $w_url;
}

/*
Get Waves transaction details
Return sample:
On success:
{
  "type" : 4,
  "id" : "9aoC4tNPNNCpz9N3V5ea38mqtBYDeQjs2wY281HPEph4",
  "sender" : "3PCebYRFcYM7CHor5MC5tFfPnBJu7Xv5gsa",
  "senderPublicKey" : "4GhinWrfkJrLqtgNvLdNZipN2Ha92Z9W3Y1JBo8TLrcf",
  "fee" : 100000,
  "timestamp" : 1520512678162,
  "signature" : "57rQr2riFpYMuuLetazpBKME7UTdt5DodWCnSbH3zyzr17eRnQ56nWcoZw86PxaKnzArxruWYRcuFyTjpuqsG24z",
  "recipient" : "3PGya1m2TgrawVdL9k3mcUmFWxrC4ZS62iV",
  "assetId" : "BjDHgbL9swcwSL7Xg1q4qnwbb6mdo2Qc4o9RqNB4TeWF",
  "amount" : 1000000000000000,
  "feeAsset" : null,
  "attachment" : "",
  "height" : 909373
}
On failure:
{ "status" : "error", "details" : "Transaction is not in blockchain" }
*/
function wp_get_waves_tx_details($txid)
{
	// create curl resource 
	$ch = curl_init(); 

	// set url 
	curl_setopt($ch, CURLOPT_URL, "https://nodes.wavesplatform.com/transactions/info/" . $txid); 

	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

	// $output contains the output string 
	$output = curl_exec($ch); 

	// close curl resource to free up system resources 
	curl_close($ch);
	
	return json_decode($output, true);
}

function wp_is_valid_payment($txid, $asset, $min_amount)
{
	$rec = wp_read_payment_by_txid($txid);
	
	if (is_array($rec) && isset($rec['id']) && intval($rec['id']) > 0)
		return -1; // the txid record exists
	
	$stt = wp_get_waves_tx_details($txid);
	
	if (!is_array($stt) || !isset($stt['type']) || $stt['type'] != 4)
		return -2;
		
	$addr = qa_waves_acct_addr_opt();
	
	if (!isset($stt['recipient']) || $stt['recipient'] != $addr)
		return -3;
		
	$asset_id = qa_waves_asset_id($asset);

	if (!isset($stt['assetId']) || $stt['assetId'] != $asset_id)
		return -4;
	
	$dp = qa_waves_asset_dp($asset);
	$m_amount = intval($min_amount * $dp);

	if ($m_amount <= 0)
		return -5;

	if (!isset($stt['amount']))
		return -6;
		
	$amount = intval($stt['amount']);
	
	if ($amount >= $m_amount)
		return 0;
		
	return -7;
}

class qa_waves_payments_page {

	function get_request($request)
	{
		$parts = explode('/', $request);
		
		if (count($parts) < 2 || $parts[0] != 'admin' || $parts[1] != 'waves-payments')
			return FALSE;
			
		array_splice($parts, 0, 2);
		
		return $parts;
	}
	
	function match_request($request)
	{
		if ($this->get_request($request) === FALSE)
			return FALSE;
		
		return TRUE;
	}

//TXID    : D1USZfZPzVd2XNH9xj52Z81XhxChpwUKDJpQHz2haXRT	
//AssetID : 8LQW8f7P5d5PZM7GtZEBgaqRPGSzS3DfPuiXrURJ4AJS

	function init_queries($tableslc)
	{
		$tablename = qa_db_add_table_prefix('waves_payments');

		if(!in_array($tablename, $tableslc)) {
			return wp_create_table_sql(); 
		}
	}	

	function render_table($data)
	{
		$out = '<div style="overflow-x:auto;"><table>';
		
		if (is_array($data)) {
			$align = array();
			if (isset($data['head-align']) && is_array($data['head-align'])) {
				$align = $data['head-align'];
			}
			if (isset($data['head']) && is_array($data['head'])) {
				$out .= '<tr>';
				$cnt = 0;
				foreach ($data['head'] as $hcol) {
					if (isset($align[$cnt]) && strlen($align[$cnt]) > 0)
						$text_align = $align[$cnt];
					else
						$text_align = 'left';
					$out .= '<th style="padding: 5px; text-align: ' . $text_align . ';" class="qam-main-nav-wrapper clearfix">' . $hcol . '</th>';
					++$cnt;
				}
				$out .= '</tr>';
			}
			$align = array();
			if (isset($data['align']) && is_array($data['align'])) {
				$align = $data['align'];
			}
			if (isset($data['rows']) && is_array($data['rows'])) {
				$line = 0;
				foreach ($data['rows'] as $row) {
					if ($line % 2 == 0)
						$out .= '<tr style="background-color: #f2f2f2;}">';
					else
						$out .= '<tr>';
					if (is_array($row)) {
						$cnt = 0;
						foreach ($row as $col) {
							if (isset($align[$cnt]) && strlen($align[$cnt]) > 0)
								$text_align = $align[$cnt];
							else
								$text_align = 'left';
							$out .= '<td style="padding: 5px; text-align: ' . $text_align . ';">' . $col . '</td>';
							++$cnt;
						}
					}
					$out .= '</tr>';
					++$line;
				}
			}
		}
		
		$out .= '</table></div>';
		
		return $out;
	}
	
	function page_waves_payments($content)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/waves_payments');
/*		
		if (qa_clicked('prev')) {
			$rec = array(
				'asset_name' => 'Lams', 
				'amount' => 67, 
				'purpose' => 'Donate 67 points',
			);
			
			$add = wp_create_payment($rec);
		}
*/
		$reccnt = wp_count_payments();
		$page = 0;
		$pagelen = 25;
		
		if (isset($_REQUEST['page']))
			$page = intval($_REQUEST['page']);

		if (qa_clicked('prev')) {
			qa_redirect('admin/waves-payments', array('page' => $page - 1));
		}
				
		if (qa_clicked('next')) {
			qa_redirect('admin/waves-payments', array('page' => $page + 1));
		}
				
		$rows = array();
		$recs = wp_read_payment_page($page, $pagelen);
		
		if (is_array($recs)) {
			foreach ($recs as $rec) {
				$rows[] = array(
					'<a href="' . qa_path('admin/waves-payments/' . $rec['id']) . '">' . date("Y-m-d", $rec['created_on']) . '</a>' ,
					$rec['asset_name'],
					$rec['amount'],
					$rec['purpose'],
				);
			}
		}
		
		$table = array(
			'head-align' => array(
				'left', 'left', 'right', 'left'
			),
			'align' => array(
				'left', 'left', 'right', 'left'
			),
			'head' => array(
				qa_lang_html('plugin_waves_pay_desc/date'), 
				qa_lang_html('plugin_waves_pay_desc/asset'), 
				qa_lang_html('plugin_waves_pay_desc/amount'), 
				qa_lang_html('plugin_waves_pay_desc/purpose'), 
			),
			'rows' => $rows,
		);

		if ($reccnt > 0)
			$content['custom'] .= $this->render_table($table);
		
		$form = '<div class="qa-part-form"><FORM METHOD="POST" ACTION="' . qa_self_html() . '">';
		
		if ($reccnt <= 0)
			$form .= '<div class="qa-form-tall-label">' . qa_lang_html('plugin_waves_pay_desc/no_payment_rec') . '</div>';
		
		if ($page > 0)
			$form .= '<INPUT NAME="prev" title="" VALUE="' . qa_lang_html('plugin_waves_pay_desc/prev') . '"  
					class="qa-form-tall-button" TYPE="submit"> &nbsp; ';

		if (($page + 1) * $pagelen < $reccnt)
			$form .= '<INPUT NAME="next" title="" VALUE="' . qa_lang_html('plugin_waves_pay_desc/next') . '"
					class="qa-form-tall-button" TYPE="submit">';
				
		$form .= '</FORM></div>';
		
		$content['custom'] .= $form;
		
		return $content;
	}
	
	function page_waves_payment_rec($content, $id)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/waves_payment_rec');
		
		$rec = wp_read_payment($id);
		$aid = qa_waves_asset_id($rec['asset_name']);
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'wide', // could be 'tall'

			'fields' => array(
				'date' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/date') . ': ',
					'value' =>date("Y-m-d H:i:s", $rec['created_on']),
					'type' => 'static',
				),
				'txid' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/txid') . ': ',
					'value' => qa_waves_id_link($rec['txid']),
					'type' => 'static',
				),
				'asset' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/asset') . ': ',
					'value' => $rec['asset_name'] . ' &ndash; ' . qa_waves_id_link($aid),
					'type' => 'static',
				),
				'amount' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/amount') . ': ',
					'value' => $rec['amount'],
					'type' => 'static',
				),
				'purpose' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/purpose') . ': ',
					'value' => $rec['purpose'],
					'type' => 'static',
				),
			),
		);
		
		return $content;
	}

	function process_request($request)
	{
		$content = qa_content_prepare();

		if (!qa_admin_check_privileges($content)) // this page is for admin only
			return $content;
		
		$req = $this->get_request($request);
		
		if ($req === FALSE || !is_array($req))
			return $content;
			
		$content['navigation']['sub'] = qa_admin_sub_navigation();
		$cnt = count($req);
		 
		if ($cnt <= 0)
			return $this->page_waves_payments($content);		
		 
		if ($cnt == 1)
			return $this->page_waves_payment_rec($content, $req[0]);		

		return $qa_content;
	}	
}
