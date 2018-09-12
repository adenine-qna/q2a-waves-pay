<?php

function qa_waves_acct_addr_opt($data = FALSE)
{
	$optname = 'plugin_waves_acct_addr';
	
	if ($data === FALSE)
		return qa_opt($optname);
	
	qa_opt($optname, $data);
}

function qa_waves_addr_link($addr)
{
	if (strlen($addr) != 35)
		return $addr;
		
	return '<a href="https://wavesexplorer.com/address/' . $addr . '" target="_blank">' . $addr . '</a>';	
}

class qa_waves_acct_page {

	function get_request($request)
	{
		$parts = explode('/', $request);
		
		if (count($parts) < 2 || $parts[0] != 'admin' || $parts[1] != 'waves-acct')
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

	function page_waves_acct($content)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/waves_acct');

		if (qa_clicked('add_addr')) {
			qa_redirect('admin/waves-acct/add');
		}
		
		if (qa_clicked('edit_addr')) {
			qa_redirect('admin/waves-acct/edit');
		}
		
		$has_addr = FALSE;
		$addr = qa_waves_acct_addr_opt();

		if (strlen($addr) > 0)
			$has_addr = TRUE;
		
		$fields = array();

		if ($has_addr)
			$label = qa_lang_html('plugin_waves_pay_desc/waves_acct_addr_label') . ': ' . qa_waves_addr_link($addr);
		else
			$label = qa_lang_html('plugin_waves_pay_desc/waves_acct_addr_label_none');
		
		$fields['addr'] = array(
			'type' => 'static',
			'label' => $label,
		);

		if ($has_addr) {
			$buttons = array(
				array(
					'tags' => 'NAME="edit_addr"',
					'label' => qa_lang_html('plugin_waves_pay_desc/edit'),
				),
			);
		}
		else {
			$buttons = array(
				array(
					'tags' => 'NAME="add_addr"',
					'label' => qa_lang_html('plugin_waves_pay_desc/add'),
				),
			);
		}
						
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

		);
		
		if (count($fields) > 0)
			$content['form']['fields'] = $fields;
		
		if (count($buttons) > 0)
			$content['form']['buttons'] = $buttons;
		
		return $content;
	}
			
	function page_waves_acct_add($content)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/add_waves_acct');
		
		if (qa_clicked('add')) {
			$addr = trim(qa_post_text('acct_addr'));
			
			qa_waves_acct_addr_opt($addr);
			qa_redirect('admin/waves-acct');
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'acct_addr' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/waves_acct_addr'),
					'type' => 'text',
					'tags' => 'NAME="acct_addr" ID="acct_addr"',
					'value' => qa_waves_acct_addr_opt(),
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
			
	function page_waves_acct_edit($content)
	{
		$content['title'] = qa_lang_html('plugin_waves_pay_desc/edit_waves_acct');
		
		if (qa_clicked('save')) {
			$addr = trim(qa_post_text('acct_addr'));
			
			qa_waves_acct_addr_opt($addr);
			qa_redirect('admin/waves-acct');
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'acct_addr' => array(
					'label' => qa_lang_html('plugin_waves_pay_desc/waves_acct_addr'),
					'type' => 'text',
					'tags' => 'NAME="acct_addr" ID="acct_addr"',
					'value' => qa_waves_acct_addr_opt(),
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="save"',
					'label' => qa_lang_html('plugin_waves_pay_desc/save'),
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
			return $this->page_waves_acct($content);		

		if ($req[0] == 'add')
			return $this->page_waves_acct_add($content);

		if ($req[0] == 'edit')
			return $this->page_waves_acct_edit($content);
				
		return $qa_content;
	}	
}
