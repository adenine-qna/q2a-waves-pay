<?php

function qa_waves_assets_opt($data = FALSE)
{
	$optname = 'plugin_waves_assets';
	
	if ($data === FALSE)
		return json_decode(qa_opt($optname), true);
	
	if (is_array($data))
		qa_opt($optname, json_encode($data));
}

function qa_waves_assets_dp_opt($data = FALSE)
{
	$optname = 'plugin_waves_assets_dp';
	
	if ($data === FALSE)
		return json_decode(qa_opt($optname), true);
	
	if (is_array($data))
		qa_opt($optname, json_encode($data));
}

function qa_waves_asset_id($name, $id = FALSE)
{
	$assets = qa_waves_assets_opt();
	
	if ($id === FALSE){
		if (is_array($assets) && isset($assets[$name]))
			return $assets[$name];
		
		return '';
	}
	else if (strlen($name) > 0) {
		if (!is_array($assets))
			$assets = array();
				
		if (strlen($id) > 0)
			$assets[$name] = $id;
		else
			unset($assets[$name]);
		
		qa_waves_assets_opt($assets);
	}
}

function qa_waves_asset_name($asset_id)
{
	$assets = qa_waves_assets_opt();
	
	if (!is_array($assets))
		return '';
	
	foreach ($assets as $name => $id) {
		if ($id == $asset_id) {
			return $name;
		}
	}
	
	return '';
}

function qa_waves_asset_dp($name, $dp = FALSE)
{
	$assets = qa_waves_assets_dp_opt();
	
	if ($dp === FALSE){
		if (is_array($assets) && isset($assets[$name]))
			return intval($assets[$name]);
		
		return 0;
	}
	else if (strlen($name) > 0) {
		if (!is_array($assets))
			$assets = array();
				
		if (strlen($dp) > 0)
			$assets[$name] = intval($dp);
		else
			unset($assets[$name]);
		
		qa_waves_assets_dp_opt($assets);
	}
}

function qa_waves_id_link($id)
{
	if (strlen($id) != 44)
		return $id;
		
	return '<a href="https://wavesexplorer.com/tx/' . $id . '" target="_blank">' . $id . '</a>';	
}

class qa_waves_assets_page {

	function get_request($request)
	{
		$parts = explode('/', $request);
		
		if (count($parts) < 2 || $parts[0] != 'admin' || $parts[1] != 'waves-assets')
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

	function page_waves_assets($content)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/waves_assets');

		if (qa_clicked('add_asset')) {
			qa_redirect('admin/waves-assets/add');
		}
		
		$fields = array();
		
		$wt = qa_waves_assets_opt();
		
		if (is_array($wt)) {
			
			ksort($wt);
			
			foreach ($wt as $name => $id)
			{
				$fields['asset_' . $name] = array(
					'type' => 'static',
					'label' => '<a href="' . qa_path('admin/waves-assets/edit/' . $name) . '">' . $name . '</a> &ndash; ' . qa_waves_id_link($id),
				);
			} 
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'buttons' => array(
				array(
					'tags' => 'NAME="add_asset"',
					'label' => qa_lang_html('plugin_waves_pay_desc/add_asset'),
				),
			),
		);
		
		if (count($fields) > 0)
			$content['form']['fields'] = $fields;
		
		return $content;
	}

	function page_waves_assets_add($content)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/add_waves_asset');

		if (qa_clicked('add')) {
			
			$name = trim(qa_post_text('asset_name'));
			$id = trim(qa_post_text('asset_id'));
			$dp = trim(qa_post_text('decimals'));
			
			qa_waves_asset_id($name, $id);
			qa_waves_asset_dp($name, $dp);
			
			qa_redirect('admin/waves-assets');
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'name' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/asset_name'),
					'type' => 'text',
					'tags' => 'NAME="asset_name" ID="asset_name"',
					'value' => '',
				),
				'id' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/asset_id'),
					'type' => 'text',
					'tags' => 'NAME="asset_id" ID="asset_id"',
					'value' => '',
				),
				'decimals' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/decimal_points'),
					'type' => 'number',
					'tags' => 'NAME="decimals" ID="decimals"',
					'value' => '',
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="add"',
					'label' => qa_lang_html('plugin_waves_pay_desc/add'),
				),
			),
		);

		return $content;
	}
	
	function page_waves_assets_edit($content, $asset)
	{
		if (strlen($asset) <= 0)
			qa_redirect('admin/waves-assets');
			
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/edit_waves_asset');

		if (qa_clicked('save')) {
			
			$name = trim(qa_post_text('asset_name'));
			$id = trim(qa_post_text('asset_id'));
			$dp = trim(qa_post_text('decimals'));

			if ($name != $asset) {
				qa_waves_asset_id($asset, '');			
				qa_waves_asset_dp($asset, '');
			}
			
			qa_waves_asset_id($name, $id);			
			qa_waves_asset_dp($name, $dp);
			
			qa_redirect('admin/waves-assets');
		}

		if (qa_clicked('delete')) {
			
			$name = trim(qa_post_text('asset_name'));
			
			if (strlen($name) > 0)
				qa_redirect('admin/waves-assets/delete/' . $name);
		}
		
		if (count($wt) <= 0)
			$wt[$asset] = '';
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'name' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/asset_name'),
					'type' => 'text',
					'tags' => 'NAME="asset_name" ID="asset_name"',
					'value' => $asset,
				),
				'id' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/asset_id'),
					'type' => 'text',
					'tags' => 'NAME="asset_id" ID="asset_id"',
					'value' => qa_waves_asset_id($asset),
				),
				'decimals' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/decimal_points'),
					'type' => 'number',
					'tags' => 'NAME="decimals" ID="decimals"',
					'value' => qa_waves_asset_dp($asset),
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="save"',
					'label' => qa_lang_html('plugin_waves_pay_desc/save'),
				),
				array(
					'tags' => 'NAME="delete"',
					'label' => qa_lang_html('plugin_waves_pay_desc/delete'),
				),
			),
		);

		return $content;
	}
	
	function page_waves_assets_delete($content, $asset)
	{
		if (strlen($asset) <= 0)
			qa_redirect('admin/waves-assets');
			
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/delete_waves_asset');
		
		if (qa_clicked('yes')) {
			
			$name = trim(qa_post_text('asset_name'));
			qa_waves_asset_id($name, '');			
			qa_waves_asset_dp($name, '');
			
			qa_redirect('admin/waves-assets');
		}
		
		if (qa_clicked('no')) {
			$name = trim(qa_post_text('asset_name'));
			
			if (strlen($name) > 0)
				qa_redirect('admin/waves-assets/edit/' . $name);
			
			qa_redirect('admin/waves-assets');				
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'message' => array(
					'label' => qa_lang_html_sub('plugin_waves_pay_desc/are_you_sure_to_delete_asset', '<em>' . $asset . '</em>'),
					'type' => 'static',
				),
				'asset_name' => array(
					'type' => 'hidden',
					'tags' => 'NAME="asset_name" ID="asset_name"',
					'value' => $asset,
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="yes"',
					'label' => qa_lang_html('plugin_waves_pay_desc/yes'),
				),
				array(
					'tags' => 'NAME="no"',
					'label' => qa_lang_html('plugin_waves_pay_desc/no'),
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
		
		if (count($req) <= 0)
			return $this->page_waves_assets($content);
		
		if ($req[0] == 'add')
			return $this->page_waves_assets_add($content);
		
		if ($req[0] == 'edit')
			return $this->page_waves_assets_edit($content, $req[1]);
		
		if ($req[0] == 'delete')
			return $this->page_waves_assets_delete($content, $req[1]);
		
		return $qa_content;
	}
}

